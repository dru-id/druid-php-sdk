<?php
/**
 * GID library for PHP
 *
 * @package    GIDSdk
 * @copyright  Copyright (c) 2012 Genetsis
 * @version    2.0
 * @see       http://developers.dru-id.com
 */
namespace Genetsis;

require_once(dirname(__FILE__) . "/Autoloader.php");

use Exception;
use Genetsis\core\ClientToken;
use Genetsis\core\Things;
use Genetsis\core\FileCache;
use Genetsis\core\InvalidGrantException;
use Genetsis\core\iTokenTypes;
use Genetsis\core\LogConfig;
use Genetsis\core\LoginStatusType;
use Genetsis\core\OAuth;
use Genetsis\core\OAuthConfig;

if (session_id() === '') {
    session_start();
}

/**
 * This is the main class of the DRUID library.
 *
 * It's the class that wraps the whole set of classes of the library and
 * that you'll have to use the most. With it, you'll be able to check if a
 * user is logged, log them out, obtain the personal data of any user,
 * and check if a user has enough data to take part in a promotion, upload
 * content or carry out an action that requires a specific set of personal
 * data.
 *
 * Sample usage:
 * <code>
 *    Identity::init();
 *    // ...
 * </code>
 *
 * @package  Genetsis
 * @version  2.0
 * @access   public
 */
class Identity
{
    /** @var Things Object to store Genetsis ID's session data. */
    private static $gid_things;
    /** @var \Logger Object for logging actions. */
    private static $logger;
    /** @var boolean Inidicates if Identity has been initialized */
    private static $initialized = false;
    /** @var boolean Inidicates if synchronizeSessionWithServer has been called */
    private static $synchronized = false;

    /**
     * When you initialize the library with this method, only the configuration defined in "oauthconf.xml" file of the gid_client is loaded
     * You must synchronize data with server if you need access to client or access token. This method is used for example, in ckactions.php actions.
     *
     * @param string $gid_client
     * @throws Exception
     */
    public static function initConfig($gid_client = 'default')
    {
        self::init($gid_client, false);
    }

    /**
     * When you initialize the library, the configuration defined in "oauthconf.xml" file of the gid_client is loaded
     * and by default this method auto-sync data (client_token, access_token,...) with server
     *
     * @param string $gid_client GID Client to load
     * @param boolean $sync Indicates if automatic data synchronization with the server is enabled
     * @param string $ini_path path of the configuration of the library (druid.ini file) if no argument passed, the default path and file will be /config/druid.ini
     * @throws Exception
     */
    public static function init($gid_client = 'default', $sync = true, $ini_path = null)
    {
        try {
            if (!self::$initialized) {
                if (!isset($ini_path)) $ini_path = dirname(__FILE__) . '/../config/druid.ini';
                Config::$ini_path = $ini_path;
                self::$initialized = true;
                AutoloaderClass::init();

                // Init config library
                Config::init($gid_client, $ini_path);

                // Initialize Logger, if we create 'gidlog' cookie, we enabled log.
                if ((Config::logLevel() === 'OFF') && (!isset($_COOKIE['gidlog']))) {
                    include_once dirname(__FILE__) . '/core/log4php/LoggerEmpty.php';
                } else {
                    if (!class_exists('Logger')) {
                        include_once dirname(__FILE__) . '/core/log4php/Logger.php';
                    }
                    \Logger::configure('main', new LogConfig(Config::logLevel(), Config::logPath()));
                }

                self::$logger = \Logger::getLogger("main");

                // Initialize Cache.
                FileCache::init(Config::cachePath(), Config::environment());
                // Initialize OAuth Config
                OAuthConfig::init();

                self::$gid_things = new Things();

                if ($sync) {
                    self::synchronizeSessionWithServer();
                }
            }
        } catch (Exception $e) {
            self::$logger->error($e->getMessage());
            throw $e;
        }
    }

    /**
     * This method verifies the authorization tokens (client_token,
     * access_token and refresh_token). Also updates the web client status,
     * storing the client_token, access_token and refresh tokend and
     * login_status in Things {@link Things}.
     *
     * Is INVOKE ON EACH REQUEST in order to check and update
     * the status of the user (not logged, logged or connected), and
     * verify that every token that you are gonna use before is going to be
     * valid.
     *
     * @return void
     */
    public static function synchronizeSessionWithServer()
    {
        if (!self::$synchronized) {
            self::$synchronized = true;

            try {
                self::$logger->debug('Synchronizing session with server');
                self::checkAndUpdateClientToken();

                self::loadUserTokenFromPersistence();

                if (self::$gid_things->getAccessToken() == null) {
                    self::$logger->debug('User is not logged, check SSO');
                    self::checkSSO();
                    if (self::$gid_things->getRefreshToken() != null) {
                        self::$logger->debug('User not logged but has Refresh Token');
                        self::checkAndRefreshAccessToken();
                    }
                } else {
                    if (self::isExpired(self::$gid_things->getAccessToken()->getExpiresAt())) {
                        self::$logger->debug('User logged but Access Token is expires');
                        self::checkAndRefreshAccessToken();
                    } else {
                        self::$logger->debug('User logged - check Validate Bearer');
                        self::checkLoginStatus();
                    }
                    if (!self::isConnected()) {
                        self::$logger->warn('User logged but is not connected (something wrong) - clear session data');
                        self::clearLocalSessionData();
                    }
                }
            } catch (Exception $e) {
                self::$logger->error($e->getMessage());
            }
            $_SESSION['Things'] = @serialize(self::$gid_things);
        }
    }

    /**
     * Checks and updates the "client_token" and cache if we have a valid one
     * If we don not have a Client Token in session, we check if we have a cookie
     * If we don not have a client Token in session or in a cookie, We request a new Client Token.
     * This method set the Client Token in Things
     *
     * @return void
     * @throws \Exception
     */
    private static function checkAndUpdateClientToken()
    {
        try {
            self::$logger->debug('Checking and update client_token.');
            if (!(($client_token = unserialize(FileCache::get('client_token'))) instanceof ClientToken) || ($client_token->getValue() == '')) {
                self::$logger->debug('Get Client token');

                if ((self::$gid_things->getClientToken() == null) || (OAuth::getStoredToken(iTokenTypes::CLIENT_TOKEN) == null)) {
                    self::$logger->debug('Not has clientToken in session or cookie');

                    if (!$client_token = OAuth::getStoredToken(iTokenTypes::CLIENT_TOKEN)) {
                        self::$logger->debug('Token Cookie does not exists. Requesting a new one.');
                        $client_token = OAuth::doGetClientToken(OauthConfig::getEndpointUrl('token_endpoint'));
                    }
                    self::$gid_things->setClientToken($client_token);
                } else {
                    self::$logger->debug('Client Token from session');
                }
                FileCache::set('client_token', serialize(self::$gid_things->getClientToken()), self::$gid_things->getClientToken()->getExpiresIn());
            } else {
                self::$logger->debug('Client Token from cache');
                self::$gid_things->setClientToken($client_token);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Checks if user is logged via SSO (datr cookie) - Single Sign On
     *
     * The method obtain the "access_token" of the logged user in
     * "*.cocacola.es" through the cookie, with Grant Type EXCHANGE_SESSION
     * To SSO on domains that are not under .cocacola.es the site must include this file
     * <script type="text/javascript" src="https://register.cocacola.es/login/sso"></script>
     *
     * @return void
     * @throws /Exception
     */
    private static function checkSSO()
    {
        try {
            if ((isset($_COOKIE['datr']) && ($_COOKIE['datr'] != ''))||(isset($_COOKIE['datr_']) && ($_COOKIE['datr_'] != ''))) {
                self::$logger->info('DATR cookie was found.');

                $response = OAuth::doExchangeSession(OauthConfig::getEndpointUrl('token_endpoint'), ($_COOKIE['datr'] != '') ? $_COOKIE['datr'] : $_COOKIE['datr_']);
                self::$gid_things->setAccessToken($response['access_token']);
                self::$gid_things->setRefreshToken($response['refresh_token']);
                self::$gid_things->setLoginStatus($response['login_status']);
            } else {
                self::$logger->debug('DATR cookie not exist, user is not logged');
            }
        } catch (InvalidGrantException $e) {
            unset($_COOKIE[OAuth::SSO_COOKIE_NAME]);
            setcookie(OAuth::SSO_COOKIE_NAME, null, -1, null, '.cocacola.es');

            self::$logger->warn('Invalid Grant, check an invalid DATR');
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Checks if a token has expired.
     *
     * @param integer $expiresAt The expiration date. In UNIX timestamp.
     * @return boolean TRUE if is expired or FALSE otherwise.
     */
    private static function isExpired($expiresAt)
    {
        if (!is_null($expiresAt)) {
            return (time() > $expiresAt);
        }
        return true;
    }

    /**
     * Checks and refresh the user's "access_token".
     *
     * @return void
     * @throws /Exception
     */
    private static function checkAndRefreshAccessToken()
    {
        try {
            self::$logger->debug('Checking and refreshing the AccessToken.');

            $response = OAuth::doRefreshToken(OauthConfig::getEndpointUrl('token_endpoint'));
            self::$gid_things->setAccessToken($response['access_token']);
            self::$gid_things->setRefreshToken($response['refresh_token']);
            self::$gid_things->setLoginStatus($response['login_status']);
        } catch (InvalidGrantException $e) {
            self::clearLocalSessionData();
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Deletes the local data of the user's session.
     *
     * @return void
     */
    private static function clearLocalSessionData()
    {
        self::$logger->debug('Clear Session Data');
        self::$gid_things->setAccessToken(null);
        self::$gid_things->setRefreshToken(null);
        self::$gid_things->setLoginStatus(null);

        OAuth::deleteStoredToken(iTokenTypes::ACCESS_TOKEN);
        OAuth::deleteStoredToken(iTokenTypes::REFRESH_TOKEN);

        if (isset($_SESSION)) {
            unset($_SESSION['Things']);
            foreach ($_SESSION as $key => $val) {
                if (preg_match('#^headerAuth#Ui', $key) || in_array($key, array('nickUserLogged', 'isConnected'))) {
                    unset($_SESSION[$key]);
                }
            }
        }
    }

    /**
     * Checks the user's status from Validate Bearer.
     * Update Things {@link Things} login status
     *
     * @return void
     * @throws /Exception
     */
    private static function checkLoginStatus()
    {
        try {
            self::$logger->debug('Checking login status');
            if (self::$gid_things->getLoginStatus()->getConnectState() == LoginStatusType::connected) {
                self::$logger->debug('User is connected, check access token');
                $loginStatus = OAuth::doValidateBearer(OauthConfig::getEndpointUrl('token_endpoint'));
                self::$gid_things->setLoginStatus($loginStatus);
            }
        } catch (InvalidGrantException $e) {
            self::$logger->warn('Invalid Grant, maybe access token is expires and sdk not checkit - call to refresh token');
            self::checkAndRefreshAccessToken();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Helper to check if the user is connected (logged on Genetsis ID)
     *
     * @return boolean TRUE if is logged, FALSE otherwise.
     */
    public static function isConnected()
    {
        if ((!is_null(self::getThings())) && (!is_null(self::getThings()->getAccessToken())) &&
            (!is_null(self::getThings()->getLoginStatus()) && (self::getThings()->getLoginStatus()->getConnectState() == LoginStatusType::connected))
        ) {
            return true;
        }
        return false;
    }

    /**
     * Helper to access library data
     *
     * @return \Genetsis\core\Things
     */
    public static function getThings()
    {
        return self::$gid_things;
    }

    /**
     * In that case, the url of "post-login" will retrieve an authorization
     * code as a GET parameter.
     *
     * Once the authorization code is provided to the web client, the SDK
     * will send it again to Genetsis ID at "token_endpoint" to obtain the
     * "access_token" of the user and create the cookie.
     *
     * This method is needed to authorize user when the web client takes
     * back the control of the browser.
     *
     * @param string $code Authorization code returned by Genetsis ID.
     * @return void
     * @throws /Exception
     */
    public static function authorizeUser($code)
    {
        try {
            self::$logger->debug('Authorize user');

            if ($code == '') {
                throw new Exception('Authorize Code is empty');
            }

            $response = OAuth::doGetAccessToken(OauthConfig::getEndpointUrl('token_endpoint'), $code, OauthConfig::getRedirectUrl('postLogin'));
            self::$gid_things->setAccessToken($response['access_token']);
            self::$gid_things->setRefreshToken($response['refresh_token']);
            self::$gid_things->setLoginStatus($response['login_status']);

            $_SESSION['Things'] = @serialize(self::$gid_things);

        } catch ( \Genetsis\core\InvalidGrantException $e) {
            self::$logger->error($e->getMessage());
        } catch (Exception $e) {
            self::$logger->error($e->getMessage());
        }
    }

    /**
     * Checks if the user have been completed all required fields for that
     * section.
     *
     * The "scope" (section) is a group of fields configured in Genetsis ID for
     * a web client.
     *
     * A section can be also defined as a "part" (section) of the website
     * (web client) that only can be accesed by a user who have filled a
     * set of personal information configured in Genetsis ID (all of the fields
     * required for that section).
     *
     * This method is commonly used for promotions or sweepstakes: if a
     * user wants to participate in a promotion, the web client must
     * ensure that the user have all the fields filled in order to let him
     * participate.
     *
     * @param $scope string Section-key identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @throws \Exception
     * @return boolean TRUE if the user have already completed all the
     *     fields needed for that section, false in otherwise
     */
    public static function checkUserComplete($scope)
    {
        $userCompleted = false;
        try {
            self::$logger->info('Checking if the user has filled its data out for this section:' . $scope);

            if (self::isConnected()) {
                $userCompleted = OAuth::doCheckUserCompleted(OAuthConfig::getApiUrl('user-api', 'base_url') . OauthConfig::getApiUrl('api.user', 'user'), $scope);
            }
        } catch (Exception $e) {
            self::$logger->error($e->getMessage());
        }
        return $userCompleted;
    }

    /**
     * Performs the logout process.
     *
     * It makes:
     * - The logout call to Genetsis ID
     * - Clear cookies
     * - Purge Tokens and local data for the logged user
     *
     * @return void
     * @throws Exception
     */
    public static function logoutUser()
    {
        try {
            if ((self::$gid_things->getAccessToken() != null) && (self::$gid_things->getRefreshToken() != null)) {
                self::$logger->info('User Single Sign Logout');
                UserApi::deleteCacheUser(self::$gid_things->getLoginStatus()->getCkUsid());

                OAuth::doLogout(OauthConfig::getEndpointUrl('logout_endpoint'));
                self::clearLocalSessionData();
            }
        } catch (Exception $e) {
            self::$logger->error($e->getMessage());
        }
    }

    /**
     * Returns a clientToken if user is not logged, and accessToken if user is logged
     *
     * @return mixed An instance of {@link AccessToken} or
     *     {@link ClientToken}
     * @throws \Exception if we have not a valid Token
     */
    private static function getTokenUser()
    {
        try {
            if (!is_null(self::$gid_things->getAccessToken())) {
                self::$logger->debug('Get AccessToken, user logged');
                return self::$gid_things->getAccessToken();
            } else {
                self::$logger->debug('Get ClientToken, user is NOT logged');
                return self::$gid_things->getClientToken();
            }
        } catch (Exception $e) {
            self::$logger->error($e->getMessage());
            throw new Exception('Not valid token');
        }
    }

    /**
     * Helper to access an static instance of Logger
     *
     * @return \Logger
     */
    public static function getLogger()
    {
        return self::$logger;
    }

    /**
     * Update the user's "access_token" from persistent data (SESSION or
     * COOKIE)
     *
     * @return void
     */
    private static function loadUserTokenFromPersistence ()
    {
        try {
            if (is_null(self::$gid_things->getAccessToken())){
                self::$logger->debug('Load access token from cookie');

                if (OAuth::hasToken(iTokenTypes::ACCESS_TOKEN)) {
                    self::$gid_things->setAccessToken(OAuth::getStoredToken(iTokenTypes::ACCESS_TOKEN));
                }
                if (OAuth::hasToken(iTokenTypes::REFRESH_TOKEN)) {
                    self::$gid_things->setRefreshToken(OAuth::getStoredToken(iTokenTypes::REFRESH_TOKEN));
                }
            }


        } catch (Exception $e) {
            self::$logger->error('['.__CLASS__.']['.__FUNCTION__.']['.__LINE__.']'.$e->getMessage());
        }
    }
}