<?php
namespace Genetsis\core\Config\Beans\Cache;

class File {

    /** @var string $cache_folder */
    private $cache_folder;

    public function __construct($cache_folder)
    {
        $this->setCacheFolder($cache_folder);
    }

    /**
     * @return string
     */
    public function getCacheFolder()
    {
        return $this->cache_folder;
    }

    /**
     * @param string $cache_folder
     * @return File
     */
    public function setCacheFolder($cache_folder)
    {
        $this->cache_folder = $cache_folder;
        return $this;
    }

}