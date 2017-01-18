<?php
namespace Genetsis\Core\Config\Beans\Cache;

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
     * @param string $group Name to keep logs under the same group.
     * @param string $folder Full path to folder where all cache files will be generated. This folder must have granted write
     *      permissions. 
     */
    public function __construct($group, $folder)
    {
        parent::__construct($group);
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
        $this->folder = rtrim($folder, '/');
        return $this;
    }

}