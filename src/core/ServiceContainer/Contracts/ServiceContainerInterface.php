<?php namespace Genetsis\core\ServiceContainer\Contracts;

use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
use Genetsis\core\Http\Contracts\HttpServiceInterface;

/**
 * Service container interface.
 *
 * A service container is a kind of container that stores service instances to be used across all the library, so you
 * can use these services without instantiating them manually or worrying how to create them.
 *
 * @package   Genetsis
 * @category  Contract
 */
interface ServiceContainerInterface {

    /**
     * @param array $services List of services. Accepts any of these:
     *      - LoggerServiceInterface
     */
    public static function init (array $services = array());

    /**
     * @return LoggerServiceInterface
     */
    public static function getLogger ();

    /**
     * @param LoggerServiceInterface $service
     * @return void
     */
    public static function setLogger (LoggerServiceInterface $service);

    /**
     * @return HttpServiceInterface
     */
    public static function getHttpService();

    /**
     * @param HttpServiceInterface $service
     * @return void
     */
    public static function setHttpService(HttpServiceInterface $service);

}