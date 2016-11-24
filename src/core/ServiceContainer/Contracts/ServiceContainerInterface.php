<?php namespace Genetsis\core\ServiceContainer\Contracts;

use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException;

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
     *      - HttpServiceInterface
     *      - OAuthServiceInterface
     * @return void
     * @throws InvalidServiceException
     */
    public static function init (array $services = array());

    /**
     * Removes all services.
     *
     * @return void
     */
    public static function reset();

    /**
     * @return LoggerServiceInterface
     */
    public static function getLogger ();

    /**
     * @param LoggerServiceInterface|null $service Set to NULL to remove service.
     * @return void
     * @throws InvalidServiceException
     */
    public static function setLogger ($service);

    /**
     * @return HttpServiceInterface
     * @throws InvalidServiceException
     */
    public static function getHttpService();

    /**
     * @param HttpServiceInterface|null $service Set to NULL to remove service.
     * @return void
     * @throws InvalidServiceException
     */
    public static function setHttpService($service);

    /**
     * @return OAuthServiceInterface
     * @throws InvalidServiceException
     */
    public static function getOAuthService();

    /**
     * @param OAuthServiceInterface|null $service Set to NULL to remove service.
     * @return void
     * @throws InvalidServiceException
     */
    public static function setOAuthService($service);

}