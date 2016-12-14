<?php

namespace Genetsis\core;
die('Deprecated: '.__FILE__);
use Exception;

/**
 * This class is used to Cache Data stored in File
 *
 * @package   Genetsis
 * @category  Helper
 * @version   1.0
 * @access    public
 * @todo      Checks storage process.
 */
class FileCache
{
    /** @var string Full path to the folder where cache will be stored. */
    protected static $cache_dir = '';
    /** @var string Holds the last error generated. */
    protected static $error = null;
    /** @var string Prefix to be added to the filename. */
    protected static $prefix = '';

    /**
     * @param string $path The full path to store cache.
     * @param string $pref
     * @throws Exception If there is an error.
     */
    public static function init($path, $pref = '')
    {
        // Requires the native JSON library
        if (!function_exists('json_decode') || !function_exists('json_encode')) {
            throw new Exception('Cache needs the JSON PHP extensions.');
        }

        self::$cache_dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $path;
        self::$prefix = $pref;

        if (!is_dir(self::$cache_dir) || !is_writable(self::$cache_dir)) {
            $auto_create = mkdir(self::$cache_dir, 0777, true);
            if ($auto_create === false) {
                throw new Exception("Failed creating cache directory [".self::$cache_dir."].");
            }
        }

    }

    /**
     * Saves data to the cache. Anything that evaluates to FALSE, NULL,
     * '', 0 will not be saved.
     *
     * @param string $key An identifier for the data.
     * @param mixed $data The data to be saved.
     * @param integer $ttl Lifetime of the stored data. In seconds.
     * @returns boolean TRUE on success, FALSE otherwise.
     */
    public static function set($key, $data = false, $ttl = 3600)
    {
        if (!$key) {
            self::$error = "Invalid key";
            return false;
        }
        if (!$data) {
            self::$error = "Invalid data";
            return false;
        }
        $key = self::makeFileKey($key);
        if (!is_integer($ttl)) {
            $ttl = (int)$ttl;
        }
        $store = array(
            'data' => $data,
            'ttl' => time() + $ttl,
        );

        $status = false;
        try {
            $fh = fopen($key, "c");
            if (flock($fh, LOCK_EX)) {
                ftruncate($fh, 0);
                fwrite($fh, json_encode($store));
                flock($fh, LOCK_UN);
                $status = true;
            }
            fclose($fh);
        } catch (Exception $e) {
            self::$error = "Exception caught: " . $e->getMessage();
            return false;
        }
        return $status;
    }

    /**
     * Creates a key for the cache.
     *
     * @param string $key The key id of the file.
     * @returns string The full path and filename to access.
     */
    private static function makeFileKey($key)
    {
        $safe_key = md5($key . '-' . self::$prefix);
        return self::$cache_dir . $safe_key;
    }

    /**
     * Reads the data from the cache.
     *
     * @param string $key An identifier for the data.
     * @returns mixed Data that was stored or FALSE on error.
     */
    public static function get($key)
    {
        if (!$key) {
            self::$error = "Invalid key";
            return false;
        }
        $key = self::makeFileKey($key);
        if (!file_exists($key)) {
            return false;
        }
        $file_content = null;

        // Get the data from the file
        try {
            $fh = fopen($key, "r");

            if ($fh) {
                // wait for a lock
                while (!flock($fh, LOCK_EX)) {;}

                if (flock($fh, LOCK_SH)) {
                    $file_content = fread($fh, filesize($key));
                }

                //release the lock
                flock($fh, LOCK_UN);

                fclose($fh);
            }
        } catch (Exception $e) {
            self::$error = "Exception caught: " . $e->getMessage();
            return false;
        }

        // Assuming we got something back...
        if ($file_content) {
            $store = json_decode($file_content, true);
            if ($store['ttl'] < time()) {
                unlink($key); // remove the file
                self::$error = "Data expired";
                return false;
            }
        }
        return $store['data'];
    }

    /**
     * Removes a key, regardless of its expiry time.
     *
     * @param string $key The identifier to be deleted.
     * @return boolean TRUE on success, FALSE otherwise.
     */
    public static function delete($key)
    {
        if (!$key) {
            self::$error = "Invalid key";
            return false;
        }
        $key = self::makeFileKey($key);
        if (!file_exists($key)) {
            return false;
        }

        try {
            unlink($key); // remove the file
        } catch (Exception $e) {
            self::$error = "Exception caught: " . $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Cleans file cache.
     *
     * @return boolean TRUE on success, FALSE otherwise.
     */
    public static function deleteCache()
    {
        return self::deleteFiles(self::$cache_dir);
    }

    /**
     * Removes all cached files recursively.
     *
     * @param string $path Full path to the folder where cached files has been
     *     placed.
     * @param boolean $del_dir TRUE if we remove the subdirectories stored or FALSE
     *     otherwise.
     * @param integer $level The current level.
     * @return boolean TRUE on success, FALSE otherwise.
     */
    private static function deleteFiles($path, $del_dir = false, $level = 0)
    {
        // Trim the trailing slash
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if (!$current_dir = @opendir($path)) {
            return false;
        }

        while (false !== ($filename = @readdir($current_dir))) {
            if ($filename != "." and $filename != "..") {
                if (is_dir($path . DIRECTORY_SEPARATOR . $filename)) {
                    // Ignore empty folders
                    if (substr($filename, 0, 1) != '.') {
                        self::deleteFiles($path . DIRECTORY_SEPARATOR . $filename, $del_dir, $level + 1);
                    }
                } else {
                    unlink($path . DIRECTORY_SEPARATOR . $filename);
                }
            }
        }
        @closedir($current_dir);

        if ($del_dir == true AND $level > 0) {
            return @rmdir($path);
        }

        return true;
    }

    /**
     * Reads and clears the internal error.
     *
     * @returns string Text of the error raised by the last process.
     */
    public function getError()
    {
        $message = self::$error;
        self::$error = null;
        return $message;
    }

    /**
     * Can be used to inspect internal error.
     *
     * @returns boolean TRUE if we have an error, FALSE otherwise.
     */
    public function haveError()
    {
        return ((self::$error !== null)
            ? true
            : false);
    }
}