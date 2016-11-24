<?php namespace Genetsis\core\ServiceContainer\Services;

use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\Http\Services\Http as HttpService;
use Genetsis\core\Logger\Services\EmptyLogger;
use Genetsis\core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\core\ServiceContainer\Contracts\ServiceContainerInterface;
use Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException;

/**
 * DruID service container.
 *
 * @package   Genetsis
 * @category  Contract
 */
class ServiceContainer implements ServiceContainerInterface {

    /** @var LoggerServiceInterface $logger */
    protected static $logger = null;

    /** @var HttpServiceInterface $http_service */
    protected static $http_service = null;

    /** @var OAuthServiceInterface  */
    protected static $oauth = null;

    /**
     * @inheritDoc
     */
    public static function init(array $services = [])
    {
        foreach ($services as $service) {
            if ($service instanceof LoggerServiceInterface) {
                static::setLogger($service);
            } elseif ($service instanceof HttpServiceInterface) {
                static::setHttpService($service);
            } elseif ($service instanceof OAuthServiceInterface) {
                static::setOAuthService($service);
            } else {
                throw new InvalidServiceException('Service "'.(is_object($service) ? get_class($service) : $service).'" is not a valid service.');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function reset()
    {
        static::$logger = null;
        static::$http_service = null;
        static::$oauth = null;
    }

    /**
     * @inheritDoc
     */
    public static function getLogger()
    {
        // Logger is the only service which should be return a default logger if not defined.
        if (!isset(static::$logger) || !(static::$logger instanceof LoggerServiceInterface)) {
            return static::$logger = new EmptyLogger();
        }
        return static::$logger;
    }

    /**
     * @inheritDoc
     */
    public static function setLogger($service)
    {
        if (is_null($service) || ($service instanceof LoggerServiceInterface)) {
            static::$logger = $service;
        } else {
            throw new InvalidServiceException('Invalid service.');
        }
    }

    /**
     * @inheritDoc
     */
    public static function getHttpService()
    {
        if (!isset(static::$http_service) || !(static::$http_service instanceof HttpServiceInterface)) {
            throw new InvalidServiceException('Http service not defined');
        }
        return static::$http_service;
    }

    /**
     * @inheritDoc
     */
    public static function setHttpService($service)
    {
        if (is_null($service) || ($service instanceof HttpServiceInterface)) {
            static::$http_service = $service;
        } else {
            throw new InvalidServiceException('Invalid service.');
        }
    }

    /**
     * @inheritDoc
     */
    public static function getOAuthService()
    {
        if (!isset(static::$oauth) || !(static::$oauth instanceof OAuthServiceInterface)) {
            throw new InvalidServiceException('OAuth service not defined');
        }
        return static::$oauth;
    }

    /**
     * @inheritDoc
     */
    public static function setOAuthService($service)
    {
        if (is_null($service) || ($service instanceof OAuthServiceInterface)) {
            static::$oauth = $service;
        } else {
            throw new InvalidServiceException('Invalid service.');
        }
    }

}