<?php namespace Genetsis\core\ServiceContainer\Contracts;

use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException;
use Psr\Log\LoggerInterface;

/**
 * Service container facade interface.
 *
 * A service container is a kind of container that stores service instances to be used across all the library, so you
 * can use these services without instantiating them manually or worrying how to create them.
 *
 * @package   Genetsis
 * @category  Contract
 */
interface ServiceContainerFacadeInterface {

    /**
     * Returns the current logger service.
     *
     * @return LoggerInterface
     * @throws InvalidServiceException
     */
    public function getLogger();

    /**
     * Register the current logger service used by this application.
     *
     * @param callable $closure Function which returns an {@link LoggerServiceInterface} instance. It won't be called
     *      until the service is used for the first time.
     * @return boolean
     * @throws InvalidServiceException
     */
    public function setLogger($closure);

    /**
     * @return HttpServiceInterface
     * @throws InvalidServiceException
     */
    public function getHttpService();

    /**
     * @param callable $closure Function which returns an {@link HttpServiceInterface} instance. It won't be called
     *      until the service is used for the first time.
     * @return void
     * @throws InvalidServiceException
     */
    public function setHttpService($closure);

    /**
     * @return OAuthServiceInterface
     * @throws InvalidServiceException
     */
    public function getOAuthService();

    /**
     * @param callable $closure Function which returns an {@link OAuthServiceInterface} instance. It won't be called
     *      until the service is used for the first time.
     * @return void
     * @throws InvalidServiceException
     */
    public function setOAuthService($closure);

}