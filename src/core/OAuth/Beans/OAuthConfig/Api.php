<?php namespace Genetsis\core\OAuth\Beans\OAuthConfig;

/**
 * Class to store APIs information.
 *
 * @package   Genetsis
 * @category  Bean
 */
class Api {

    /** @var string $name API name. */
    protected $name = '';

    /** @var string $base_url */
    protected $base_url = '';

    /** @var array $endpoints */
    protected $endpoints = [];

    /**
     * @param array $settings Initial settings. Array structure:
     *      [
     *          'name' => {@see Api::setName},
     *          'base_url' => {@see Api::setBaseUrl},
     *          'endpoints' => {@see Api::setEndpoints},
     *      ]
     */
    public function __construct(array $settings = [])
    {
        if (isset($settings['name'])) { $this->setName($settings['name']); }
        if (isset($settings['base_url'])) { $this->setBaseUrl($settings['base_url']); }
        if (isset($settings['endpoints'])) { $this->setEndpoints($settings['endpoints']); }
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
     * @return Api
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }

    /**
     * @param string $base_url
     * @return Api
     */
    public function setBaseUrl($base_url)
    {
        $this->base_url = rtrim($base_url, '/');
        return $this;
    }

    /**
     * @return array
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }

    /**
     * @param string $id
     * @param boolean $include_base_url TRUE to include the base URL or FALSE if not.
     * @return string|false
     */
    public function getEndpoint($id, $include_base_url = false)
    {
        return ($id && isset($this->endpoints[$id]))
            ? (($include_base_url === true) ? $this->base_url.$this->endpoints[$id] : $this->endpoints[$id])
            : false;
    }

    /**
     * @param array $endpoints Set of endpoints. The "key" will be the endpoint identifier and the "value" the endpoint
     *      itself. Example:
     *          [
     *              'participate' => '/v1/game/request',
     *              'share' => '/v1/
     *          ]
     * @return Api
     */
    public function setEndpoints(array $endpoints)
    {
        $this->endpoints = [];
        foreach ($endpoints as $key => $val) {
            $this->addEndpoint($key, $val);
        }
        return $this;
    }

    /**
     * @param string $id
     * @param string $endpoint
     * @return Api
     */
    public function addEndpoint($id, $endpoint)
    {
        if ($id) {
            $this->endpoints[$id] = '/'.ltrim($endpoint, '/');
        }
        return $this;
    }

}