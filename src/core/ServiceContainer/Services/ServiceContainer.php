<?php namespace Genetsis\core\ServiceContainer\Services;

use Genetsis\core\ServiceContainer\Contracts\ServiceContainerInterface;
use Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException;

/**
 * Service container implementation.
 *
 * @package   Genetsis
 * @category  Contract
 */
class ServiceContainer implements ServiceContainerInterface {

    /** @var array $factories List of registered service factories. */
    protected static $factories = [];
    /** @var array $instances List of instantiated services. */
    protected static $instances = [];

    /**
     * @inheritDoc
     */
    public function register($name, $closure)
    {
        if (!$name) {
            throw new \InvalidArgumentException('Service name cannot be empty.');
        }
        if (!is_callable($closure)) {
            throw new \InvalidArgumentException('Service declaration must be callable.');
        }

        if ($this->resolved($name)) {
            $this->remove($name);
        }

        static::$factories[$name] = $closure;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function registered($name)
    {
        return ($name && isset(static::$factories[$name]) && is_callable(static::$factories[$name]));
    }

    /**
     * @inheritDoc
     */
    public function resolved($name)
    {
        return ($name && isset(static::$instances[$name]));
    }

    /**
     * @inheritDoc
     */
    public function need($name)
    {
        if (!$name) {
            throw new \InvalidArgumentException('Service name cannot be empty.');
        }

        if ($this->resolved($name)) {
            return static::$instances[$name];
        } elseif ($this->registered($name)) {
            static::$instances[$name] = call_user_func(static::$factories[$name]);
            return static::$instances[$name];
        } else {
            throw new InvalidServiceException('Service required is not registered yet.');
        }
    }

    /**
     * @inheritDoc
     */
    public function remove($name)
    {
        if ($this->registered($name)) {
            unset(static::$factories[$name]);
        }
        if ($this->resolved($name)) {
            unset(static::$instances[$name]);
        }
    }

}