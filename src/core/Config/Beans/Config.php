<?php
namespace Genetsis\core\Config\Beans;

use Genetsis\core\Config\Beans\Cache\AbstractCache;
use Genetsis\core\Config\Beans\Log\AbstractLog;

/**
 * This class stores all configuration parameters for the current process.
 *
 * @package  Genetsis
 * @category Bean
 */
class Config {

    /** @var string $server_name */
    private $server_name = '';
    /** @var AbstractLog|null $log Configuration parameters for logging process. If it is not defined none of all
     * log calls will be processed. */
    private $log;
    /** @var AbstractCache|null $cache Configuration parameters for caching process. If it is not defined none
     * of all caching process will be processed.*/
    private $cache;

    /**
     * @param string $server_name
     * @param AbstractLog|null $log
     * @param AbstractCache|null $cache
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
     * @return AbstractLog|null
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param AbstractLog|null $log
     * @return Config
     * @throws \InvalidArgumentException
     */
    public function setLog($log)
    {
        if (is_null($log) || ($log instanceof AbstractLog)) {
            $this->log = $log;
            return $this;
        } else {
            throw new \InvalidArgumentException('Invalid argument value.');
        }
    }

    /**
     * @return AbstractCache|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param AbstractCache|null $cache
     * @return Config
     * @throws \InvalidArgumentException
     */
    public function setCache($cache)
    {
        if (is_null($cache) || ($cache instanceof AbstractCache)) {
            $this->cache = $cache;
            return $this;
        } else {
            throw new \InvalidArgumentException('Invalid argument value.');
        }
    }
}