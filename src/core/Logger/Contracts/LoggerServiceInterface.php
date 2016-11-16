<?php namespace Genetsis\core\Logger\Contracts;

/**
 * Logger service interface.
 *
 * @package   Genetsis
 * @category  Contract
 */
interface LoggerServiceInterface {

    /**
     * Log a message string with the DEBUG level.
     *
     * @param string $message message
     * @param string|null $method Class, method, function,... anything where the log is made.
     * @param integer|null $line If defined the line number.
     * @return LoggerServiceInterface
     */
    public function debug ($message, $method = null, $line = null);

    /**
     * Log a message string with the INFO level.
     *
     * @param string $message message
     * @param string|null $method Class, method, function,... anything where the log is made.
     * @param integer|null $line If defined the line number.
     * @return LoggerServiceInterface
     */
    public function info ($message, $method = null, $line = null);

    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param string|null $method Class, method, function,... anything where the log is made.
     * @param integer|null $line If defined the line number.
     * @return Boolean Whether the record has been processed
     */
    public function notice($message, $method = null, $line = null);

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param string|null $method Class, method, function,... anything where the log is made.
     * @param integer|null $line If defined the line number.
     * @return Boolean Whether the record has been processed
     */
    public function warn($message, $method = null, $line = null);

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param string|null $method Class, method, function,... anything where the log is made.
     * @param integer|null $line If defined the line number.
     * @return Boolean Whether the record has been processed
     */
    public function warning($message, $method = null, $line = null);

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param string|null $method Class, method, function,... anything where the log is made.
     * @param integer|null $line If defined the line number.
     * @return Boolean Whether the record has been processed
     */
    public function err($message, $method = null, $line = null);

        /**
     * Log a message string with the ERROR level.
     *
     * @param string $message message
     * @param string|null $method Class, method, function,... anything where the log is made.
     * @param integer|null $line If defined the line number.
     * @return LoggerServiceInterface
     */
    public function error ($message, $method = null, $line = null);

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param string|null $method Class, method, function,... anything where the log is made.
     * @param integer|null $line If defined the line number.
     * @return Boolean Whether the record has been processed
     */
    public function crit($message, $method = null, $line = null);

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param string|null $method Class, method, function,... anything where the log is made.
     * @param integer|null $line If defined the line number.
     * @return Boolean Whether the record has been processed
     */
    public function critical($message, $method = null, $line = null);

    /**
     * Adds a log record at the ALERT level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param string|null $method Class, method, function,... anything where the log is made.
     * @param integer|null $line If defined the line number.
     * @return Boolean Whether the record has been processed
     */
    public function alert($message, $method = null, $line = null);

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param string|null $method Class, method, function,... anything where the log is made.
     * @param integer|null $line If defined the line number.
     * @return Boolean Whether the record has been processed
     */
    public function emerg($message, $method = null, $line = null);

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param string|null $method Class, method, function,... anything where the log is made.
     * @param integer|null $line If defined the line number.
     * @return Boolean Whether the record has been processed
     */
    public function emergency($message, $method = null, $line = null);

}