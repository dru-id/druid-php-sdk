<?php
namespace Genetsis\core\Cache\Services;
use Genetsis\core\Cache\Contracts\CacheServiceInterface;

/**
 * Empty cache service implementation.
 *
 * Use this class if you want to disable all caching capabilities without messing all up with conditional controls.
 * @package  Genetsis
 * @category Service
 */
class EmptyCache implements CacheServiceInterface {

    /**
     * @inheritDoc
     */
    public function set($key, $data = false, $ttl = 3600)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        return $default;
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function clean()
    {
        return true;
    }

}
