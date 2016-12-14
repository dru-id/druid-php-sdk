<?php
namespace Genetsis\core\Config\Beans\Cache;

/**
 * This class keeps config parameters when cache should be stored in files.
 *
 * @package  Genetsis
 * @category Bean
 */
class File extends AbstractCache {

    /** @var string $folder Full path to folder where all cache files will be generated. */
    private $folder;

    /**
     * @param string $folder Full path to folder where all cache files will be generated. This folder must have granted write
     *      permissions. 
     */
    public function __construct($folder)
    {
        $this->setFolder($folder);
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param string $folder
     * @return File
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
        return $this;
    }

}