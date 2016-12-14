<?php namespace Genetsis\core\OAuth\Beans\OAuthConfig;

/**
 * Class to store the configuration data for OAuth library.
 *
 * @package   Genetsis
 * @category  Bean
 */
class Config {

    /** @var string $version */
    protected $version;
    /** @var string $client_id */
    protected $client_id = '';
    /** @var string $client_secret */
    protected $client_secret = '';
    /** @var string $app_name */
    protected $app_name = '';
    /** @var Brand $brand */
    protected $brand = null;
    /** @var string $opi */
    protected $opi = '';
    /** @var array $hosts A set of {@link EndPoint} instances. */
    protected $hosts = [];
    /** @var array $endpoints A set of {@link EndPoint} instances. */
    protected $endpoints = [];
    /** @var array $sections Entry points data. See {@link Config::getEntryPoints} for array structure. */
    protected $entry_points = ['entry_points' => [], 'default' => ''];
    /** @var array $redirects Redirect URLs data. See {@link Config::getRedirects} for array structure. */
    protected $redirects = [];
    /** @var array $apis APIs data. See {@link Config::getApis} for array structure. */
    protected $apis = [];

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return Config
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @param string $client_id
     * @return Config
     */
    public function setClientId($client_id)
    {
        $this->client_id = trim($client_id);
        return $this;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * @param string $client_secret
     * @return Config
     */
    public function setClientSecret($client_secret)
    {
        $this->client_secret = trim($client_secret);
        return $this;
    }

    /**
     * @return string
     */
    public function getAppName()
    {
        return $this->app_name;
    }

    /**
     * @param string $app_name
     * @return Config
     */
    public function setAppName($app_name)
    {
        $this->app_name = $app_name;
        return $this;
    }

    /**
     * @return Brand|null
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     * @return Config
     */
    public function setBrand(Brand $brand)
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return string
     */
    public function getOpi()
    {
        return $this->opi;
    }

    /**
     * @param string $opi
     * @return Config
     */
    public function setOpi($opi)
    {
        $this->opi = $opi;
        return $this;
    }

    /**
     * @return array Array returned:
     *      [
     *          host_id => host_data,
     *          host_id => host_data,
     *          ...
     *      ]
     *
     *      - "host_id" refers to host identifier.
     *      - "host_data" is an instance of {@link Host}
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * @param string $id Host identifier.
     * @return Host|false
     */
    public function getHost($id)
    {
        return ($id && isset($this->hosts[$id])) ? $this->hosts[$id] : false;
    }

    /**
     * @param array $hosts A set of {@link Host} objects.
     * @return Config
     */
    public function setHosts(array $hosts)
    {
        $this->hosts = [];
        foreach ($hosts as $h) {
            if ($h instanceof Host) {
                $this->addHost($h);
            }
        }
        return $this;
    }

    /**
     * @param Host $host If the host already exists then will be overwritten.
     * @return Config
     */
    public function addHost(Host $host)
    {
        $this->hosts[$host->getId()] = $host;
        return $this;
    }

    /**
     * @return array Array returned:
     *      [
     *          endpoint_id => endpoint_data,
     *          endpoint_id => endpoint_data,
     *          ...
     *      ]
     *
     *      - "endpoint_id" refers to endpoint identifier.
     *      - "endpoint_data" is an instance of {@link EndPoint}
     */
    public function getEndPoints()
    {
        return $this->endpoints;
    }

    /**
     * @param string $id Endpoint identifier.
     * @return EndPoint|false
     */
    public function getEndPoint($id)
    {
        return ($id && isset($this->endpoints[$id])) ? $this->endpoints[$id] : false;
    }

    /**
     * @param array $endpoints A set of {@link EndPoint} objects.
     * @return Config
     */
    public function setEndPoints(array $endpoints)
    {
        $this->endpoints = [];
        foreach ($endpoints as $ep) {
            if ($ep instanceof EndPoint) {
                $this->addEndPoint($ep);
            }
        }
        return $this;
    }

    /**
     * @param EndPoint $end_point
     * @return Config
     */
    public function addEndPoint(EndPoint $end_point)
    {
        $this->endpoints[$end_point->getId()] = $end_point;
        return $this;
    }

    /**
     * @return array Array returned:
     *      [
     *          'entry_points' => [
     *              entry_point_id => entry_point_data,
     *              entry_point_id => entry_point_data,
     *              ...
     *          ],
     *          'default' => entry_point_id
     *      ]
     *
     *      - "entry_point_id" refers to the entry point identifier.
     *      - "entry_point_data" is an instance of {@link EntryPoint}
     *      - If there is no entry points then "default" will be empty.
     */
    public function getEntryPoints()
    {
        return $this->entry_points;
    }

    /**
     * Retrieve a single entry point by its ID.
     *
     * @param string|null $id If not defined the default entry point will be returned.
     * @return EntryPoint|false
     */
    public function getEntryPoint($id = null)
    {
        if (!$id) { // Default entry point.
            return isset($this->entry_points['entry_points'][$this->entry_points['default']])
                ? $this->entry_points['entry_points'][$this->entry_points['default']]
                : false;
        } else {
            return isset($this->entry_points['entry_points'][$id]) ? $this->entry_points['entry_points'][$id] : false;
        }
    }

    /**
     * @param array $entry_points A set of {@link EntryPoint} instances. Old entry points will be removed.
     * @param string $default Default entry point identifier. If not defined then the first one will be chosen.
     * @return Config
     */
    public function setEntryPoints(array $entry_points, $default)
    {
        $this->entry_points = ['entry_points' => [], 'default' => ''];
        foreach ($entry_points as $ep) {
            if ($ep instanceof EntryPoint) {
                $this->addEntryPoint($ep, ($ep->getId() == $default));
            }
        }
        return $this;
    }

    /**
     * @param EntryPoint $entry_point
     * @param boolean $is_default
     * @return Config
     */
    public function addEntryPoint(EntryPoint $entry_point, $is_default = false)
    {
        $this->entry_points['entry_points'][$entry_point->getId()] = $entry_point;
        if ($is_default) {
            $this->entry_points['default'] = $entry_point->getId();
        }
        return $this;
    }

    /**
     * @return array Array returned:
     *      [
     *          type_id => [
     *              'callbacks' => [
     *                  0 => url_data,
     *                  1 => url_data,
     *                  ...
     *              ]
     *              'default' => callback_id
     *          ],
     *          type_id => [
     *              'callbacks' => [
     *                  0 => url_data,
     *                  ...
     *              ]
     *              'default' => callback_id
     *          ],
     *          ...
     *      ]
     *
     *      - "type_id" refers to redirect type identifier.
     *      - "url_data" is an instance of {@link RedirectUrl}
     *      - If there are no callbacks in each type then "default" will be empty.
     */
    public function getRedirects()
    {
        return $this->redirects;
    }

    /**
     * Retrieve a single redirect url.
     *
     * @param string $type
     * @param string|null $callback
     * @return RedirectUrl|false
     */
    public function getRedirect($type, $callback = null)
    {
        if (!$type || !isset($this->redirects[$type])) {
            return false;
        }

        // We are looking for an specific callback.
        if (is_string($callback) && $callback) {
            foreach ($this->redirects[$type]['callbacks'] as $cb) {
                if ($cb->getUrl() == $callback) {
                    return $cb;
                }
            }
            return false;
        }

        // Default callback.
        return (isset($this->redirects[$type]['callbacks'][$this->redirects[$type]['default']]))
            ? $this->redirects[$type]['callbacks'][$this->redirects[$type]['default']]
            : false;
    }

    /**
     * @param array $redirects A set of {@link RedirectUrl} instances. Old urls will be removed.
     * @return Config
     */
    public function setRedirects(array $redirects)
    {
        $this->redirects = [];
        foreach ($redirects as $redirect) {
            if ($redirect instanceof RedirectUrl) {
                $this->addRedirect($redirect);
            }
        }
        return $this;
    }

    /**
     * @param RedirectUrl $redirect
     * @return Config
     */
    public function addRedirect(RedirectUrl $redirect)
    {
        if (!isset($this->redirects[$redirect->getType()])) {
            $this->redirects[$redirect->getType()] = ['callbacks' => [], 'default' => 0];
        }
        $this->redirects[$redirect->getType()]['callbacks'][] = $redirect;
        if ($redirect->getIsDefault()) {
            $this->redirects[$redirect->getType()]['default'] = array_pop(array_keys($this->redirects[$redirect->getType()]['callbacks']));
        }
        return $this;
    }

    /**
     * @return array Array returned:
     *      [
     *          api_name => api_data,
     *          api_name => api_data,
     *          ...
     *      ]
     *
     *      - "api_id" refers to API identifier.
     *      - "api_data" is an instance of {@link Api}
     */
    public function getApis()
    {
        return $this->apis;
    }

    /**
     * Retrieve a single API data.
     *
     * @param string $api_name
     * @return Api|false
     */
    public function getApi($api_name)
    {
        return ($api_name && isset($this->apis[$api_name])) ? $this->apis[$api_name] : false;
    }

    /**
     * @param array $apis A set of {@link Api} instances. Old apis will be removed.
     * @return Config
     */
    public function setApis(array $apis)
    {
        $this->apis = [];
        foreach ($apis as $api) {
            if ($api instanceof Api) {
                $this->addApi($api);
            }
        }
        return $this;
    }

    /**
     * @param Api $api
     * @return Config
     */
    public function addApi(Api $api)
    {
        $this->apis[$api->getName()] = $api;
        return $this;
    }

}