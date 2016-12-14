<?php namespace Genetsis\core\Logger\Collections;

/**
 * Class to group all log levels.
 *
 * @package   Genetsis
 * @category  Collection
 */
class LogLevels {

    /**
     * Detailed debug information
     */
    const DEBUG = 'debug';

    /**
     * Interesting events
     *
     * Examples: User logs in, SQL logs.
     */
    const INFO = 'info';

    /**
     * Uncommon events
     */
    const NOTICE = 'notice';

    /**
     * Exceptional occurrences that are not errors
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    const WARNING = 'warning';

    /**
     * Runtime errors
     */
    const ERROR = 'error';

    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    const CRITICAL = 'critical';

    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    const ALERT = 'alert';

    /**
     * Urgent alert.
     */
    const EMERGENCY = 'emergency';

    /**
     * Checks if it is a valid level.
     *
     * @param string $level
     * @return boolean
     */
    public static function check($level)
    {
        return in_array($level, [self::DEBUG, self::INFO, self::NOTICE, self::WARNING, self::ERROR, self::CRITICAL, self::ALERT, self::EMERGENCY]);
    }

}