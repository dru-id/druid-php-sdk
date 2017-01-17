<?php
namespace Genetsis;

use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\VoidCache;
use Genetsis\core\Config\Beans\Cache\File as FileCacheConfig;
use Genetsis\core\Config\Beans\Cache\Memcached as MemcachedCacheConfig;
use Genetsis\core\Config\Beans\Config as DruIDConfig;
use Genetsis\core\Config\Beans\Log\File as FileLogConfig;
use Genetsis\core\Config\Beans\Log\Syslog as SyslogConfig;
use Genetsis\core\Http\Contracts\CookiesServiceInterface;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\Http\Contracts\SessionServiceInterface;
use Genetsis\core\Http\Services\Cookies;
use Genetsis\core\Http\Services\Http;
use Genetsis\core\Http\Services\Session;
use Genetsis\core\Logger\Collections\LogLevels as LogLevelsCollection;
use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
use Genetsis\core\Logger\Services\DruIDLogger;
use Genetsis\core\Logger\Services\EmptyLogger;
use Genetsis\core\Logger\Services\SyslogLogger;
use Genetsis\core\Logger\Services\VoidLogger;
use Genetsis\core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\core\OAuth\Services\OAuth;
use Genetsis\core\OAuth\Services\OAuthConfigFactory;
use Genetsis\Identity\Contracts\IdentityServiceInterface;
use Genetsis\Identity\Services\Identity;
use Genetsis\Opi\Services\Opi;
use Genetsis\Opinator\Contracts\OpiServiceInterface;
use Genetsis\UrlBuilder\Contracts\UrlBuilderServiceInterface;
use Genetsis\UrlBuilder\Services\UrlBuilder;
use Genetsis\UserApi\Contracts\UserApiServiceInterface;
use Genetsis\UserApi\Services\UserApi;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

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

    /** @var OAuthServiceInterface $oauth */
    private static $oauth;
    /** @var HttpServiceInterface $http */
    private static $http;
    /** @var SessionServiceInterface $session */
    private static $session;
    /** @var CookiesServiceInterface $cookie */
    private static $cookie;
    /** @var LoggerInterface $logger */
    private static $logger;
    /** @var DoctrineCacheInterface $cache {@see http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/reference/caching.html} */
    private static $cache;

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
     * @param string $oauth_config_xml OAuth configuration XML content.
     * @param LoggerInterface|null $logger
     * @return void
     * @throws \Exception
     */
    public static function setup(DruIDConfig $druid_config, $oauth_config_xml, $logger = null)
    {
        // Logger service.
        if (!($logger instanceof LoggerInterface)) {
            $logger = new VoidLogger();
        }
        self::$logger = $logger;

//        if ($druid_config->getLog() instanceof FileLogConfig) {
//            self::$logger = new DruIDLogger($druid_config->getLog()->getFolder().'/'.$druid_config->getLog()->getGroup(), LogLevelsCollection::DEBUG);
//        } elseif ($druid_config->getLog() instanceof SyslogConfig) {
//            self::$logger = new SyslogLogger($druid_config->getLog()->getLevel());
//        } else {
//            self::$logger = new EmptyLogger();
//        }

        // Cache service
        if ($druid_config->getCache() instanceof FileCacheConfig) {
            self::$cache = new FilesystemCache($druid_config->getCache()->getFolder().'/'.$druid_config->getCache()->getGroup());
        } elseif ($druid_config->getCache() instanceof MemcachedCacheConfig) {
            if (class_exists('\Memcached')) {
                $memcached = new \Memcached();
                $memcached->addServer($druid_config->getCache()->getHost(), $druid_config->getCache()->getPort());
                self::$cache = new MemcachedCache();
                self::$cache->setMemcached($memcached);
            } else {
                self::$logger->warning('Memcached cache required but Memcached is not available in this server. A void cache will be used instead.', __METHOD__, __LINE__);
            }
        }
        // If cache service is not provided then we will use a VoidCache in order to avoid messing things up with
        // conditional controls around the code.
        if (!isset(self::$cache) || !(self::$cache instanceof DoctrineCacheInterface)) {
            self::$cache = new VoidCache();
        }

        // Http service.
        // We associate the logger received with the Guzzle client to register the requests.
        $stack = HandlerStack::create();
        $stack->push(Middleware::log(self::$logger, new MessageFormatter(), LogLevel::INFO));
        $stack->push(Middleware::log(self::$logger, new MessageFormatter("\nREQUEST:\n{request}\n\nRESPONSE:\n{response}\n"), LogLevel::DEBUG));
        // Do not verify SSL for self-signed certifies. Only for development.
        self::$http = new Http(new Client(['handler' => $stack, 'http_errors' => false, 'verify' => false]), self::$logger);

        // Cookie service.
        self::$cookie = new Cookies();

        // Session service.
        self::$session = new Session();

        // OAuth service
        $oauth_config = (new OAuthConfigFactory(self::$logger, self::$cache))->buildConfigFromXml($oauth_config_xml);
        if ($oauth_config->getVersion() != self::CONF_VERSION) {
            self::$logger->error('Invalid XML version: ' . $oauth_config->getVersion() . ' (expected ' . self::CONF_VERSION . ')', __METHOD__, __LINE__);
            throw new \Exception('Invalid version. You are trying load a configuration file for another version of the service.');
        }
        self::$oauth = new OAuth($oauth_config, self::$http, self::$cookie, self::$logger);

        self::$identity = new Identity(self::$oauth, self::$session, self::$cookie, self::$logger, self::$cache);
        self::$url_builder = new UrlBuilder(self::$oauth, self::$logger);
        self::$user_api = new UserApi(self::$oauth, self::$http, self::$logger, self::$cache);
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
        self::$identity->synchronizeSessionWithServer();
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
     * Returns an instance of identity service.
     *
     * @return IdentityServiceInterface
     * @throws \Exception
     */
    public static function identity()
    {
        self::checkSetup();
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
        return self::$opi;
    }

}