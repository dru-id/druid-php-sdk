<?php namespace Genetsis\core\OAuth\Collections;

/**
 * This class brings together the different types of existing tokens.
 *
 * @package   Genetsis
 * @category  Collection
 */
class TokenTypes {

    const CLIENT_TOKEN = '__ucs';
    const ACCESS_TOKEN = '__uas';
    const REFRESH_TOKEN = '__urs';

    /**
     * @param string $value
     * @return boolean TRUE if it is a valid value or FALSE otherwise.
     */
    public static function check($value)
    {
        return in_array($value, [self::CLIENT_TOKEN, self::ACCESS_TOKEN, self::REFRESH_TOKEN]);
    }

}
	