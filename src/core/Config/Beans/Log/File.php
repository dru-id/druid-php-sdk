<?php
namespace Genetsis\core\Config\Beans\Log;

use Genetsis\core\Logger\Collections\LogLevels;

/**
 * This class keep config parameters when log events should be stored in files.
 *
 * @package  Genetsis
 * @category Bean
 */
class File extends AbstractLog {

    /** @var string $folder Full path to the folder where all logs will be stored. */
    private $folder;

    /**
     * @param string $folder Full path to the folder where all logs will be stored.
     * @param string $log_level One of defined at {@link \Genetsis\core\Logger\Collections\LogLevels}
     */
    public function __construct($folder, $log_level = LogLevels::DEBUG)
    {
        parent::__construct($log_level);
        $this->setFolder($folder);
    }

    /**
     * @return mixed
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param mixed $folder Path to the folder where all logs will be stored.
     * @return File
     */
    public function setFolder($folder)
    {
        $this->folder = rtrim($folder, '/');
        return $this;
    }

}