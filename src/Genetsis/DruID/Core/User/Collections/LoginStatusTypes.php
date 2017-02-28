<?php
namespace Genetsis\DruID\Core\User\Collections;

use Genetsis\DruID\Core\Utils\Contracts\CollectionInterface;

/**
 * Class to group all login status types.
 *
 * @package   Genetsis\DruID
 * @category  Collection
 */
class LoginStatusTypes implements CollectionInterface
{

    const CONNECTED = 'connected';
    const NOT_CONNECTED = 'notConnected';
    const UNKNOWN = 'unknown';

    /**
     * @inheritDoc
     */
    public static function check($value)
    {
        return ($value && in_array($value, [self::CONNECTED, self::NOT_CONNECTED, self::UNKNOWN]));
    }
}
