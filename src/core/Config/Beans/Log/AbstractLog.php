<?php
namespace Genetsis\core\Config\Beans\Log;

use Genetsis\core\Logger\Collections\LogLevels;

/**
 * This is the common class for all configuration log beans.
 *
 * @package  Genetsis
 * @category Bean
 */
abstract class AbstractLog {

    /** @var string $group Name to keep logs under the same group. */
    protected $group;
    /** @var string $level Log level. One of defined at {@link \Genetsis\core\Logger\Collections\LogLevels} */
    protected $level;

    /**
     * @param string $group Name to keep logs under the same group.
     * @param string $log_level One of defined at {@link \Genetsis\core\Logger\Collections\LogLevels}
     */
    public function __construct($group, $log_level)
    {
        $this->setGroup($group);
        $this->setLevel($log_level);
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
     * @return AbstractLog
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level One of defined at {@link \Genetsis\core\Logger\Collections\LogLevels}
     * @return AbstractLog
     * @throws \InvalidArgumentException If the log levels is not allowed.
     */
    public function setLevel($level)
    {
        if (!LogLevels::check($level)) {
            throw new \InvalidArgumentException('Invalid log level.');
        }
        $this->level = $level;
        return $this;
    }

}