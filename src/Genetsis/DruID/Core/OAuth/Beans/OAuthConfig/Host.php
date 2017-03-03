<?php namespace Genetsis\DruID\Core\OAuth\Beans\OAuthConfig;

/**
 * Class to store host information.
 *
 * @author Genetsis
 * @link http://developers.dru-id.com
 */
class Host {

    /** @var string $id */
    protected $id = '';

    /** @var string $url */
    protected $url = '';

    /**
     * @param array $settings Initial settings. Array structure:
     *      [
     *          'id' => {@see Host::setId},
     *          'url' => {@see Host::setUrl},
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
     * @return Host
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
     * @return Host
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

}