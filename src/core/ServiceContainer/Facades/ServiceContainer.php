<?php namespace Genetsis\core\ServiceContainer\Facades;

use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\core\ServiceContainer\Contracts\ServiceContainerFacadeInterface;
use Genetsis\core\ServiceContainer\Contracts\ServiceContainerInterface;
use Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException;
use Psr\Log\LoggerInterface;

/**
 * DruID service container.
 *
 * @package   Genetsis
 * @category  Contract
 */
class ServiceContainer implements ServiceContainerFacadeInterface {

    /** @var ServiceContainerInterface $sc */
    protected $sc;

    /**
     * @param ServiceContainerInterface $sc
     */
    public function __construct(ServiceContainerInterface $sc)
    {
        $this->sc = $sc;
    }

    /**
     * @inheritDoc
     */
    public function getLogger()
    {
        try {
            $service = $this->sc->need('logger');
            if (!($service instanceof LoggerInterface)) {
                throw new InvalidServiceException('Logger service not defined.');
            }
            return $service;
        } catch (InvalidServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new InvalidServiceException('Logger service not defined.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function setLogger($closure)
    {
        try {
            $this->sc->register('logger', $closure);
            return true;
        } catch (\InvalidArgumentException $e) {
            throw new InvalidServiceException('Invalid service configuration.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getHttpService()
    {
        try {
            $service = $this->sc->need('http');
            if (!($service instanceof HttpServiceInterface)) {
                throw new InvalidServiceException('Http service not defined.');
            }
            return $service;
        } catch (InvalidServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new InvalidServiceException('Http service not defined.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function setHttpService($closure)
    {
        try {
            $this->sc->register('http', $closure);
            return true;
        } catch (\InvalidArgumentException $e) {
            throw new InvalidServiceException('Invalid service configuration.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getOAuthService()
    {
        try {
            $service = $this->sc->need('oauth');
            if (!($service instanceof OAuthServiceInterface)) {
                throw new InvalidServiceException('OAuth service not defined.');
            }
            return $service;
        } catch (InvalidServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new InvalidServiceException('OAuth service not defined.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function setOAuthService($closure)
    {
        try {
            $this->sc->register('oauth', $closure);
            return true;
        } catch (\InvalidArgumentException $e) {
            throw new InvalidServiceException('Invalid service configuration.', 0, $e);
        }
    }

}