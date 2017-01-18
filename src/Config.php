<?php
    namespace Genetsis;
    die('Deprecated: '.__FILE__);
    /**
     * library Configuration
     *
     * @package    Genetsis
     * @copyright  Copyright (c) 2014 Genetsis
     * @version    2.0
     */

    class Config {

        const CONF_VERSION = '1.4';

        public static $CONF_PATH = '';
        public static $CACHE_PATH = '';
        public static $LOG_PATH = '';

        public static $DEV_LOG_LEVEL = '';
        public static $TEST_LOG_LEVEL = '';
        public static $PROD_LOG_LEVEL = '';

        public static $DEV_SERVER = '';
        public static $TEST_SERVER = '';
        public static $PROD_SERVER = '';

        public static $gid_client = '';
        public static $ini_path = '';




        public static function init($client = 'default'){
            if (isset($_SESSION['lib_config'])){
                $config = $_SESSION['lib_config'];
            } else {
                $config = parse_ini_file(self::$ini_path);
                $_SESSION['lib_config'] = $config;
            }
            self::$gid_client = ($client != '') ? $client : 'default';

            $_SESSION['gid_client'] = $client;

            self::$CONF_PATH = $config['CONF_PATH'];
            self::$CACHE_PATH = $config['CACHE_PATH'];
            self::$LOG_PATH = $config['LOG_PATH'];

            self::$DEV_LOG_LEVEL = $config['DEV_LOG_LEVEL'];
            self::$TEST_LOG_LEVEL = $config['TEST_LOG_LEVEL'];
            self::$PROD_LOG_LEVEL = $config['PROD_LOG_LEVEL'];

            self::$DEV_SERVER = explode(',', trim($config['DEV_SERVER']));
            self::$TEST_SERVER = explode(',', trim($config['TEST_SERVER']));
            self::$PROD_SERVER = explode(',', trim($config['PROD_SERVER']));
        }

        /**
         * Get the environment where the library is executed
         *
         * @return string possible values: dev, test and prod
         */
        public static function environment(){
            if (isset($_SERVER['SERVER_NAME'])) {
                if (in_array($_SERVER['SERVER_NAME'], self::$DEV_SERVER)){
                    return 'dev';
                } elseif (in_array($_SERVER['SERVER_NAME'], self::$TEST_SERVER)) {
                    return 'test';
                } else {
                    return 'prod';
                }
            } else {
                return 'dev';
            }
        }

        public static function configPath(){
            $path = Config::$CONF_PATH;
            $path = (self::$gid_client != '') ? $path . self::$gid_client . '/' : $path;
            $path = $path . self::environment() . '/oauthconf.xml';

            return $path;
        }

        public static function logPath() {
            $path = Config::$LOG_PATH;
            $path = (self::$gid_client != '') ? $path . self::$gid_client . '/' : $path;
            return $path;
        }

        public static function cachePath() {
            $path = Config::$CACHE_PATH;
            $path = (self::$gid_client != '') ? $path . self::$gid_client . '/' : $path;
            return $path;
        }

        public static function logLevel() {
            switch (self::environment()) {
                case 'dev':
                    return Config::$DEV_LOG_LEVEL;
                    break;
                case 'test':
                    return Config::$TEST_LOG_LEVEL;
                    break;
                case 'prod':
                    return Config::$PROD_LOG_LEVEL;
                    break;
            }
        }
    }