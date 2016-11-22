<?php namespace Genetsis\core\User\Beans;

use Genetsis\core\OAuth\Beans\ClientToken;
use Genetsis\core\OAuth\Beans\AccessToken;
use Genetsis\core\OAuth\Beans\RefreshToken;

/**
 * This class stores data from user's session generated by Genetsis ID.
 *
 * @package   Genetsis
 * @category  Bean
 * @version   1.0
 * @access    private
 */
class Things
{
    /** @var ClientToken|null $client_token */
    private $client_token = null;
    /** @var AccessToken|null $access_token */
    private $access_token = null;
    /** @var RefreshToken|null $refresh_token */
    private $refresh_token = null;
    /** @var LoginStatus $login_status */
    private $login_status = null;

    /**
     * @param array $settings Initial settings. Array structure:
     *      [
     *          'client_token' => {@see Things::setClientToken},
     *          'access_token' => {@see Things::setAccessToken},
     *          'refresh_token' => {@see Things::setRefreshToken},
     *          'login_status' => {@see Things::setLoginStatus},
     *      ]
     */
    public function __construct(array $settings = [])
    {
        if (isset($settings['client_token'])) { $this->setClientToken($settings['client_token']); }
        if (isset($settings['access_token'])) { $this->setAccessToken($settings['access_token']); }
        if (isset($settings['refresh_token'])) { $this->setRefreshToken($settings['refresh_token']); }
        if (isset($settings['login_status'])) { $this->setLoginStatus($settings['login_status']); }
    }

    /**
     * @return ClientToken|null
     */
    public function getClientToken()
    {
        return $this->client_token;
    }

    /**
     * @param ClientToken|null $token An instance of {@link ClientToken} or NULL to remove it.
     * @return Things
     */
    public function setClientToken($token)
    {
        if (($token instanceof ClientToken) || is_null($token)) {
            if (isset($this->client_token)) { unset($this->client_token); }
            $this->client_token = $token;
        }
        return $this;
    }

    /**
     * @return AccessToken|null
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @param AccessToken|null $token An instance of {@link AccessToken} or NULL to remove it.
     * @return Things
     */
    public function setAccessToken($token)
    {
        if (($token instanceof AccessToken) || is_null($token)) {
            if (isset($this->access_token)) { unset($this->access_token); }
            $this->access_token = $token;
        }
        return $this;
    }

    /**
     * @return RefreshToken|null
     */
    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    /**
     * @param RefreshToken|null $token An instance of {@link RefreshToken} or NULL to remove it.
     * @return Things
     */
    public function setRefreshToken($token)
    {
        if (($token instanceof RefreshToken) || is_null($token)) {
            if (isset($this->refresh_token)) { unset($this->refresh_token); }
            $this->refresh_token = $token;
        }
        return $this;
    }

    /**
     * @return LoginStatus|null
     */
    public function getLoginStatus()
    {
        return $this->login_status;
    }

    /**
     * @param LoginStatus|null $login_status A instance of {@link LoginStatus} or NULL to remove it.
     * @return Things
     */
    public function setLoginStatus($login_status)
    {
        if (($login_status instanceof LoginStatus) || is_null($login_status)) {
            if (isset($this->login_status)) { unset($this->login_status); }
            $this->login_status = $login_status;
        }
        return $this;
    }
}