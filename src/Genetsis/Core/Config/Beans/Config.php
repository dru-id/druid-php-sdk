<?php
namespace Genetsis\Core\Config\Beans;

/**
 * This class stores all configuration parameters for the current process.
 *
 * @package  Genetsis
 * @category Bean
 */
class Config {

    /** @var string $server_name */
    private $server_name = '';

    /**
     * @param string $server_name
     */
    public function __construct($server_name)
    {
        $this->setServerName($server_name);
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
}