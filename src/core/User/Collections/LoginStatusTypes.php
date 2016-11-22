<?php namespace Genetsis\core\User\Collections;

/**
 * Class to group all login status types.
 *
 * @package   Genetsis
 * @category  Collection
 */
class LoginStatusTypes
{
    const CONNECTED = 'connected';
    const NOT_CONNECTED = 'notConnected';
    const UNKNOWN = 'unknown';

    /**
     * @param string $value
     * @return boolean
     */
    public static function check($value)
    {
        return in_array($value, [self::CONNECTED, self::NOT_CONNECTED, self::UNKNOWN]);
    }
}