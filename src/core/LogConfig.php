<?php
namespace Genetsis\core;

use Exception;

/**
 * This class stores the configuration for objects for logging.
 *
 * @package   Genetsis
 * @category  Bean
 * @version   2.0
 * @access    private
 */
class LogConfig implements \LoggerConfigurator
{

    /** @var string The threshold for defining which messages should be
     * stored. */
    protected $threshold = 'OFF';
    /** @var string Full path to folder where logs will be saved. */
    protected $log_path = '';

    /**
     * @param string The threshold for defining which messages should be
     * stored.
     * @param string Full path to folder where logs will be saved.
     * @throws \Exception If there is an error in the process.
     */
    public function __construct($levelInfo, $logPath)
    {
        if (!is_null($levelInfo)) {
            $this->threshold = $levelInfo;
        }
        $this->log_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $logPath;

        if (!is_dir($this->log_path) || !is_writable($this->log_path)) {
            $auto_create = mkdir($this->log_path, 0777, true);
            if ($auto_create === false) {
                throw new Exception("Failed creating Log  directory [$this->log_path].");
            }
        }
    }

    /**
     * Initialize objects for logging. We have two logs files:
     * - gid-last-request.log: to see logger for the last request
     * - gid-all-requests.log: to see all logger for all request
     *
     * @param \LoggerHierarchy Object to handle objects for logging.
     * @param mixed Either path to the config file or the configuration as
     *     an array.
     * @return void
     */
    public function configure(\LoggerHierarchy $hierarchy, $input = null)
    {
        //Create a logger layout
        $layout = new \LoggerLayoutPattern();
        $layout->setConversionPattern("%d{Y-m-d H:i:s}[%r] %-5level %C.%M[%L] %msg%n");
        $layout->activateOptions();

        // Create an appender which logs to file
        $appLog = new \LoggerAppenderRollingFile('main');
        $appLog->setFile($this->log_path . 'gid-all-requests.log');
        $appLog->setAppend(true);
        $appLog->setMaxFileSize('2MB');
        $appLog->setMaxBackupIndex(5);
        $appLog->setThreshold($this->threshold);
        $appLog->setLayout($layout);
        $appLog->activateOptions();

        //Create an appender which logs Console
        $appConsole = new \LoggerAppenderFile('console');
        $appConsole->setFile($this->log_path . 'gid-last-request.log');
        $appConsole->setAppend(false);
        $appConsole->setThreshold($this->threshold);
        $appConsole->setLayout($layout);
        $appConsole->activateOptions();

        // Add appenders to the root logger
        $root = $hierarchy->getRootLogger();
        $root->addAppender($appLog);
        $root->addAppender($appConsole);
    }
}