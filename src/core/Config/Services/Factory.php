<?php
namespace Genetsis\core\Config\Services;

use Genetsis\core\Config\Beans\Config;
use Genetsis\core\Config\Beans\Log;


class Factory {

    /**
     * @param string $config_file
     * @return Config
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function makeFromFile($config_file)
    {
        if (!is_file($config_file) || !is_readable($config_file)) {
            throw new \InvalidArgumentException('Unable to load configuration file.');
        }
        $config_file = parse_ini_file($config_file);

        $config = new Config(
            $config_file['SERVER'],
            call_user_func(function($settings){
                if (isset($settings['LOG_PATH']) && $settings['LOG_PATH']) {
                    if (!is_dir($settings['LOG_PATH']) || !is_writable($settings['LOG_PATH'])) {
                        throw new \Exception('Log folder path defined but it doesn\'t exist or insufficient permissions.');
                    }
                    return new Log($settings['LOG_PATH'], $settings['LOG_LEVEL']);
                }
                return null;
            }, $config_file),
            call_user_func(function($settings){
                if (isset($settings['CACHE_PATH']) && $settings['CACHE_PATH']) {
                    if (!is_dir($settings['CACHE_PATH']) || !is_writable($settings['CACHE_PATH'])) {
                        throw new \Exception('Cache folder path defined but it doesn\'t exist or insufficient permissions.');
                    }
                }
                return null;
            }, $config_file)
        );
        return $config;
    }

    public static function getDefaultConfig()
    {
        if (!isset($_SERVER['SERVER_NAME']) || !$_SERVER['SERVER_NAME']) {
            throw new \Exception('ServerName not found.');
        }
        return new Config($_SERVER['SERVER_NAME']);
    }

}