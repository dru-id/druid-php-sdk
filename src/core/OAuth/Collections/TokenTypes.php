<?php
namespace Genetsis\core\OAuth\Collections;
use Genetsis\core\Utils\Contracts\CollectionInterface;

/**
 * This class brings together the different types of existing tokens.
 *
 * @package   Genetsis
 * @category  Collection
 */
class TokenTypes implements CollectionInterface {

    const CLIENT_TOKEN = '__ucs';
    const ACCESS_TOKEN = '__uas';
    const REFRESH_TOKEN = '__urs';

    /**
     * @inheritDoc
     */
    public static function check($value)
    {
        return ($value && in_array($value, [self::CLIENT_TOKEN, self::ACCESS_TOKEN, self::REFRESH_TOKEN]));
    }

}
	