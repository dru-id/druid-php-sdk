<?php
namespace Genetsis\core\Cache\Services;

use Genetsis\core\Cache\Contracts\CacheServiceInterface;
use Genetsis\core\Logger\Contracts\LoggerServiceInterface;

/**
 * File cache service implementation.
 *
 * All data will be stored into file system based on the configuration parameters defined when instantiating
 * this class.
 *
 * @package  Genetsis
 * @category Service
 */
class FileCache implements CacheServiceInterface {

    /** @var string $folder Full path to the folder where all files will be stored. */
    private $folder;
    /** @var LoggerServiceInterface $logger */
    private $logger;

    /**
     * @param string $folder Full path to the folder where all files will be stored.
     * @param LoggerServiceInterface $logger
     */
    public function __construct($folder, LoggerServiceInterface $logger)
    {
        $this->setFolder($folder);
        $this->logger = $logger;
    }

    /**
     * Sets the folder path where files will be stored.
     *
     * The folder will be checked and if not exists then tries to create it.
     *
     * @param string $folder Full path to the folder where all files will be stored.
     * @return FileCache
     * @throws \Exception If the folder does not exists and creation fails.
     */
    public function setFolder($folder)
    {
        if (!is_dir($folder) || !is_writable($folder)) {
            if (@mkdir($folder, 0777, true) === false) {
                throw new \Exception('Failed to create cache directory: ' . $folder);
            }
        }

        $this->folder = rtrim($folder, '/');
        return $this;
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $data = null, $ttl = 3600)
    {
        if (!$key) {
            $this->logger->error('Empty key.', __METHOD__, __LINE__);
            return false;
        }

        $key = $this->prepareCacheFilename($key);
        $data = ['data' => $data, 'ttl' => (time() + $ttl) ];
        $data = function_exists('json_encode') ? json_encode($data) : serialize($data);

        $status = false;
        try {
            $fh = @fopen($key, "c");
            if ($fh === false) {
                throw new \Exception('We can not open a resource to create the file :' . $key);
            }
            if (@flock($fh, LOCK_EX)) {
                @ftruncate($fh, 0);
                @fwrite($fh, $data);
                @flock($fh, LOCK_UN);
                $status = true;
            }
            @fclose($fh);
        } catch (\Exception $e) {
            $this->logger->error('Error detected where store cache data: ' . $e->getMessage(), __METHOD__, __LINE__);
            return false;
        }

        return $status;
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        if (!$key) {
            $this->logger->warn('Empty key.', __METHOD__, __LINE__);
            return $default;
        }

        if (!$this->has($key)) {
            return $default;
        }
        $key = $this->prepareCacheFilename($key);

        $data = null;
        try {
            $fh = @fopen($key, "r");
            if ($fh === false) {
                throw new \Exception('We can not open a resource to read the file :' . $key);
            }
            if (@flock($fh, LOCK_SH)) {
                $data = @fread($fh, filesize($key));
            }
            @flock($fh, LOCK_UN);
            @fclose($fh);
        } catch (\Exception $e) {
            $this->logger->error('Error detected where retrieving cache data: ' . $e->getMessage(), __METHOD__, __LINE__);
            return $default;
        }

        // Assuming we got something back...
        if ($data) {
            $data = function_exists('json_decode') ? json_decode($data, true) : unserialize($data);
            if (!isset($data['data'])) {
                $this->logger->info('Data structure corrupted. Returned default data.', __METHOD__, __LINE__);
                $this->delete($key);
                return $default;
            }
            if (isset($data['ttl']) && ($data['ttl'] < time())) {
                $this->logger->info('Data expired. Returned default data.', __METHOD__, __LINE__);
                $this->delete($key);
                return $default;
            }
            return $data['data'];
        } else {
            return $default;
        }
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        $key = $this->prepareCacheFilename($key);
        return ($key && is_file($key) && is_readable($key));
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        if ($this->has($key)) {
            return @unlink($this->prepareCacheFilename($key));
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function clean()
    {
        return $this->deleteFiles($this->folder);
    }

    /**
     * Creates the filename key for this cache data.
     *
     * @param string $key The key identifier.
     * @return string The prepared key.
     */
    private function prepareCacheFilename($key)
    {
        return $this->folder . '/' . md5($key);
    }

    /**
     * Removes all files within folder.
     *
     * @param string $folder The folder where all files will be deleted.
     * @param integer $level Current level. All directories will be removed if level is greater than zero.
     * @return boolean
     */
    private function deleteFiles($folder, $level = 0)
    {
        $folder = rtrim($folder, '/\\'); // Trim the trailing slash
        $current_dir = @opendir($folder);
        if (!$current_dir) {
            return false;
        }

        while (($filename = @readdir($current_dir)) !== false) {
            if (($filename !== '.') && ($filename !== '..')) {
                if (is_dir($folder . DIRECTORY_SEPARATOR . $filename)) {
                    $this->deleteFiles($folder . DIRECTORY_SEPARATOR . $filename, $level + 1);
                } else {
                    @unlink($folder . DIRECTORY_SEPARATOR . $filename);
                }
            }
        }

        closedir($current_dir);

        return ($level > 0) ? @rmdir($folder) : true;
    }

}
