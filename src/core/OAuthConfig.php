<?php
namespace Genetsis\core;

use DOMDocument;
use Exception;
use Genetsis\Config;

/**
 * Manages OAuth configuration file.
 *
 * @package Genetsis
 * @category Bean
 * @version 1.0
 * @access private
 * @todo Review source code.
 */
class OAuthConfig
{
    /** @var array Where the settings are saved. */
    private static $config = array();

    public static function init()
    {
        self::$config = FileCache::get('config');
        if (!self::$config) {
            self::loadXml($_SERVER['DOCUMENT_ROOT'] . '/' . Config::configPath());
        }
    }

    /**
     * Loads a XML from file.
     *
     * @param string Full path to file.
     * @return void
     * @throws \Exception If there is an error: file doesn't exists, not well-formed, ...
     */
    private static function loadXml($file)
    {
        if ((($file = trim((string)$file)) == '') || !file_exists($file) || !is_file($file) || !is_readable($file)) {
            throw new Exception('The config file is not found or can´t be readable' . $file);
        }

        $xmlObj = new DOMDocument();
        $xmlObj->load($file);

        try {

            foreach ($xmlObj->getElementsByTagName("oauth-config")->item(0)->attributes as $attrName => $attrNode) {
                if (!$version = $attrNode->value) {
                    throw new Exception('Not version');
                }
            }

            if ($version !== Config::CONF_VERSION) {
                throw new Exception('Not correct version');
            }

            if (!$credentials = $xmlObj->getElementsByTagName("credentials")->item(0)) {
                throw new Exception('Not credentials');
            }

            foreach ($credentials->childNodes as $node) {
                if ($node->nodeName === 'clientid') {
                    self::$config['clientid'] = $node->nodeValue;
                } else if ($node->nodeName === 'clientsecret') {
                    self::$config['clientsecret'] = $node->nodeValue;
                }
            }
            if ((!isset(self::$config['clientid'])) || (!isset(self::$config['clientsecret']))) {
                throw new Exception('Not defined credentials');
            }

            //data
            if (!$data = $xmlObj->getElementsByTagName("data")->item(0)) {
                throw new Exception('No data node');
            }

            foreach ($data->childNodes as $node) {
                if ($node->nodeName === 'name') {
                    self::$config['name'] = $node->nodeValue;
                } else if ($node->nodeName === 'brand') {
                    self::$config['brand'] = $node->attributes->getNamedItem('key')->nodeValue;
                    self::$config['brand-label'] = $node->nodeValue;
                }
            }
            if (!isset(self::$config['name'])) {
                throw new Exception('No name defined');
            }

            self::getParserXML($xmlObj, 'redirections', 'type');

            self::getParserXML($xmlObj, 'endpoints', 'id');
            // Parse urls from apis
            $apis = $xmlObj->getElementsByTagName("apis")->item(0);
            foreach ($apis->childNodes as $singleApi) {
                if ($singleApi->nodeType == XML_ELEMENT_NODE) {
                    $apiName = trim($singleApi->getAttribute('name'));

                    self::$config['apis'][$apiName]['base_url'] = trim($singleApi->getAttribute('base-url'));

                    foreach ($singleApi->childNodes as $urlNode) {
                        if ($urlNode->nodeType == XML_ELEMENT_NODE) {

                            self::$config['apis'][$apiName][$urlNode->getAttribute('id')] = $urlNode->nodeValue;
                        }
                    }
                }
            }
            self::getParserXML($xmlObj, 'sections', 'id');
            FileCache::set('config', self::$config, 600);
        } catch (Exception $e) {
            throw new Exception('The config file is not valid - ' . $e->getMessage());
        }
    }

    /**
     * Parse a XML section and loads it on "self::$config" variable.
     *
     * @param $xmlObj DOMDocument Object with XML loaded where search will performed.
     * @param $section string The section to be parsed. Example of sections: apis, enpoints, redirections, ... It depends on XML.
     * @param $typeId string The type of identifier. Example of identifiers: id, type, ... It depends on XML.
     * @throws \Exception If the sections is not defined
     * @return void
     */
    private static function getParserXML($xmlObj, $section, $typeId)
    {
        if (!$sections = $xmlObj->getElementsByTagName($section)->item(0)) {
            throw new Exception('Not ' . $section . ' Defined');
        }

        foreach ($sections->childNodes as $node) {

            if ($node->hasAttributes()) {
                $default = $node->getAttribute('default');
                $id = $node->getAttribute($typeId);
                $value = $node->nodeValue;

                /* Now redirections can to have more than one url for each type.
                 * Check if a value is previously assigned and if it is so then
                * a structure data (array) is created for this resource.
                * $redirectionsData = array(
                    *		// A list of all url of callbacks. Key is the value of url for callback and
                    *		// value is an array with all attribues for this redirection
                    *		'callbacks_list' => array(),
                    *		// Is default callback
                    *		'default_url' => '',
                    *		// A url for callback can to be forced by user.
                    *		'forced_url' => '',
                    *	);
                */
                if ($section == 'redirections') {

                    // Set to callback list
                    self::$config[$section][$id]['callbacks_list'][$value] = array(
                        'default' => $default
                    );

                    // Check if this url is a default url
                    if ($default) {
                        self::$config[$section][$id]['default_url'] = $value;
                    }
                    // Initialize forced url
                    self::$config[$section][$id]['forced_url'] = '';
                } // Sections
                else {
                    if ($section == 'sections') {

                        $promotionId = $node->getAttribute('promotionId');

                        $prizes = null;
                        foreach ($node->childNodes as $prizesDOM) {
                            if ($prizesDOM->nodeType == XML_ELEMENT_NODE) {
                                foreach ($prizesDOM->childNodes as $prizeNode) {
                                    if ($prizeNode->nodeType == XML_ELEMENT_NODE) {
                                        $prizes[$prizeNode->getAttribute('id')] = $prizeNode->getAttribute('id');
                                    }
                                }
                            }
                        }
                        // Sections has an structure of attributes
                        self::$config[$section][$id] = array(
                            'value' => $id,
                            'default' => $default,
                            'promotionId' => $promotionId,
                            'prizes' => $prizes
                        );
                    } else {
                        // Standar insert
                        self::$config[$section][$id] = $value;
                    }
                }
            }
        }
    }

    /**
     * Returns web client identifier.
     *
     * @return string Web client identifier. It could be empty.
     */
    public static function getClientId()
    {
        return (isset(self::$config['clientid']) ? self::$config['clientid'] : false);
    }

    /**
     * Returns web client secret.
     *
     * @return string Web client secret. It could be empty.
     */
    public static function getClientSecret()
    {
        return (isset(self::$config['clientsecret']) ? self::$config['clientsecret'] : false);
    }

    /**
     * Returns App name
     *
     * @return string App name
     */
    public static function getAppName()
    {
        return (isset(self::$config['name']) ? self::$config['name'] : false);
    }

    /**
     * Returns Brand asociated to this App (if defined)
     *
     * @return string Brand associated. It could be empty
     */
    public static function getBrand()
    {
        return (isset(self::$config['brand']) ? self::$config['brand'] : false);
    }

    /**
     * Returns Brand asociated to this App (if defined)
     *
     * @return string Brand associated. It could be empty
     */
    public static function getBrandLabel()
    {
        return (isset(self::$config['brand-label']) ? self::$config['brand-label'] : false);
    }

    /**
     * Returns an URL to redirect user.
     * Redirects can to have more than a url associate to a type.
     * Value in first position is the default value.
     *
     * @param string $type Identifier to select a URL type.
     * @param string $urlCallback Url for callback. This url must to be defined in 'oauthconf.xml'
     * @return string The URL selected. It could be empty if not exists
     *     that type or if $urlCallback is not defined in 'oauthconf.xml'.
     */
    public static function getRedirectUrl($type, $urlCallback = null)
    {
        $type = trim((string)$type);

        // Check if is defined
        if (isset(self::$config['redirections'][$type])) {
            // Check if value is a simple string (url).
            // Now redirections are the only defined by an array but other are strings
            if (is_string(self::$config['redirections'][$type])) {
                // Return url
                return self::$config['redirections'][$type];
            } // Value is a associative array
            else {
                // If $urlCallback is defined then check if exist and return
                if ($urlCallback !== null) {

                    // Check if url is defined in array.
                    if (isset(self::$config['redirections'][$type]['callbacks_list'][$urlCallback])) {
                        return $urlCallback;
                    } // Callback url is not define. Url is not valid.
                    else {
                        return false;
                    }
                } // Check if default url exist
                else {
                    if (self::$config['redirections'][$type]['default_url']) {
                        // Return default url
                        return self::$config['redirections'][$type]['default_url'];
                    } // Default url is not exist.
                    else {
                        // Return false
                        return false;
                    }
                }
            }
        }

        // Url for this type is not defined
        return false;
    }

    /**
     * Returns an endpoint to interact with Genetsis ID servers.
     *
     * @param $type string Identifier to select an URL.
     * @return string The URL selected. It could be empty if not exists that type.
     */
    public static function getEndpointUrl($type)
    {
        $type = trim((string)$type);
        return (isset(self::$config['endpoints'][$type]) ? self::$config['endpoints'][$type] : false);
    }

    /**
     * Returns an endpoint to interact with API-Query.
     *
     * @param $type Identifier to select an URL.
     * @param null $verb
     * @return bool
     */
    public static function getApiUrl($type, $verb = null)
    {
        $type = trim((string)$type);
        $verb = trim((string)$verb);
        return (isset(self::$config['apis'][$type][$verb]) ? self::$config['apis'][$type][$verb] : false);
    }

    /**
     * Returns a section.
     *
     * @param string $type Identifier to select a section.
     * @return string The section selected. It could be empty if not exists that type.
     */
    public static function getSection($type)
    {
        $type = trim((string)$type);
        return (isset(self::$config['sections'][$type]) ? self::$config['sections'][$type] : false);
    }

    /**
     * Return default section or false if no exist a default.
     *
     * @return mixed default section or false
     */
    public static function getDefaultSection()
    {
        // Looking for default section
        foreach (self::$config['sections'] as $item) {
            // If default is true return value
            if ($item['default']) {
                return $item['value'];
            }
        }
        return false;
    }

    /**
     * Return a prize for the section in activityID if exist
     *
     * @param $section Section identifier
     * @param $prize Prize identifier in ActivityID
     * @return mixed int prize ID in activityId, null if prize not exist
     */
    public static function getPrizeSection($section, $prize) {

        if (is_array(self::getSection($section)['prizes'])) {
            if (isset(self::getSection($section)['prizes'][$prize])) {
                return self::getSection($section)['prizes'][$prize];
            }
        }
        return null;
    }

    /**
     * Returns hosts for GID environement
     *
     * @param
     *            string Identifier to select an URL.
     * @return string The URL selected. It could be empty if not exists
     *         that type.
     */
    public static function getHost()
    {
        return (isset(self::$config['host']) ? self::$config['host'] : false);
    }

}
