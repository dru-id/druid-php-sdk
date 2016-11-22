<?php namespace Genetsis\core\OAuth\Services;

use DOMDocument;
use Genetsis\core\FileCache;
use Genetsis\Config as IniConfig;
use Genetsis\core\OAuth\Beans\OAuthConfig\Api;
use Genetsis\core\OAuth\Beans\OAuthConfig\Brand;
use Genetsis\core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\core\OAuth\Beans\OAuthConfig\EndPoint;
use Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint;
use Genetsis\core\OAuth\Beans\OAuthConfig\Host;
use Genetsis\core\OAuth\Beans\OAuthConfig\RedirectUrl;
use Genetsis\core\ServiceContainer\Services\ServiceContainer;

/**
 * @package   Genetsis
 * @category  Service
 */
class OAuthConfig {

    /**
     * Builds a configuration object from a XML data stored in a file.
     *
     * @param string $file Full path to configuration file.
     * @param string $version Version expected. We will use this value to check if the configuration file comply with
     *      the expected version.
     * @return Config
     * @throws \Exception
     */
    public static function buildConfigFromXmlFile($file, $version)
    {
        if (!$file || !file_exists($file) || !is_file($file) || !is_readable($file)) {
            ServiceContainer::getLogger()->error('', __METHOD__, __LINE__);
            throw new \Exception('Invalid configuration file.');
        }
        return static::buildConfigFromXml(file_get_contents($file), $version);
    }

    /**
     * Returns a configuration object filled from XML data.
     *
     * @param string $xml The XML to be parsed.
     * @param string $version Version expected. We will use this value to check if the configuration file comply with
     *      the expected version.
     * @return Config
     * @throws \Exception
     */
    public static function buildConfigFromXml($xml, $version)
    {
        // TODO: it remains to implement the cache system.


        if (!$xml) {
            ServiceContainer::getLogger()->error('The XML is empty.', __METHOD__, __LINE__);
            throw new \Exception('Invalid configuration file.');
        }

        $xml_obj = new DOMDocument();
        if (!$xml_obj->loadXML($xml)) {
            ServiceContainer::getLogger()->error('The XML is invalid.', __METHOD__, __LINE__);
            throw new \Exception('Invalid configuration file.');
        }

        try {
            // Checks the XML version.
            $xml_version = null;
            foreach ($xml_obj->getElementsByTagName("oauth-config")->item(0)->attributes as $attrName => $attrNode) {
                if ($attrName == 'version') {
                    $xml_version = $attrNode->value;
                }
            }
            if (!$xml_version || ($xml_version != $version)) {
                ServiceContainer::getLogger()->error('Invalid XML version: '.$xml_version.' (expected '.$version.')', __METHOD__, __LINE__);
                throw new \Exception('Invalid version. You are trying load a configuration file for another version of the service.');
            }

            $config = new Config();

            // Client ID.
            $temp = $xml_obj->getElementsByTagName('clientid');
            if (($temp->length == 0) || !($temp->item(0) instanceof \DOMElement) || !$temp->item(0)->nodeValue) {
                ServiceContainer::getLogger()->error('Value of "clientid" is not defined', __METHOD__, __LINE__);
                throw new \Exception('Invalid credentials');
            }
            $config->setClientId($temp->item(0)->nodeValue);

            // Client Secret.
            $temp = $xml_obj->getElementsByTagName('clientsecret');
            if (($temp->length == 0) || !($temp->item(0) instanceof \DOMElement) || !$temp->item(0)->nodeValue) {
                ServiceContainer::getLogger()->error('Value of "clientsecret" is not defined', __METHOD__, __LINE__);
                throw new \Exception('Invalid credentials');
            }
            $config->setClientSecret($temp->item(0)->nodeValue);

            // Data. This node is not mandatory.
            $temp = $xml_obj->getElementsByTagName('data');
            if ($temp->length > 0) {
                foreach ($temp->item(0)->childNodes as $node) {
                    if ($node instanceof \DOMElement) {
                        switch ($node->nodeName) {
                            case 'name': $config->setAppName(trim($node->nodeValue)); break;
                            case 'brand': $config->setBrand(new Brand(['key' => $node->getAttribute('key'), 'name' => $node->nodeValue])); break;
                            case 'opi': $config->setOpi(trim($node->nodeValue)); break;
                        }
                    }
                }
            }

            // Hosts. This node is not mandatory.
            $temp = $xml_obj->getElementsByTagName('hosts');
            if ($temp->length > 0) {
                foreach ($temp->item(0)->childNodes as $node) {
                    if (($node instanceof \DOMElement) && $node->hasAttributes() && $node->getAttribute('id')) {
                        $config->addHost(new Host([
                            'id' => $node->getAttribute('id'),
                            'url' => $node->nodeValue
                        ]));
                    }
                }
            }

            // Redirects.
            $temp = $xml_obj->getElementsByTagName('redirections');
            if ($temp->length == 0) {
                ServiceContainer::getLogger()->error('The "redirections" node is not defined.', __METHOD__, __LINE__);
                throw new \Exception('The XML is invalid.');
            }
            foreach ($temp->item(0)->childNodes as $node) {
                if (($node instanceof \DOMElement) && $node->hasAttributes() && $node->getAttribute('type')) {
                    $config->addRedirect(new RedirectUrl([
                        'type' => $node->getAttribute('type'),
                        'url' => $node->nodeValue,
                        'is_default' => (strtolower($node->getAttribute('default')) == 'true')
                    ]));
                }
            }

            // Endpoints.
            $temp = $xml_obj->getElementsByTagName('endpoints');
            if ($temp->length == 0) {
                ServiceContainer::getLogger()->error('The "endpoints" node is not defined.', __METHOD__, __LINE__);
                throw new \Exception('The XML is invalid.');
            }
            foreach ($temp->item(0)->childNodes as $node) {
                if (($node instanceof \DOMElement) && $node->hasAttributes() && $node->getAttribute('id')) {
                    $config->addEndPoint(new EndPoint([
                        'id' => $node->getAttribute('id'),
                        'url' => $node->nodeValue
                    ]));
                }
            }

            // Entry points.
            $temp = $xml_obj->getElementsByTagName('sections');
            if ($temp->length == 0) {
                ServiceContainer::getLogger()->error('The "sections" node is not defined.', __METHOD__, __LINE__);
                throw new \Exception('The XML is invalid.');
            }
            foreach ($temp->item(0)->childNodes as $node) {
                if (($node instanceof \DOMElement) && $node->hasAttributes() && $node->getAttribute('id')) {
                    $ep = new EntryPoint([
                        'id' => $node->getAttribute('id'),
                        'promotion_id' => $node->getAttribute('promotionId')
                    ]);

                    // Get prizes.
                    foreach ($node->childNodes as $prizes_dom) {
                        if ($prizes_dom->nodeType == XML_ELEMENT_NODE) {
                            foreach ($prizes_dom->childNodes as $prize_node) {
                                if ($prize_node->nodeType == XML_ELEMENT_NODE) {
                                    $ep->addPrize($prize_node->getAttribute('id'), $prize_node->getAttribute('id'));
                                }
                            }
                        }
                    }

                    $config->addEntryPoint($ep, (strtolower($node->getAttribute('default')) == 'true'));
                }
            }

            // APIs
            $temp = $xml_obj->getElementsByTagName('apis');
            if ($temp->length > 0) {
                foreach ($temp->item(0)->childNodes as $node) {
                    if (($node instanceof \DOMElement) && $node->hasAttributes() && $node->getAttribute('name')) {
                        $api = new Api([
                            'name' => $node->getAttribute('name'),
                            'base_url' => $node->getAttribute('base-url')
                        ]);
                        foreach ($node->childNodes as $url_node) {
                            if (($url_node instanceof \DOMElement) && $url_node->hasAttributes() && $node->getAttribute('id')) {
                                $api->addEndpoint($url_node->getAttribute('id'), $url_node->nodeValue);
                            }
                        }
                        $config->addApi($api);
                    }
                }
            }

            // TODO: it remains to implement the cache system.
//            FileCache::set('config', self::$config, 600);

            return $config;

        } catch (\Exception $e) {
            throw new \Exception('Invalid configuration file: ' . $e->getMessage());
        }
    }

}