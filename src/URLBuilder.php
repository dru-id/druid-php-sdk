<?php
namespace Genetsis;

use Exception;
use Genetsis\core\ServiceContainer\Services\ServiceContainer as SC;

/**
 * This class is used to build the links to different services of Genetsis ID.
 *
 * @package   Genetsis
 * @category  Helper
 * @version   2.0
 * @access    private
 */
class URLBuilder
{
    private static $ids = array("email", "screen_name", "national_id", "phone_number");
    private static $location = array("telephone");
    private static $location_address = array("streetAddress", "locality", "region", "postalCode", "country");

    /**
     * Returns the link for login process.
     *
     * @param string $scope Section-key Identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file. If it's NULL,
     *     the default section will be used.
     * @param string $social - to force login with social network. Optional. Values 'facebook', 'twitter'
     * @param string $urlCallback Url for callback. A list of valid url is defined in "oauthconf.xml"
     *     If it's NULL default url will be used.
     * @param string $urlCallback Url for callback. A list of valid url is defined in "oauthconf.xml"
     *     If it's NULL default url will be used.
     * @return string The URL for login process.
     */
    public static function getUrlLogin($scope = null, $social = null, $urlCallback = null, array $prefill = array())
    {
        return self::buildLoginUrl(
            (string)SC::getOAuthService()->getConfig()->getEndPoint('authorization_endpoint'),
            (string)SC::getOAuthService()->getConfig()->getRedirect('postLogin', $urlCallback),
            $scope,
            $social,
            $prefill
        );
    }

    /**
     * Returns the link for register form page.
     *
     * @param string $scope Section-key Identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file. If it's NULL,
     *     the default section will be used.
     * @param string $urlCallback Url for callback. A list of url is defined in "oauthconf.xml"
     *     If it's NULL the default url will be used.
     * @return string The URL for register process.
     */
    public static function getUrlRegister($scope = null, $urlCallback = null, array $prefill = array())
    {
        return self::buildSignupUrl(
            (string)SC::getOAuthService()->getConfig()->getEndPoint('signup_endpoint'),
            (string)SC::getOAuthService()->getConfig()->getRedirect('register', $urlCallback),
            $scope,
            $prefill
        );
    }

    /**
     * Returns the link for edit account form page.
     *
     * @param string $scope Section-key Identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file. If it's NULL,
     *     the default section will be used.
     * @param string $urlCallback Url for callback. A list of url is defined in "oauthconf.xml"
     *     If it's NULL the default url will be used.
     * @return string The URL for edit account process.
     */
    public static function getUrlEditAccount($scope = null, $urlCallback = null)
    {
        $params = array();
        $params['client_id'] = SC::getOAuthService()->getConfig()->getClientId();
        $params['redirect_uri'] = (string)SC::getOAuthService()->getConfig()->getRedirect('postEditAccount', $urlCallback);
        $next_url = ((string)SC::getOAuthService()->getConfig()->getEndPoint('next_url') . '?' . http_build_query($params));
        $cancel_url = ((string)SC::getOAuthService()->getConfig()->getEndPoint('cancel_url') . '?' . http_build_query($params));
        unset($params);

        return self::buildEditAccountUrl(
            (string)SC::getOAuthService()->getConfig()->getEndPoint('edit_account_endpoint'),
            $next_url,
            $cancel_url,
            $scope
        );
    }

    /**
     * Returns the URL to complete the account for a section (scope) given.
     *
     * @param string $scope Section-key Identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @return string The URL for complete process.
     */
    public static function getUrlCompleteAccount($scope = null)
    {
        $params = array();
        $params['client_id'] = SC::getOAuthService()->getConfig()->getClientId();
        $params['redirect_uri'] = (string)SC::getOAuthService()->getConfig()->getRedirect('postEditAccount');
        $next_url = (string)SC::getOAuthService()->getConfig()->getEndPoint('next_url') . '?' . http_build_query($params);
        $cancel_url = (string)SC::getOAuthService()->getConfig()->getEndPoint('cancel_url') . '?' . http_build_query($params);
        unset($params);

        return self::buildCompleteAccountUrl(
            (string)SC::getOAuthService()->getConfig()->getEndPoint('complete_account_endpoint'),
            $next_url,
            $cancel_url,
            $scope
        );
    }

    /**
     * This method is commonly used for promotions or sweepstakes: if a
     * user wants to participate in a promotion, the web client must
     * ensure that the user is logged and have all the fields filled
     * in order to let him participate.
     *
     * - If it is not logged, will return the login URL.
     * - If it is logged the method will check
     *     - If the user have not enough PII to access to a section,
     *       returns the URL needed to force a consumer to fill all the
     *       PII needed to enter into a section
     *     - Else will return false (user logged and completed)
     *
     * The "scope" (section) is a group of fields configured in Genetsis ID for
     * a web client.
     *
     * A section can be also defined as a "part" (section) of the website
     * (web client) that only can be accesed by a user who have filled a
     * set of personal information configured in Genetsis ID (all of the fields
     * required for that section).
     *
     * @param string Section-key Identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @return string With generated URL. If the user is not connected,
     *     will return login URL.
     * @throws Exception if scope is empty.
     */
    public static function buildSignupPromotionUrl($scope)
    {
        try {
            if (self::checkParam($scope)) {
                throw new \Exception ('Scope section is empty');
            }

            if (!Identity::isConnected()) {
                return sefl::getUrlLogin($scope);
            } else {
                if (!Identity::checkUserComplete($scope)) {
                    return self::getUrlCompleteAccount($scope);
                }
            }
            return false;
        } catch (\Exception $e) {
            SC::getLogger()->debug($e->getMessage(), __METHOD__, __LINE__);
        }
    }

    /**
     *
     */
    private static function arrayToUserJson(array $userInfo) {


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
     * @param string $scope Section-key identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @param string $social Social - to force login with social network. Optional. Values 'facebook', 'twitter'
     * @return string The URL generated.
     * @throws \Exception If there is an error.
     */
    private static function buildLoginUrl($endpoint_url, $redirect_url, $scope = null, $social = null, array $prefill = array())
    {

        try {
            if (self::checkParam($endpoint_url)) {
                throw new Exception ('Endpoint URL is empty');
            }
            if (self::checkParam($redirect_url)) {
                throw new Exception ('Redirect URL is empty');
            }

            $endpoint_url = rtrim($endpoint_url, '?');
            $params = array();
            $params['client_id'] = SC::getOAuthService()->getConfig()->getClientId();
            $params['redirect_uri'] = $redirect_url;
            $params['response_type'] = 'code';
            if (!is_null($scope)) {
                $params['scope'] = $scope;
            }

            if ($social != null) {
                $params['ck_auth_provider'] = $social;
            }

            if (!empty($prefill)) {
                $params['x_prefill'] = base64_encode(self::arrayToUserJson($prefill));
            }

            return $endpoint_url . '?' . http_build_query($params, null, '&');
        } catch (Exception $e) {
            SC::getLogger()->debug($e->getMessage(), __METHOD__, __LINE__);
        }
    }

    /**
     * Builds the URL to edit the user's data.
     *
     * @param string The endpoint. Normally the 'edit_account_endpoint' of
     *     OAuth server.
     * @param string Where the user will be redirected when finished
     *     editing data.
     * @param string Where the user will be redirected if the process is
     *     cancelled.
     * @param string Section-key identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @return string The URL generated.
     * @throws \Exception If there is an error.
     */
    private static function buildEditAccountUrl($endpoint_url, $next_url, $cancel_url, $scope = null)
    {
        try {
            if (self::checkParam($endpoint_url)) {
                throw new Exception ('Endpoint URL is empty');
            }
            if (self::checkParam($next_url)) {
                throw new Exception ('Next URL is empty');
            }
            if (self::checkParam($cancel_url)) {
                throw new Exception ('Cancel URL is empty');
            }

            $access_token = Identity::getThings()->getAccessToken();

            if (is_null($access_token)) {
                throw new Exception ('Access token is empty');
            }

            $endpoint_url = rtrim($endpoint_url, '?');
            $params = array();
            $params ['next'] = $next_url;
            $params ['cancel_url'] = $cancel_url;
            $params ['oauth_token'] = $access_token->getValue();
            if (!is_null($scope)) {
                $params ['scope'] = $scope;
            }
            unset ($access_token);

            return $endpoint_url . '?' . http_build_query($params, null, '&');
        } catch (Exception $e) {
            SC::getLogger()->debug($e->getMessage(), __METHOD__, __LINE__);
        }
    }

    /**
     * Builds the URL to sign up process.
     *
     * @param string The endpoint. Normally the 'signup_endpoint' of OAuth
     *     server.
     * @param string Where the user will be redirected, even on success or
     *     not.
     * @param string Section-key identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @return string The URL generated.
     * @throws \Exception If there is an error.
     */
    private static function buildSignupUrl($endpoint_url, $redirect_url, $scope = null, array $prefill = array())
    {
        try {

            $url = self::buildLoginUrl($endpoint_url, $redirect_url);
            if (self::checkParam($url)) {
                throw new Exception("Can't build sign up URL");
            }

            $params = array();
            $params['x_method'] = 'sign_up';
            if (!is_null($scope)) {
                $params ['scope'] = $scope;
            }

            if (!empty($prefill)) {
                $params['x_prefill'] = base64_encode(self::arrayToUserJson($prefill));
            }

            return $url . '&' . http_build_query($params, null, '&');
        } catch (Exception $e) {
            SC::getLogger()->debug($e->getMessage(), __METHOD__, __LINE__);
        }
    }

    /**
     * Builds the URL to fill up data for a specific section.
     *
     * @param string The endpoint. Normally the 'edit_account_endpoint' of
     *     OAuth server.
     * @param string Where the user will be redirected when finished
     *     fill up data.
     * @param string Where the user will be redirected if the process is
     *     cancelled.
     * @param string Section-key identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @return string The URL generated.
     * @throws \Exception If there is an error.
     */
    private static function buildCompleteAccountUrl($endpoint_url, $next_url, $cancel_url, $scope)
    {
        try {
            if (self::checkParam($endpoint_url)) {
                throw new Exception ('Endpoint URL is empty');
            }
            if (self::checkParam($next_url)) {
                throw new Exception ('Next URL is empty');
            }
            if (self::checkParam($cancel_url)) {
                throw new Exception ('Cancel URL is empty');
            }
            $access_token = Identity::getThings()->getAccessToken();

            if (is_null($access_token)) {
                throw new Exception ('Access token is empty');
            }
            if (self::checkParam($scope)) {
                throw new Exception ('Scope section is empty');
            }

            $endpoint_url = rtrim($endpoint_url, '?');
            $params = array();
            $params ['next'] = $next_url;
            $params ['cancel_url'] = $cancel_url;
            $params ['oauth_token'] = $access_token->getValue();
            unset ($access_token);
            $params['scope'] = $scope;

            return $endpoint_url . '?' . http_build_query($params, null, '&');
        } catch (Exception $e) {
            SC::getLogger()->debug($e->getMessage(), __METHOD__, __LINE__);
        }
    }

    /**
     * Check if param is null or empty or blank
     *
     * @param string $param The string to validate
     * @return bool True if is null, empty or blank, False in other case
     */
    private static function checkParam($param)
    {
        $param = trim($param);
        return empty($param);
    }
}