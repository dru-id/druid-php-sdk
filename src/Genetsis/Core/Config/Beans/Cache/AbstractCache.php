<?php
namespace Genetsis\Core\Config\Beans\Cache;

/**
 * This is the common class for all configuration cache beans.
 *
 * @package  Genetsis
 * @category Bean
 */
abstract class AbstractCache {

    /** @var string $group Name to keep logs under the same group. */
    protected $group;

    /**
     * @param string $group Name to keep logs under the same group.
     */
    public function __construct($group)
    {
        $this->setGroup($group);
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     * @return AbstractCache
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

}