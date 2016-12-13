<?php
namespace Genetsis\core\Config\Beans;

class Log {

    private $level;
    private $log_folder;

    public function __construct($log_folder, $log_level)
    {
        $this->setLogFolder($log_folder);
        $this->setLevel($log_level);
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     * @return Log
     */
    public function setLevel($level)
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogFolder()
    {
        return $this->log_folder;
    }

    /**
     * @param mixed $log_folder
     * @return Log
     */
    public function setLogFolder($log_folder)
    {
        $this->log_folder = $log_folder;
        return $this;
    }

}