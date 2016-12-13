<?php namespace Genetsis\core\OAuth\Services;

use Genetsis\core\Encryption\Services\Encryption;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
use Genetsis\core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\core\OAuth\Exceptions\InvalidGrantException;
use Genetsis\core\User\Beans\LoginStatus;
use Genetsis\DruID;
use Genetsis\core\OAuth\Beans\StoredToken;
use Genetsis\core\OAuth\Beans\AccessToken;
use Genetsis\core\OAuth\Beans\RefreshToken;
use Genetsis\core\OAuth\Beans\ClientToken;
use Genetsis\core\OAuth\Contracts\StoredTokenInterface;
use Genetsis\core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\core\OAuth\Collections\AuthMethods as AuthMethodsCollection;
use Genetsis\core\OAuth\Collections\TokenTypes as TokenTypesCollection;
use Genetsis\core\Http\Collections\HttpMethods as HttpMethodsCollection;

/**
 * This class wraps all methods for interactions with OAuth service,
 * for user authentication and validation. Also generates the URLs to
 * perform this operations as register or login.
 *
 * @package   Genetsis
 * @category  Helper
 * @version   1.0
 * @access    private
 */
class OAuth implements OAuthServiceInterface
{
    /** Default expiration time. In seconds. */
    const DEFAULT_EXPIRES_IN = 900;
    /** Indicates the percentage to be subtracted from the number of
     * seconds of "expires_in" to not be so close to the expiration date
     * of the token. */
    const SAFETY_RANGE_EXPIRES_IN = 0.10; # 10%
    /** Cookie name for SSO (Single Sign-Out). */
    const SSO_COOKIE_NAME = 'datr';

    /** @var Config $config */
    private $config = null;
    /** @var HttpServiceInterface $http */
    private $http;
    /** @var LoggerServiceInterface $logger */
    private $logger;

    /**
     * @param Config $config
     * @param HttpServiceInterface $http
     * @param LoggerServiceInterface $logger
     */
    public function __construct(Config $config, HttpServiceInterface $http, LoggerServiceInterface $logger)
    {
        $this->setConfig($config);
        $this->http = $http;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function setConfig(Config $config)
    {
        $this->config = clone $config;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function doGetClientToken ($endpoint_url)
    {
        try {
            if (($endpoint_url = trim(( string )$endpoint_url)) == '') {
                throw new \Exception ('Endpoint URL is empty');
            }

            $params = array();
            $params['grant_type'] = AuthMethodsCollection::GRANT_TYPE_CLIENT_CREDENTIALS;
            $params['client_id'] = $this->getConfig()->getClientId();
            $params['client_secret'] = $this->getConfig()->getClientSecret();
            $response = $this->http->execute($endpoint_url, $params, HttpMethodsCollection::POST);

            $this->checkErrors($response);

            if (!isset ($response['result']->access_token) || ($response['result']->access_token == '')) {
                throw new \Exception ('The client_token retrieved is empty');
            }

            $expires_in = self::DEFAULT_EXPIRES_IN;
            if (isset ($response['result']->expires_in)) {
                $expires_in = intval($response['result']->expires_in);
            }

            $expires_in = ($expires_in - ($expires_in * self::SAFETY_RANGE_EXPIRES_IN));
            $expires_at = (time() + $expires_in);
            $client_token = new ClientToken(trim($response['result']->access_token), $expires_in, $expires_at, '/');
            $this->storeToken($client_token);

            return $client_token;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), __METHOD__, __LINE__);
            throw $e;
        }
    }

    /**
     * Checks if there are errors in the response.
     *
     * @param array $response Where we will search errors.
     * @return void
     * @throws \Exception If there is an error in the response.
     */
    private function checkErrors($response)
    {
        if (isset($response['result']->error)) {
            if (isset($response['result']->type)) {
                switch ($response['result']->type) {
                    case 'InvalidGrantException' :
                        throw new InvalidGrantException($response['result']->error . ' (' . (isset($response['result']->type) ? trim($response['result']->type) : '') . ')');
                }
            }
            throw new \Exception($response['result']->error . ' (' . (isset($response['result']->type) ? trim($response['result']->type) : '') . ')');
        }
        if (isset($response['code']) && ($response['code'] != 200)) {
            throw new \Exception('Error: ' .$response['code']);
        }
    }

    /**
     * Stores a token in a cookie
     *
     * @param StoredTokenInterface $token An object with token data to be stored.
     * @throws \Exception
     */
    public function storeToken (StoredTokenInterface $token)
    {
        @setcookie($token->getName(), (new Encryption($this->config->getClientId()))->encode($token->getValue()), $token->getExpiresAt(), $token->getPath(), '', false, true);
    }

    /**
     * Gets an "access_token" for the current web client.
     *
     * @param string $endpoint_url The endpoint where "access_token" is requested.
     * @param string $code The authorization code returned by Genetsis ID.
     * @param string $redirect_url Where the user will be redirected.
     * @return array An array with the following data:
     *      [
     *          'access_token' => An instance of {@link AccessToken}
     *          'refresh_token' => An instance of {@link RefreshToken}
     *          'login_status' => An instance of {@link LoginStatus}
     *      ]
     * @throws \Exception If there is an error.
     * @throws InvalidGrantException
     */
    public function doGetAccessToken ($endpoint_url, $code, $redirect_url)
    {
        try {
            if (($endpoint_url = trim(( string )$endpoint_url)) == '') {
                throw new \Exception ('Endpoint URL is empty');
            }
            if (($code = trim(( string )$code)) == '') {
                throw new \Exception ('Code is empty');
            }
            if (($redirect_url = trim(( string )$redirect_url)) == '') {
                throw new \Exception ('Redirect URL is empty');
            }

            $params = [];
            $params['grant_type'] = AuthMethodsCollection::GRANT_TYPE_AUTH_CODE;
            $params['code'] = $code;
            $params['redirect_uri'] = $redirect_url;
            $params['client_id'] = $this->getConfig()->getClientId();
            $params['client_secret'] = $this->getConfig()->getClientSecret();
            $response = $this->http->execute($endpoint_url, $params, HttpMethodsCollection::POST);

            $this->checkErrors($response);

            if (!isset ($response ['result']->access_token) || ($response ['result']->access_token == '')) {
                throw new \Exception ('The access_token retrieved is empty');
            }
            if (!isset ($response ['result']->refresh_token) || ($response ['result']->refresh_token == '')) {
                throw new \Exception ('The refresh_token retrieved is empty');
            }

            $expires_in = self::DEFAULT_EXPIRES_IN;
            if (isset ($response['result']->expires_in)) {
                $expires_in = intval($response['result']->expires_in);
            }
            $expires_in = ($expires_in - ($expires_in * self::SAFETY_RANGE_EXPIRES_IN));
            $expires_at = (time() + $expires_in);
            $refresh_expires_at = ($expires_at + (60*60*24*12));

            $result['access_token'] = new AccessToken(trim($response ['result']->access_token), $expires_in, $expires_at, '/');
            $result['refresh_token'] = new RefreshToken(trim($response ['result']->refresh_token), 0, $refresh_expires_at, '/');

            $this->storeToken($result['access_token']);
            $this->storeToken($result['refresh_token']);

            $loginStatus = new LoginStatus();
            if (isset($response['result']->login_status)) {
                $loginStatus->setCkusid(isset($response['result']->login_status->uid) ? $response['result']->login_status->uid : '');
                $loginStatus->setOid(isset($response['result']->login_status->oid) ? $response['result']->login_status->oid : '');
                $loginStatus->setConnectState(isset($response['result']->login_status->connect_state) ? $response['result']->login_status->connect_state : '');
            }

            $result['login_status'] = $loginStatus;

            return $result;
        } catch (InvalidGrantException $e) {
            throw new InvalidGrantException('Error [' . __FUNCTION__ . '] - Maybe "code" is reused - '.$e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Error [' . __FUNCTION__ . '] - '.$e->getMessage());
        }
    }

    /**
     * Updates tokens.
     *
     * @param string $endpoint_url The endpoint where the request will be sent.
     * @return boolean TRUE if the tokens have been updated or FALSE
     *     otherwise.
     * @throws \Exception If there is an error.
     */
    public function doRefreshToken($endpoint_url)
    {
        try {
            if (($endpoint_url = trim(( string )$endpoint_url)) == '') {
                throw new \Exception ('Endpoint URL is empty');
            }
            if (!($refresh_token = DruID::identity()->getThings()->getRefreshToken()) instanceof RefreshToken) { // TODO: "Things" shouldn't be used like that.
                throw new \Exception ('Refresh token is empty');
            }

            // Send request.
            $params = array();
            $params['grant_type'] = AuthMethodsCollection::GRANT_TYPE_REFRESH_TOKEN;
            $params['refresh_token'] = $refresh_token->getValue();
            $params['client_id'] = $this->getConfig()->getClientId();
            $params['client_secret'] = $this->getConfig()->getClientSecret();
            $response = $this->http->execute($endpoint_url, $params, HttpMethodsCollection::POST);

            $this->checkErrors($response);

            if (!isset ($response ['result']->access_token) || ($response ['result']->access_token == '')) {
                throw new \Exception ('The access_token retrieved is empty');
            }
            if (!isset ($response ['result']->refresh_token) || ($response ['result']->refresh_token == '')) {
                throw new \Exception ('The refresh_token retrieved is empty');
            }

            $expires_in = self::DEFAULT_EXPIRES_IN;
            if (isset ($response['result']->expires_in)) {
                $expires_in = intval($response['result']->expires_in);
            }
            $expires_in = ($expires_in - ($expires_in * self::SAFETY_RANGE_EXPIRES_IN));
            $expires_at = (time() + $expires_in);
            $refresh_expires_at = ($expires_at + (60*60*24*12));

            $result['access_token'] = new AccessToken (trim($response ['result']->access_token), $expires_in, $expires_at, '/');
            $result['refresh_token'] = new RefreshToken (trim($response ['result']->refresh_token), 0, $refresh_expires_at, '/');

            self::storeToken($result['access_token']);
            self::storeToken($result['refresh_token']);

            $loginStatus = new LoginStatus();
            if (isset ($response ['result']->login_status)) {
                $loginStatus->setCkusid($response['result']->login_status->uid);
                $loginStatus->setOid($response['result']->login_status->oid);
                $loginStatus->setConnectState($response['result']->login_status->connect_state);
            }
            $result['login_status'] = $loginStatus;

            return $result;
        } catch (InvalidGrantException $e) {
            throw new InvalidGrantException('Error [' . __FUNCTION__ . '] - '.$e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Error [' . __FUNCTION__ . '] - '.$e->getMessage());
        }
    }

    /**
     * Checks if user is logged.
     *
     * @param string $endpoint_url The endpoint where the request will be sent.
     * @return LoginStatus An object with user status.
     * @throws \Exception If there is an error.
     */
    public function doValidateBearer($endpoint_url)
    {
        try {
            if (($endpoint_url = trim(( string )$endpoint_url)) == '') {
                throw new \Exception ('Endpoint URL is empty');
            }
            if (!(($access_token = DruID::identity()->getThings()->getAccessToken()) instanceof AccessToken) || ($access_token->getValue() == '')) {
                throw new \Exception ('Access token is empty');
            }

            $params = array();
            $params['grant_type'] = AuthMethodsCollection::GRANT_TYPE_VALIDATE_BEARER;
            $params['oauth_token'] = $access_token->getValue();
            $params['client_id'] = $this->getConfig()->getClientId();
            $params['client_secret'] = $this->getConfig()->getClientSecret();
            $response = $this->http->execute($endpoint_url, $params, HttpMethodsCollection::POST);
            unset ($access_token);

            $this->checkErrors($response);

            $loginStatus = new LoginStatus();
            if (isset ($response ['result']->login_status)) {
                $loginStatus->setCkusid($response['result']->login_status->uid);
                $loginStatus->setOid($response['result']->login_status->oid);
                $loginStatus->setConnectState($response['result']->login_status->connect_state);
            }

            return $loginStatus;
        } catch (InvalidGrantException $e) {
            throw new InvalidGrantException('Error [' . __FUNCTION__ . '] - '.$e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Error [' . __FUNCTION__ . '] - '.$e->getMessage());
        }
    }

    /**
     * Checks if user is logged by Exchange Session (SSO)
     *
     * @param string $endpoint_url The endpoint where the request will be sent.
     * @param string $cookie_value The content of the cookie that stores the SSO.
     * @return mixed An instance of {@link AccessToken} if its connected or
     *     NULL if not.
     * @throws \Exception If there is an error.
     */
    public function doExchangeSession($endpoint_url, $cookie_value)
    {
        try {
            $access_token = null;

            if (($endpoint_url = trim(( string )$endpoint_url)) == '') {
                throw new \Exception ('Endpoint URL is empty');
            }
            if (($cookie_value = trim($cookie_value)) == '') {
                throw new \Exception ('SSO cookie is empty');
            }

            $params = array();
            $params['grant_type'] = AuthMethodsCollection::GRANT_TYPE_EXCHANGE_SESSION;
            $params['client_id'] = $this->getConfig()->getClientId();
            $params['client_secret'] = $this->getConfig()->getClientSecret();
            $response = $this->http->execute($endpoint_url, $params, HttpMethodsCollection::POST, [], [self::SSO_COOKIE_NAME . '=' . $cookie_value]);

            $this->checkErrors($response);

            if (!isset ($response ['result']->access_token) || ($response ['result']->access_token == '')) {
                throw new \Exception ('The access_token retrieved is empty');
            }
            if (!isset ($response ['result']->refresh_token) || ($response ['result']->refresh_token == '')) {
                throw new \Exception ('The refresh_token retrieved is empty');
            }

            $expires_in = self::DEFAULT_EXPIRES_IN;
            if (isset ($response['result']->expires_in)) {
                $expires_in = intval($response['result']->expires_in);
            }
            $expires_in = ($expires_in - ($expires_in * self::SAFETY_RANGE_EXPIRES_IN));
            $expires_at = (time() + $expires_in);
            $refresh_expires_at = ($expires_at + (60*60*24*12));

            $result['access_token'] = new AccessToken (trim($response ['result']->access_token), $expires_in, $expires_at, '/');
            $result['refresh_token'] = new RefreshToken (trim($response ['result']->refresh_token), 0, $refresh_expires_at, '/');

            self::storeToken($result['access_token']);
            self::storeToken($result['refresh_token']);

            $loginStatus = new LoginStatus();
            if (isset ($response ['result']->login_status)) {
                $loginStatus->setCkusid($response['result']->login_status->uid);
                $loginStatus->setOid($response['result']->login_status->oid);
                $loginStatus->setConnectState($response['result']->login_status->connect_state);
            }

            $result['login_status'] = $loginStatus;

            return $result;
        } catch (InvalidGrantException $e) {
            throw new InvalidGrantException('Error [' . __FUNCTION__ . '] - '.$e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Error [' . __FUNCTION__ . '] - '.$e->getMessage());
        }
    }

    /**
     * Performs revocation process. Removes all tokens from that user.
     *
     * @param string $endpoint_url The endpoint where the request will be sent.
     * @return void
     * @throws \Exception If there is an error.
     */
    public function doLogout($endpoint_url)
    {
        try {
            if (($endpoint_url = trim(( string )$endpoint_url)) == '') {
                throw new \Exception ('Endpoint URL is empty');
            }
            if (!($refresh_token = DruID::identity()->getThings()->getRefreshToken()) instanceof RefreshToken) {
                throw new \Exception ('Refresh token is empty');
            }

            $params = array();
            $params['token'] = $refresh_token->getValue();
            $params['token_type'] = 'refresh_token';
            $params['client_id'] = $this->getConfig()->getClientId();
            $params['client_secret'] = $this->getConfig()->getClientSecret();
            $this->http->execute($endpoint_url, $params, HttpMethodsCollection::POST);
            unset ($refresh_token);

            unset($_COOKIE[self::SSO_COOKIE_NAME]);
            setcookie(self::SSO_COOKIE_NAME, null, -1,null,'.cocacola.es');

            self::deleteStoredToken(TokenTypesCollection::ACCESS_TOKEN);
            self::deleteStoredToken(TokenTypesCollection::REFRESH_TOKEN);

        } catch (\Exception $e) {
            throw new \Exception('Error [' . __FUNCTION__ . '] - '.$e->getMessage());
        }
    }

    /**
     * Removes a specific token.
     *
     * It will removed from SESSION and COOKIE.
     *
     * @param string $name The token we want to remove. Are defined in {@link TokenTypesCollection}
     * @return void
     */
    public function deleteStoredToken ($name)
    {
        if (isset($_COOKIE[$name])) {
            setcookie($name, '', time()-42000, '/');
            unset($_COOKIE[$name]);
        }
    }


    /**
     * Checks if we have a specific token.
     *
     * @param string $name The token we want to check. Are defined in {@link TokenTypesCollection}
     * @return bool TRUE if exists or FALSE otherwise.
     */
    public function hasToken($name)
    {
        return (self::getStoredToken($name) instanceof StoredTokenInterface);
    }

    /**
     * Returns a specific stored token.
     * SESSION has more priority than COOKIE.
     *
     * @param string $name The token we want to recover. Are defined in {@link TokenTypesCollection}
     * @return bool|AccessToken|ClientToken|RefreshToken|mixed|string An instance of {@link StoredTokenInterface} or FALSE if we
     *     can't recover it.
     * @throws \Exception
     */
    public function getStoredToken($name)
    {
        if (($name = trim((string)$name)) == '') {
            throw new \Exception ('Token type not exist');
        }

        return (isset($_COOKIE[$name]) && $_COOKIE[$name])
            ? StoredToken::factory($name, (new Encryption($this->config->getClientId()))->decode($_COOKIE[$name]), 0, 0, '/')
            : null;
    }

    /**
     * Get The Url for access to the Opinator.
     *
     * @param string $endpoint_url The endpoint where the request will be sent.
     * @param string $scope Section-key identifier of the web client propietary of Opinator
     * @param StoredTokenInterface $token Token
     * @return mixed $token Token, an access_token if user is logged, a client_token if user is not login
     * @throws \Exception If there is an error.
     */
    public function doGetOpinator($endpoint_url, $scope, StoredTokenInterface $token)
    {
        try {
            if (($endpoint_url = trim(( string )$endpoint_url)) == '') {
                throw new \Exception ('Endpoint Opinator URL is empty');
            }

            if (($scope = trim((string)$scope)) == '') {
                throw new \Exception ('Scope is empty');
            }

            if ($token->getValue() == '') {
                throw new \Exception ('Token is not valid');
            }

            // Send request.
            $params = array();
            $params['oauth_token'] = $token->getValue();
            $params['client_id'] = $this->getConfig()->getClientId();
            $params['client_secret'] = $this->getConfig()->getClientSecret();
            $response = $this->http->execute($endpoint_url . '/' . $scope, $params, HttpMethodsCollection::POST);

            if (isset($response['code']) && ($response['code'] == 200)) {
                return $response['result'];
            } else {
                throw new \Exception('Error [' . __FUNCTION__ . '] - ' . $response['code'] . ' - ' . $response['result']);
            }

        } catch (\Exception $e) {
            throw new \Exception('Error [' . __FUNCTION__ . '] - ' . $e->getMessage());
        }
    }

    /**
     * Checks if the user has completed all required data for the specified
     * section (scope).
     *
     * @param string $endpoint_url The endpoint where the request will be sent.
     * @param string $scope Section-key identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @return boolean TRUE if the user has completed all required data or
     *     FALSE if not.
     * @throws \Exception If there is an error.
     */
    public function doCheckUserCompleted($endpoint_url, $scope)
    {
        try {
            if (($endpoint_url = trim(( string )$endpoint_url)) == '') {
                throw new \Exception ('Endpoint URL is empty');
            }

            if (($scope = trim((string)$scope)) == '') {
                throw new \Exception ('Scope is empty');
            }

            if (!(($access_token = DruID::identity()->getThings()->getAccessToken()) instanceof AccessToken) || ($access_token->getValue() == '')) {
                throw new \Exception ('Access token is empty');
            }

            // Send request.
            $params = array();
            $params['oauth_token'] = $access_token->getValue();
            $params['s'] = "needsToCompleteData";
            $params['f'] = "UserMeta";
            $params['w.section'] = $scope;

            $response = $this->http->execute($endpoint_url, $params, HttpMethodsCollection::POST);

            $this->checkErrors($response);

            if (isset($response['code']) && ($response['code'] == 200)) {
                return (($response['result']->data[0]->meta->value) === 'false') ? true : false;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception('Error [' . __FUNCTION__ . '] - '.$e->getMessage());
        }
    }

    /**
     * Checks if the user has accepted terms and conditions for the specified section (scope).
     *
     * @param string $endpoint_url The endpoint where the request will be sent.
     * @param string $scope Section-key identifier of the web client. The section-key is located in "oauthconf.xml" file.
     * @return boolean TRUE if the user need to accept the terms and conditions (not accepted yet) or
     *      FALSE if it has already accepted them (no action required).
     * @throws \Exception If there is an error.
     */
    public function doCheckUserNeedAcceptTerms($endpoint_url, $scope)
    {
        try {
            if (($endpoint_url = trim(( string )$endpoint_url)) == '') {
                throw new \Exception ('Endpoint URL is empty');
            }

            if (($scope = trim((string)$scope)) == '') {
                throw new \Exception ('Scope is empty');
            }

            if (!(($access_token = DruID::identity()->getThings()->getAccessToken()) instanceof AccessToken) || ($access_token->getValue() == '')) {
                throw new \Exception ('Access token is empty');
            }

            // Send request.
            $params = array();
            $params['oauth_token'] = $access_token->getValue();
            $params['s'] = "needsToCompleteData";
            $params['f'] = "UserMeta";
            $params['w.section'] = $scope;

            $response = $this->http->execute($endpoint_url, $params, HttpMethodsCollection::POST);

            $this->checkErrors($response);

            if (isset($response['code']) && ($response['code'] == 200)) {
                return call_user_func(function($result){
                        if (isset($result->data) && is_array($result->data)) {
                            foreach ($result->data as $data) {
                                if (isset($data->meta->name) && ($data->meta->name === 'needsToAcceptTerms')) {
                                    return (isset($data->meta->value) && ($data->meta->value === 'true'));
                                }
                            }
                        }
                        return false;
                    }, $response['result']);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception('Error [' . __FUNCTION__ . '] - '.$e->getMessage());
        }
    }
}