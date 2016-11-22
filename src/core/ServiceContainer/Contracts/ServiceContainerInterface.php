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
     * @return LoggerServiceInterface
     * @throws InvalidServiceException
     */
    public static function getLogger ();

    /**
     * @param LoggerServiceInterface $service
     * @return void
     */
    public static function setLogger (LoggerServiceInterface $service);

    /**
     * @return HttpServiceInterface
     * @throws InvalidServiceException
     */
    public static function getHttpService();

    /**
     * @param HttpServiceInterface $service
     * @return void
     */
    public static function setHttpService(HttpServiceInterface $service);

    /**
     * @return OAuthServiceInterface
     * @throws InvalidServiceException
     */
    public static function getOAuthService();

    /**
     * @param OAuthServiceInterface $service
     * @return void
     */
    public static function setOAuthService(OAuthServiceInterface $service);

}