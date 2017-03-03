<?php
namespace Genetsis\DruID\Core\Config\Beans\Cache;

/**
 * This class keeps config parameters when cache should be stored in memcached.
 *
 * @author Genetsis
 * @link http://developers.dru-id.com
 */
class Memcached extends AbstractCache {

    /** @var string $host */
    private $host;
    /** @var string $port */
    private $port;

    /**
     * @param string $group Name to keep logs under the same group.
     * @param string $host
     * @param string $port
     */
    public function __construct($group, $host, $port)
    {
        parent::__construct($group);
        $this->setHost($host);
        $this->setPort($port);
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return Memcached
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     * @return Memcached
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

}