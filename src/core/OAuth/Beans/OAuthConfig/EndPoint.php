<?php namespace Genetsis\core\OAuth\Beans\OAuthConfig;

/**
 * Class to store endpoint information.
 *
 * @package   Genetsis
 * @category  Bean
 */
class EndPoint {

    /** @var string $id */
    protected $id = '';

    /** @var string $url */
    protected $url = '';

    /**
     * @param array $settings Initial settings. Array structure:
     *      [
     *          'id' => {@see EndPoint::setId},
     *          'url' => {@see EndPoint::setUrl},
     *      ]
     */
    public function __construct(array $settings = [])
    {
        if (isset($settings['id'])) { $this->setId($settings['id']); }
        if (isset($settings['url'])) { $this->setUrl($settings['url']); }
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return EndPoint
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return EndPoint
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

}