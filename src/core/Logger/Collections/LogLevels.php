<?php
namespace Genetsis\core\Logger\Collections;
use Genetsis\core\Utils\Contracts\CollectionInterface;

/**
 * Class to group all log levels.
 *
 * @package   Genetsis
 * @category  Collection
 */
class LogLevels implements CollectionInterface {

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
     * @inheritDoc
     */
    public static function check($value)
    {
        return ($value && in_array($value, [self::DEBUG, self::INFO, self::NOTICE, self::WARNING, self::ERROR, self::CRITICAL, self::ALERT, self::EMERGENCY]));
    }

}