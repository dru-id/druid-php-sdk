<?php namespace Genetsis\core\ServiceContainer\Services;

use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\Http\Services\Http as HttpService;
use Genetsis\core\Logger\Services\EmptyLogger;
use Genetsis\core\ServiceContainer\Contracts\ServiceContainerInterface;

/**
 * DruID service container.
 *
 * @package   Genetsis
 * @category  Contract
 */
class ServiceContainer implements ServiceContainerInterface {

    /** @var LoggerServiceInterface $logger */
    private static $logger = null;

    /** @var HttpServiceInterface $http_service */
    private static $http_service = null;

    /**
     * @inheritDoc
     */
    public static function init(array $services = [])
    {
        foreach ($services as $service) {
            if ($service instanceof LoggerServiceInterface) {
                static::setLogger($service);
            }
            if ($service instanceof HttpServiceInterface) {
                static::setHttpService($service);
            }
        }

        // Default logger service.
        if (!isset(static::$logger)) {
            static::$logger = new EmptyLogger();
        }
        if (!isset(static::$http_service)) {
            static::$http_service = new HttpService();
        }
    }

    /**
     * @inheritDoc
     */
    public static function getLogger()
    {
        static::init();
        return static::$logger;
    }

    /**
     * @inheritDoc
     */
    public static function setLogger(LoggerServiceInterface $service)
    {
        static::$logger = $service;
    }

    /**
     * @inheritDoc
     */
    public static function getHttpService()
    {
        return static::$http_service;
    }

    /**
     * @inheritDoc
     */
    public static function setHttpService(HttpServiceInterface $service)
    {
        static::$http_service = $service;
    }

}