<?php namespace Genetsis\Core\OAuth\Beans;

use Genetsis\Core\OAuth\Collections\TokenTypes as TokenTypesCollection;

/**
 * This class stores "client_token" data.
 *
 * @package   Genetsis
 * @category  Bean
 * @version   1.0
 * @access    public
 * @since     2011-09-08
 */
class ClientToken extends StoredToken
{

    /**
     * @inheritDoc
     */
    public function __construct ($value, $expires_in = 0, $expires_at = 0, $path = '/')
    {
        $this->name = TokenTypesCollection::CLIENT_TOKEN;
        parent::__construct($value, $expires_in, $expires_at, $path);
    }

}