<?php
namespace Genetsis;

use Genetsis\core\Config\Beans\Cache;
use Genetsis\core\Config\Beans\Config as DruIDConfig;
use Genetsis\core\Config\Beans\Log;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\Http\Services\Http;
use Genetsis\core\Logger\Collections\LogLevels as LogLevelsCollection;
use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
use Genetsis\core\Logger\Services\DruIDLogger;
use Genetsis\core\Logger\Services\EmptyLogger;
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

    const CONF_VERSION = '1.4';

    /** @var OAuthServiceInterface $oauth */
    private static $oauth;
    /** @var HttpServiceInterface $http */
    private static $http;
    /** @var LoggerServiceInterface $logger */
    private static $logger;

    /** @var DruIDConfig $druid_config */
    private static $druid_config;
    /** @var IdentityServiceInterface $identity */
    private static $identity;
    /** @var UrlBuilderServiceInterface $url_builder */
    private static $url_builder;
    /** @var UserApiServiceInterface $user_api */
    private static $user_api;
    /** @var OpiServiceInterface $opi */
    private static $opi;

    /**
     * @param DruIDConfig $druid_config
     * @param OAuthConfig $oauth_config
     * @return void
     */
    public static function init(DruIDConfig $druid_config, OAuthConfig $oauth_config)
    {
        self::$druid_config = $druid_config;

        // Logger service.
        if (isset($settings['logger']) && ($settings['logger'] instanceof LoggerServiceInterface)) { // Custom logger.
            self::$logger = $settings['logger'];
        } else { // Default logger based on configuration.
            if (isset($settings['log-level']) && ($settings['log-level'] != 'off')) {
                self::$logger = new DruIDLogger(self::$druid_config->getLog()->getLogFolder().'/'.$oauth_config->getClientId(), LogLevelsCollection::DEBUG); // TODO: check if log leves if defined by user through configuration.
            } else {
                self::$logger = new EmptyLogger();
            }
        }

        // Http service.
        self::$http = new Http(self::$logger);

        // OAuth service
        // TODO: check if there is a cache for the OAuth.
        self::$oauth = new OAuth($oauth_config, self::$http, self::$logger);

        self::$identity = new Identity(self::$oauth, self::$logger);
        self::$url_builder = new UrlBuilder(self::$oauth, self::$logger);
        self::$user_api = new UserApi(self::$oauth, self::$http, self::$logger);
        self::$opi = new Opi(self::$oauth);
    }

    /**
     * @return IdentityServiceInterface
     * @throws \Exception
     */
    public static function identity()
    {
        if (!isset(self::$identity) || !(self::$identity instanceof IdentityServiceInterface)) {
            throw new \Exception('Identity service not defined.');
        }
        return self::$identity;
    }

    /**
     * @return UrlBuilderServiceInterface
     * @throws \Exception
     */
    public static function urlBuilder()
    {
        if (!isset(self::$url_builder) || !(self::$url_builder instanceof UrlBuilderServiceInterface)) {
            throw new \Exception('UrlBuilder service not defined.');
        }
        return self::$url_builder;
    }

    /**
     * @return UserApiServiceInterface
     * @throws \Exception
     */
    public static function userApi()
    {
        if (!isset(self::$user_api) || !(self::$user_api instanceof UserApiServiceInterface)) {
            throw new \Exception('UserApi service not defined.');
        }
        return self::$user_api;
    }

    /**
     * @return OpiServiceInterface
     * @throws \Exception
     */
    public static function opi()
    {
        if (!isset(self::$opi) || !(self::$opi instanceof OpiServiceInterface)) {
            throw new \Exception('Opi service not defined.');
        }
        return self::$opi;
    }

}