<?php namespace Genetsis\core\OAuth\Beans\OAuthConfig;

/**
 * @package   Genetsis
 * @category  Bean
 */
class Brand
{
    /** @var string $key */
    private $key = '';
    /** @var string $name */
    private $name = '';

    /**
     * @param array $settings Initial settings. Array structure:
     *      [
     *          'key' => {@see Brand::setKey},
     *          'name' => {@see Brand::setName},
     *      ]
     */
    public function __construct(array $settings = [])
    {
        if (isset($settings['key'])) { $this->setKey($settings['key']); }
        if (isset($settings['name'])) { $this->setName($settings['name']); }
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Brand
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Brand
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

}