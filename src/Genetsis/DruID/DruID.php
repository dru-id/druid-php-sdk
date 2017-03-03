<?php
namespace Genetsis\DruID;

use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;
use Doctrine\Common\Cache\Cache;
use Genetsis\DruID\Core\Config\Beans\Config;
use Genetsis\DruID\Core\Http\Cookies;
use Genetsis\DruID\Core\Http\Http;
use Genetsis\DruID\Core\Http\Session;
use Genetsis\DruID\Core\OAuth\OAuth;
use Genetsis\DruID\Core\OAuth\OAuthConfigFactory;
use Genetsis\DruID\Identity\Contracts\IdentityServiceInterface;
use Genetsis\DruID\Identity\Identity;
use Genetsis\DruID\Opi\Opi;
use Genetsis\DruID\Opi\Contracts\OpiServiceInterface;
use Genetsis\DruID\UrlBuilder\Contracts\UrlBuilderServiceInterface;
use Genetsis\DruID\UrlBuilder\UrlBuilder;
use Genetsis\DruID\UserApi\Contracts\UserApiServiceInterface;
use Genetsis\DruID\UserApi\UserApi;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * This is the main class for DruID library. All starts here.
 *
 * @author Genetsis
 * @link http://developers.dru-id.com
 */
class DruID
{

    // Indicates which is the OAuth configuration file version is accepted by this library.
    const CONF_VERSION = '1.4';

    /** @var Config $config */
    private $config;
    /** @var IdentityServiceInterface $identity */
    private $identity;
    /** @var UrlBuilderServiceInterface $url_builder */
    private $url_builder;
    /** @var UserApiServiceInterface $user_api */
    private $user_api;
    /** @var OpiServiceInterface $opi */
    private $opi;

    /**
     * Instantiating this class does not sync the library with remote DruID web services, is just the setup. If you need
     * to interact with the library you have to call {@link DruID->identity->synchronizeSessionWithServer} before
     * anything.
     *
     * @param Config $config
     * @param string $oauth_config_xml OAuth configuration XML content.
     * @param LoggerInterface $logger
     * @param DoctrineCacheInterface $cache {@see http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/reference/caching.html}
     * @throws \Exception
     */
    public function __construct (Config $config, $oauth_config_xml, LoggerInterface $logger, Cache $cache)
    {
        $this->config = $config;

        // Http service.
        // We associate the logger received with the Guzzle client to register the requests.
        $stack = HandlerStack::create();
        $stack->push(Middleware::log($logger, new MessageFormatter(), LogLevel::INFO));
        $stack->push(Middleware::log($logger, new MessageFormatter("\nREQUEST:\n{request}\n\nRESPONSE:\n{response}\n"), LogLevel::DEBUG));
        // Do not verify SSL for self-signed certifies. Only for development.
        $http = new Http(new Client(['handler' => $stack, 'http_errors' => false, 'verify' => false]), $logger);

        // OAuth service
        $oauth_config = (new OAuthConfigFactory($logger, $cache))->buildConfigFromXml($oauth_config_xml);
        if ($oauth_config->getVersion() != self::CONF_VERSION) {
            $logger->error('Invalid XML version: ' . $oauth_config->getVersion() . ' (expected ' . self::CONF_VERSION . ')', ['method' => __METHOD__, 'line' => __LINE__]);
            throw new \Exception('Invalid version. You are trying load a configuration file for another version of the service.');
        }
        $oauth = new OAuth($this, $oauth_config, $http, new Cookies(), $logger);

        $this->identity = new Identity($oauth, new Session(), new Cookies(), $logger, $cache);
        $this->user_api = new UserApi($this->identity, $oauth, $http, $logger, $cache);
        $this->url_builder = new UrlBuilder($this->identity, $this->user_api, $oauth, $logger);
        $this->opi = new Opi($this->user_api, $oauth);
    }

    /**
     * Returns an instance of identity service.
     *
     * @return IdentityServiceInterface
     * @throws \Exception
     */
    public function identity()
    {
        return $this->identity;
    }

    /**
     * Returns an instance of URL builder service.
     *
     * @return UrlBuilderServiceInterface
     * @throws \Exception
     */
    public function urlBuilder()
    {
        return $this->url_builder;
    }

    /**
     * Returns an instance of user API service.
     *
     * @return UserApiServiceInterface
     * @throws \Exception
     */
    public function userApi()
    {
        return $this->user_api;
    }

    /**
     * Returns an instance of OPI service.
     *
     * @return OpiServiceInterface
     * @throws \Exception
     */
    public function opi()
    {
        return $this->opi;
    }
}
