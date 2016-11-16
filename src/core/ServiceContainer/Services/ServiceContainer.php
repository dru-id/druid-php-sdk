<?php namespace Genetsis\core\ServiceContainer\Services;

use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
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

    /**
     * @inheritDoc
     */
    public static function init(array $services = [])
    {
        foreach ($services as $service) {
            if ($service instanceof LoggerServiceInterface) {
                static::setLogger($service);
            }
        }

        // Default logger service.
        if (!isset(static::$logger)) {
            static::$logger = new EmptyLogger();
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

}