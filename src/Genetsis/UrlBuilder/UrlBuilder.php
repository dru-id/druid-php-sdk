<?php
namespace Genetsis\UrlBuilder;

use Genetsis\Core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\DruID;
use Genetsis\DruIDFacade;
use Genetsis\Identity\Contracts\IdentityServiceInterface;
use Genetsis\UrlBuilder\Contracts\UrlBuilderServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is used to build the links to different services of Genetsis ID.
 *
 * @package   Genetsis
 * @category  Helper
 * @version   2.0
 * @access    private
 */
class UrlBuilder implements UrlBuilderServiceInterface
{

    /** @var IdentityServiceInterface $identity */
    private $identity;
    /** @var OAuthServiceInterface $oauth */
    protected $oauth;
    /** @var LoggerInterface $logger */
    protected $logger;

    private static $ids = array("email", "screen_name", "national_id", "phone_number");
    private static $location = array("telephone");
    private static $location_address = array("streetAddress", "locality", "region", "postalCode", "country");

    /**
     * @param IdentityServiceInterface $identity
     * @param OAuthServiceInterface $oauth
     * @param LoggerInterface $logger
     */
    public function __construct(IdentityServiceInterface $identity, OAuthServiceInterface $oauth, LoggerInterface $logger)
    {
        $this->identity = $identity;
        $this->oauth = $oauth;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getUrlLogin($entry_point = null, $social = null, $urlCallback = null, array $prefill = array())
    {
        return $this->buildLoginUrl(
            (string)$this->oauth->getConfig()->getEndPoint('authorization_endpoint'),
            (string)$this->oauth->getConfig()->getRedirect('postLogin', $urlCallback),
            $entry_point,
            $social,
            $prefill
        );
    }

    /**
     * @inheritDoc
     */
    public function getUrlRegister($entry_point = null, $urlCallback = null, array $prefill = array())
    {
        return $this->buildSignupUrl(
            (string)$this->oauth->getConfig()->getEndPoint('signup_endpoint'),
            (string)$this->oauth->getConfig()->getRedirect('register', $urlCallback),
            $entry_point,
            $prefill
        );
    }

    /**
     * @inheritDoc
     */
    public function getUrlEditAccount($entry_point = null, $urlCallback = null)
    {
        $params = array();
        $params['client_id'] = $this->oauth->getConfig()->getClientId();
        $params['redirect_uri'] = (string)$this->oauth->getConfig()->getRedirect('postEditAccount', $urlCallback);
        $next_url = ((string)$this->oauth->getConfig()->getEndPoint('next_url') . '?' . http_build_query($params));
        $cancel_url = ((string)$this->oauth->getConfig()->getEndPoint('cancel_url') . '?' . http_build_query($params));
        unset($params);

        return $this->buildEditAccountUrl(
            (string)$this->oauth->getConfig()->getEndPoint('edit_account_endpoint'),
            $next_url,
            $cancel_url,
            $entry_point
        );
    }

    /**
     * @inheritDoc
     */
    public function getUrlCompleteAccount($entry_point = null)
    {
        $params = array();
        $params['client_id'] = $this->oauth->getConfig()->getClientId();
        $params['redirect_uri'] = (string)$this->oauth->getConfig()->getRedirect('postEditAccount');
        $next_url = (string)$this->oauth->getConfig()->getEndPoint('next_url') . '?' . http_build_query($params);
        $cancel_url = (string)$this->oauth->getConfig()->getEndPoint('cancel_url') . '?' . http_build_query($params);
        unset($params);

        return $this->buildCompleteAccountUrl(
            (string)$this->oauth->getConfig()->getEndPoint('complete_account_endpoint'),
            $next_url,
            $cancel_url,
            $entry_point
        );
    }

    /**
     * @inheritDoc
     */
    public function buildSignupPromotionUrl($entry_point)
    {
        try {
            if ($this->checkParam($entry_point)) {
                throw new \Exception ('Scope section is empty');
            }

            if (!$this->identity->isConnected()) {
                return $this->getUrlLogin($entry_point);
            } elseif (!$this->identity->checkUserComplete($entry_point)) {
                return $this->getUrlCompleteAccount($entry_point);
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
        }

        return false;
    }

    /**
     *
     */
    private function arrayToUserJson(array $userInfo) {


        $user = array("objectType" => "user");

        foreach ($userInfo as $field => $value) {
            if (in_array($field, self::$ids)) {
                $user["ids"][$field] = array("value" => $value);
            } else if (in_array($field, self::$location)) {
                $user["location"][$field] = $value;
            } else if (in_array($field, self::$location_address)) {
                $user["location"]["address"][$field] = $value;
            } else { //is a data
                $user["datas"][$field] = array("value" => $value);
            }
        }

        return json_encode($user);
    }

    /**
     * Builds the URL to login process.
     *
     * @param string $endpoint_url The endpoint. Normally the 'authorization_endpoint' of
     *     OAuth server.
     * @param string $redirect_url Where the user will be redirected, even on success or
     *     not.
     * @param string $entry_point Section-key identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @param string $social Social - to force login with social network. Optional. Values 'facebook', 'twitter'
     * @param string $social (optional) You can use this parameter to force user to login with a social network. Accepts
     *      any of those defined at {@link \Genetsis\UrlBuilder\Collections\SocialNetworks}
     * @return string|false The URL generated.
     * @throws \Exception If there is an error.
     */
    private function buildLoginUrl($endpoint_url, $redirect_url, $entry_point = null, $social = null, array $prefill = array())
    {
        try {
            if ($this->checkParam($endpoint_url)) {
                throw new \Exception ('Endpoint URL is empty');
            }
            if ($this->checkParam($redirect_url)) {
                throw new \Exception ('Redirect URL is empty');
            }

            $endpoint_url = rtrim($endpoint_url, '?');
            $params = array();
            $params['client_id'] = $this->oauth->getConfig()->getClientId();
            $params['redirect_uri'] = $redirect_url;
            $params['response_type'] = 'code';
            if (!is_null($entry_point)) {
                $params['scope'] = $entry_point;
            }

            if ($social != null) {
                $params['ck_auth_provider'] = $social;
            }

            if (!empty($prefill)) {
                $params['x_prefill'] = base64_encode($this->arrayToUserJson($prefill));
            }

            return $endpoint_url . '?' . http_build_query($params, null, '&');
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
        }

        return false;
    }

    /**
     * Builds the URL to edit the user's data.
     *
     * @param string $endpoint_url The endpoint. Normally the 'edit_account_endpoint' of
     *     OAuth server.
     * @param string $next_url Where the user will be redirected when finished
     *     editing data.
     * @param string $cancel_url Where the user will be redirected if the process is
     *     cancelled.
     * @param string $entry_point Section-key identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @return string The URL generated.
     * @throws \Exception If there is an error.
     */
    private function buildEditAccountUrl($endpoint_url, $next_url, $cancel_url, $entry_point = null)
    {
        try {
            if ($this->checkParam($endpoint_url)) {
                throw new \Exception ('Endpoint URL is empty');
            }
            if ($this->checkParam($next_url)) {
                throw new \Exception ('Next URL is empty');
            }
            if ($this->checkParam($cancel_url)) {
                throw new \Exception ('Cancel URL is empty');
            }

            $access_token = $this->identity->getThings()->getAccessToken();

            if (is_null($access_token)) {
                throw new \Exception ('Access token is empty');
            }

            $endpoint_url = rtrim($endpoint_url, '?');
            $params = array();
            $params ['next'] = $next_url;
            $params ['cancel_url'] = $cancel_url;
            $params ['oauth_token'] = $access_token->getValue();
            if (!is_null($entry_point)) {
                $params ['scope'] = $entry_point;
            }
            unset ($access_token);

            return $endpoint_url . '?' . http_build_query($params, null, '&');
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
        }

        return false;
    }

    /**
     * Builds the URL to sign up process.
     *
     * @param string $endpoint_url The endpoint. Normally the 'signup_endpoint' of OAuth
     *     server.
     * @param string $redirect_url Where the user will be redirected, even on success or
     *     not.
     * @param string $endpoint_url Section-key identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @return string The URL generated.
     * @throws \Exception If there is an error.
     */
    private function buildSignupUrl($endpoint_url, $redirect_url, $entry_point = null, array $prefill = array())
    {
        try {
            $url = $this->buildLoginUrl($endpoint_url, $redirect_url);
            if ($this->checkParam($url)) {
                throw new \Exception("Can't build sign up URL");
            }

            $params = array();
            $params['x_method'] = 'sign_up';
            if (!is_null($entry_point)) {
                $params ['scope'] = $entry_point;
            }

            if (!empty($prefill)) {
                $params['x_prefill'] = base64_encode($this->arrayToUserJson($prefill));
            }

            return $url . '&' . http_build_query($params, null, '&');
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
        }

        return false;
    }

    /**
     * Builds the URL to fill up data for a specific section.
     *
     * @param string $endpoint_url The endpoint. Normally the 'edit_account_endpoint' of
     *     OAuth server.
     * @param string $next_url Where the user will be redirected when finished
     *     fill up data.
     * @param string $cancel_url Where the user will be redirected if the process is
     *     cancelled.
     * @param string $entry_point Section-key identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @return string The URL generated.
     * @throws \Exception If there is an error.
     */
    private function buildCompleteAccountUrl($endpoint_url, $next_url, $cancel_url, $entry_point)
    {
        try {
            if ($this->checkParam($endpoint_url)) {
                throw new \Exception ('Endpoint URL is empty');
            }
            if ($this->checkParam($next_url)) {
                throw new \Exception ('Next URL is empty');
            }
            if ($this->checkParam($cancel_url)) {
                throw new \Exception ('Cancel URL is empty');
            }
            $access_token = $this->identity->getThings()->getAccessToken();

            if (is_null($access_token)) {
                throw new \Exception ('Access token is empty');
            }
            if ($this->checkParam($entry_point)) {
                throw new \Exception ('Scope section is empty');
            }

            $endpoint_url = rtrim($endpoint_url, '?');
            $params = array();
            $params ['next'] = $next_url;
            $params ['cancel_url'] = $cancel_url;
            $params ['oauth_token'] = $access_token->getValue();
            unset ($access_token);
            $params['scope'] = $entry_point;

            return $endpoint_url . '?' . http_build_query($params, null, '&');
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
        }

        return false;
    }

    /**
     * Check if param is null or empty or blank
     *
     * @param string $param The string to validate
     * @return bool True if is null, empty or blank, False in other case
     */
    private function checkParam($param)
    {
        $param = trim($param);
        return empty($param);
    }
}