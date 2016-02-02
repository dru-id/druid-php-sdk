<?php

namespace Genetsis\core\user;

/**
 * This class stores the User Brand register origin
 *
 * @package   Genetsis
 * @category  Bean
 * @version   2.0
 */
class Brand
{
    /** @var string key */
    private $key = null;
    /** @var string value id */
    private $name = null;

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

} 