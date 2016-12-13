<?php
namespace Genetsis\core\ServiceContainer\Contracts;

use Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException;

/**
 * Service container interface.
 *
 * @package   Genetsis
 * @category  Contract
 */
interface ServiceContainerInterface
{

    /**
     * Register a new service into container.
     *
     * @param string $name Service name. If exists this service will be overwritten.
     * @param callable $closure Function which returns a configured service. It won't be called until the service is
     *      used for the first time.
     * @return ServiceContainerInterface
     * @throws \InvalidArgumentException
     */
    public function register($name, $closure);

    /**
     * Checks if a service has been registered.
     *
     * @param string $name Service name.
     * @return boolean
     */
    public function registered($name);

    /**
     * Checks if a service has been resolved.
     *
     * @param string $name
     * @return boolean
     */
    public function resolved($name);

    /**
     * Returns the service instance.
     *
     * @param string $name Service name.
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws InvalidServiceException
     * @throws \Exception Maybe an exception will be thrown when resolving service instantiation.
     */
    public function need($name);

    /**
     * Removes an specific service.
     *
     * @param string $name Service name.
     * @return void
     */
    public function remove($name);

}