<?php namespace Genetsis\Core\OAuth\Beans\OAuthConfig;

/**
 * Class to store redirect url information.
 *
 * @package   Genetsis
 * @category  Bean
 */
class RedirectUrl {

    /** @var string $type */
    protected $type = '';

    /** @var string $url */
    protected $url = '';

    /** @var boolean $is_default */
    protected $is_default = false;

    /**
     * @param array $settings Initial settings. Array structure:
     *      [
     *          'type' => {@see RedirectUrl::setType},
     *          'url' => {@see RedirectUrl::setUrl},
     *          'is_default' => {@see RedirectUrl::setDefault}
     *      ]
     */
    public function __construct(array $settings = [])
    {
        if (isset($settings['type'])) { $this->setType($settings['type']); }
        if (isset($settings['url'])) { $this->setUrl($settings['url']); }
        if (isset($settings['is_default'])) { $this->setIsDefault($settings['is_default']); }
    }

    /**
     * @return string The URL.
     */
    public function __toString()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return RedirectUrl
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return RedirectUrl
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsDefault()
    {
        return $this->is_default;
    }

    /**
     * @param boolean $is_default
     * @return RedirectUrl
     */
    public function setIsDefault($is_default)
    {
        $this->is_default = ($is_default === true);
        return $this;
    }

}