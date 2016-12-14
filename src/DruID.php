<?php
namespace Genetsis;

use Genetsis\core\Config\Beans\Cache;
use Genetsis\core\Config\Beans\Config as DruIDConfig;
use Genetsis\core\Config\Beans\Log\File as FileLogConfig;
use Genetsis\core\Config\Beans\Log\Syslog as SyslogConfig;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\Http\Services\Http;
use Genetsis\core\Logger\Collections\LogLevels as LogLevelsCollection;
use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
use Genetsis\core\Logger\Services\DruIDLogger;
use Genetsis\core\Logger\Services\EmptyLogger;
use Genetsis\core\Logger\Services\SyslogLogger;
use Genetsis\core\OAuth\Beans\OAuthConfig\Config as OAuthConfig;
use Genetsis\core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\core\OAuth\Services\OAuth;
use Genetsis\Identity\Contracts\IdentityServiceInterface;
use Genetsis\Identity\Services\Identity;
use Genetsis\Opi\Services\Opi;
use Genetsis\Opinator\Contracts\OpiServiceInterface;
use Genetsis\UrlBuilder\Contracts\UrlBuilderServiceInterface;
use Genetsis\UrlBuilder\Services\UrlBuilder;
use Genetsis\UserApi\Contracts\UserApiServiceInterface;
use Genetsis\UserApi\Services\UserApi;

/**
 * This is the main class for DruID library. All starts here.
 *
 * @package Genetsis
 */
class DruID {

    // Indicates which is the OAuth configuration file version is accepted by this library.
    const CONF_VERSION = '1.4';

    /** @var boolean $setup_done Indicates if the setup process has been done or not. */
    private static $setup_done = false;
    /** @var boolean $sync_done Indicates if the synchronization process has been done or not. */
    private static $sync_done = false;

    /** @var OAuthServiceInterface $oauth */
    private static $oauth;
    /** @var HttpServiceInterface $http */
    private static $http;
    /** @var LoggerServiceInterface $logger */
    private static $logger;

    /** @var IdentityServiceInterface $identity */
    private static $identity;
    /** @var UrlBuilderServiceInterface $url_builder */
    private static $url_builder;
    /** @var UserApiServiceInterface $user_api */
    private static $user_api;
    /** @var OpiServiceInterface $opi */
    private static $opi;

    /**
     * Configures the application defining parameters such as registry system or cache, as well as which configuration
     * file to use to use DruID web services.
     *
     * This method does not make an initial call to DruID web services to synchronize the library, for this you must
     * call {@link DruID::init()}.
     *
     * @param DruIDConfig $druid_config
     * @param OAuthConfig $oauth_config
     * @return void
     */
    public static function setup(DruIDConfig $druid_config, OAuthConfig $oauth_config)
    {
        // Log service.
        if ($druid_config->getLog() instanceof FileLogConfig) {
            self::$logger = new DruIDLogger($druid_config->getLog()->getFolder().'/'.$oauth_config->getClientId(), LogLevelsCollection::DEBUG);
        } elseif ($druid_config->getLog() instanceof SyslogConfig) {
            self::$logger = new SyslogLogger($druid_config->getLog()->getLevel());
        } else {
            self::$logger = new EmptyLogger();
        }

        // Cache service
        // TODO: implement cache service.

        // Http service.
        self::$http = new Http(self::$logger);

        // OAuth service
        // TODO: check if there is a cache for the OAuth.
        self::$oauth = new OAuth($oauth_config, self::$http, self::$logger);

        self::$identity = new Identity(self::$oauth, self::$logger);
        self::$url_builder = new UrlBuilder(self::$oauth, self::$logger);
        self::$user_api = new UserApi(self::$oauth, self::$http, self::$logger);
        self::$opi = new Opi(self::$oauth);

        self::$setup_done = true;
    }

    /**
     * Start the synchronization with DruID services.
     *
     * This action will be done once, so if you make multiple calls of this method those will be ignored.
     *
     * @return void
     * @throws \Exception
     */
    public static function init()
    {
        self::checkSetup();
        if (!self::$sync_done) {
            self::$identity->synchronizeSessionWithServer();
            self::$sync_done = true;
        }
    }

    /**
     * @throws \Exception If the library is not setup.
     */
    private static function checkSetup()
    {
        if (!self::$setup_done) {
            throw new \Exception('DruID library is not setup.');
        }
    }

    /**
     * @throws \Exception If the library is not synced with DruID services.
     */
    private static function checkSync()
    {
        if (!self::$sync_done) {
            throw new \Exception('DruID library is not synced.');
        }
    }

    /**
     * Returns an instance of identity service.
     *
     * @return IdentityServiceInterface
     * @throws \Exception
     */
    public static function identity()
    {
        self::checkSetup();
        self::checkSync();
        return self::$identity;
    }

    /**
     * Returns an instance of URL builder service.
     *
     * @return UrlBuilderServiceInterface
     * @throws \Exception
     */
    public static function urlBuilder()
    {
        self::checkSetup();
        self::checkSync();
        return self::$url_builder;
    }

    /**
     * Returns an instance of user API service.
     *
     * @return UserApiServiceInterface
     * @throws \Exception
     */
    public static function userApi()
    {
        self::checkSetup();
        self::checkSync();
        return self::$user_api;
    }

    /**
     * Returns an instance of OPI service.
     *
     * @return OpiServiceInterface
     * @throws \Exception
     */
    public static function opi()
    {
        self::checkSetup();
        self::checkSync();
        return self::$opi;
    }

}