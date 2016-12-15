<?php
namespace Genetsis\core\OAuth\Collections;
use Genetsis\core\Utils\Contracts\CollectionInterface;

/**
 * Class to group all authentication methods.
 *
 * @package   Genetsis
 * @category  Collection
 */
class AuthMethods implements CollectionInterface {

    const GRANT_TYPE_AUTH_CODE = 'authorization_code';
    const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';
    const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    const GRANT_TYPE_VALIDATE_BEARER = 'urn:es.cocacola:oauth2:grant_type:validate_bearer';
    const GRANT_TYPE_EXCHANGE_SESSION = 'urn:es.cocacola:oauth2:grant_type:exchange_session';

    /**
     * @inheritDoc
     */
    public static function check($value)
    {
        return ($value && in_array($value, [self::GRANT_TYPE_AUTH_CODE, self::GRANT_TYPE_REFRESH_TOKEN, self::GRANT_TYPE_CLIENT_CREDENTIALS, self::GRANT_TYPE_VALIDATE_BEARER, self::GRANT_TYPE_EXCHANGE_SESSION]));
    }

}