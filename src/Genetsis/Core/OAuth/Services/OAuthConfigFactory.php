<?php
namespace Genetsis\Core\OAuth\Services;

use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;
use DOMDocument;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Api;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Brand;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\Core\OAuth\Beans\OAuthConfig\EndPoint;
use Genetsis\Core\OAuth\Beans\OAuthConfig\EntryPoint;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Host;
use Genetsis\Core\OAuth\Beans\OAuthConfig\RedirectUrl;
use Psr\Log\LoggerInterface;

/**
 * @package   Genetsis
 * @category  Service
 */
class OAuthConfigFactory {

    /** @var LoggerInterface $logger */
    protected $logger;
    /** @var DoctrineCacheInterface $cache */
    protected $cache;

    /**
     * @param LoggerInterface $logger
     * @param DoctrineCacheInterface $cache
     */
    public function __construct(LoggerInterface $logger, DoctrineCacheInterface $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * Builds a configuration object from a XML data stored in a file.
     *
     * @param string $file Full path to configuration file.
     * @return Config
     * @throws \InvalidArgumentException
     */
    public function buildConfigFromXmlFile($file)
    {
        if (!$file || !is_file($file) || !is_readable($file)) {
            $this->logger->error('The oauth configuration file does not exists.', ['method' => __METHOD__, 'line' => __LINE__]);
            throw new \InvalidArgumentException('Invalid configuration file.');
        }
        return $this->buildConfigFromXml(file_get_contents($file));
    }

    /**
     * Returns a configuration object filled from XML data.
     *
     * @param string $xml The XML to be parsed.
     * @return Config
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function buildConfigFromXml($xml)
    {
        // TODO: it remains to implement the cache system.

        try {
            if (!$xml) {
                $this->logger->error('The XML is empty.', ['method' => __METHOD__, 'line' => __LINE__]);
                throw new \InvalidArgumentException('Invalid configuration file.');
            }

            $xml_obj = new DOMDocument();
            if (!$xml_obj->loadXML($xml)) {
                $this->logger->error('The XML is invalid.', ['method' => __METHOD__, 'line' => __LINE__]);
                throw new \InvalidArgumentException('Invalid configuration file.');
            }

            $config = new Config();

            // XML version.
            foreach ($xml_obj->getElementsByTagName("oauth-config")->item(0)->attributes as $attrName => $attrNode) {
                if ($attrName == 'version') {
                    $config->setVersion($attrNode->value);
                }
            }
            if (!$config->getVersion()) {
                $this->logger->error('Unable to get the XML version.', ['method' => __METHOD__, 'line' => __LINE__]);
                throw new \Exception('Unable to verify XML version.');
            }

            // Client ID.
            $temp = $xml_obj->getElementsByTagName('clientid');
            if (($temp->length == 0) || !($temp->item(0) instanceof \DOMElement) || !$temp->item(0)->nodeValue) {
                $this->logger->error('Value of "clientid" is not defined', ['method' => __METHOD__, 'line' => __LINE__]);
                throw new \Exception('Invalid credentials');
            }
            $config->setClientId($temp->item(0)->nodeValue);

            // Client Secret.
            $temp = $xml_obj->getElementsByTagName('clientsecret');
            if (($temp->length == 0) || !($temp->item(0) instanceof \DOMElement) || !$temp->item(0)->nodeValue) {
                $this->logger->error('Value of "clientsecret" is not defined', ['method' => __METHOD__, 'line' => __LINE__]);
                throw new \Exception('Invalid credentials');
            }
            $config->setClientSecret($temp->item(0)->nodeValue);

            // Data. This node is not mandatory.
            $temp = $xml_obj->getElementsByTagName('data');
            if ($temp->length > 0) {
                foreach ($temp->item(0)->childNodes as $node) {
                    if ($node instanceof \DOMElement) {
                        switch ($node->nodeName) {
                            case 'name':
                                $config->setAppName(trim($node->nodeValue));
                                break;
                            case 'brand':
                                $config->setBrand(new Brand(['key' => $node->getAttribute('key'), 'name' => $node->nodeValue]));
                                break;
                            case 'opi':
                                $config->setOpi(trim($node->nodeValue));
                                break;
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
                $this->logger->error('The "redirections" node is not defined.', ['method' => __METHOD__, 'line' => __LINE__]);
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
                $this->logger->error('The "endpoints" node is not defined.', ['method' => __METHOD__, 'line' => __LINE__]);
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
                $this->logger->error('The "sections" node is not defined.', ['method' => __METHOD__, 'line' => __LINE__]);
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

        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Invalid configuration file: ' . $e->getMessage(), 0, $e);
        }
    }

}