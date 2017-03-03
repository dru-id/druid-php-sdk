<?php namespace Genetsis\DruID\Core\OAuth\Beans;

use Genetsis\DruID\Core\OAuth\Collections\TokenTypes as TokenTypesCollection;

/**
 * This class stores "access_token" data.
 *
 * @author Genetsis
 * @link http://developers.dru-id.com
 */
class AccessToken extends StoredToken
{

    /**
     * {@inheritDoc}
     */
    public function __construct ($value, $expires_in = 0, $expires_at = 0, $path = '/')
    {
        $this->name = TokenTypesCollection::ACCESS_TOKEN;
        parent::__construct($value, $expires_in, $expires_at, $path);
    }

}