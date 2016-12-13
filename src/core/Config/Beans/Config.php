<?php
namespace Genetsis\core\Config\Beans;

use Genetsis\core\Config\Beans\Cache\File;
use Genetsis\core\Config\Beans\Cache\Memcached;

/**
 * @package  Genetsis
 * @category Bean
 */
class Config {

    /** @var string $server_name */
    private $server_name = '';
    /** @var Log|null $log */
    private $log;
    /** @var File|Memcached|null $cache */
    private $cache;

    /**
     * @param string $server_name
     * @param Log|null $log
     * @param File|Memcached|null $cache
     */
    public function __construct($server_name, $log = null, $cache = null)
    {
        $this->setServerName($server_name);
        $this->setLog($log);
        $this->setCache($cache);
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        return $this->server_name;
    }

    /**
     * @param string $server_name
     * @return Config
     */
    public function setServerName($server_name)
    {
        $this->server_name = $server_name;
        return $this;
    }

    /**
     * @return Log|null
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param Log|null $log
     * @return Config
     * @throws \InvalidArgumentException
     */
    public function setLog($log)
    {
        if (is_null($log) || ($log instanceof Log)) {
            $this->log = $log;
            return $this;
        } else {
            throw new \InvalidArgumentException('Invalid parameter.');
        }
    }

    /**
     * @return File|Memcached|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param File|Memcached|null $cache
     * @return Config
     * @throws \InvalidArgumentException
     */
    public function setCache($cache)
    {
        if (is_null($cache) || ($cache instanceof File) || ($cache instanceof Memcached)) {
            $this->cache = $cache;
            return $this;
        } else {
            throw new \InvalidArgumentException('Invalid parameter.');
        }
    }
}