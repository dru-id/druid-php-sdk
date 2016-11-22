<?php namespace Genetsis\tests\ServiceContainer;

use Genetsis\core\Http\Collections\HttpMethods as HttpMethodsCollection;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\Http\Services\Http;
use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
use Genetsis\core\Logger\Services\EmptyLogger;
use Genetsis\core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\core\OAuth\Contracts\StoredTokenInterface;
use Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException;
use Genetsis\core\ServiceContainer\Services\ServiceContainer;
use PHPUnit\Framework\TestCase;

class ServiceContainerServiceTest extends TestCase
{
    public function testLogger()
    {
        $this->expectException(InvalidServiceException::class);
        ServiceContainer::getLogger();

        $logger = new FooLogger();
        ServiceContainer::setLogger($logger);
        $this->assertInstanceOf('\Genetsis\tests\ServiceContainer\FooLogger', ServiceContainer::getLogger());
    }

    public function testHttp()
    {
        $this->expectException(InvalidServiceException::class);
        ServiceContainer::getHttpService();

        $http = new FooHttp();
        ServiceContainer::setHttpService($http);
        $this->assertInstanceOf('\Genetsis\tests\ServiceContainer\FooHttp', ServiceContainer::getHttpService());
    }

    public function testOAuth()
    {
        $this->expectException(InvalidServiceException::class);
        ServiceContainer::getOAuthService();

        $oauth = new FooOAuth();
        ServiceContainer::setOAuthService($oauth);
        $this->assertInstanceOf('\Genetsis\tests\ServiceContainer\FooOAuth', ServiceContainer::getOAuthService());
    }

    /**
     * @depends testLogger
     * @depends testHttp
     * @depends testOAuth
     */
    public function testInitEmpty()
    {
        ServiceContainer::init();
        $this->expectException(InvalidServiceException::class);
        ServiceContainer::getLogger();
    }

    /**
     * @depends testLogger
     * @depends testHttp
     * @depends testOAuth
     */
    public function testInitInvalidValue()
    {
        $this->expectException(InvalidServiceException::class);
        ServiceContainer::init([ new FooClass() ]);
        ServiceContainer::init([ 'foobar' ]);
    }

    /**
     * @depends testLogger
     * @depends testHttp
     * @depends testOAuth
     */
    public function testInitWithServices()
    {
        ServiceContainer::init([
            new FooHttp(),
            new FooLogger(),
            new FooOAuth()
        ]);
        $this->assertInstanceOf('\Genetsis\tests\ServiceContainer\FooLogger', ServiceContainer::getLogger());
        $this->assertInstanceOf('\Genetsis\tests\ServiceContainer\FooHttp', ServiceContainer::getHttpService());
        $this->assertInstanceOf('\Genetsis\tests\ServiceContainer\FooOAuth', ServiceContainer::getOAuthService());
    }
}

class FooClass {
}

class FooLogger implements LoggerServiceInterface {

    /**
     * @inheritDoc
     */
    public function debug($message, $method = null, $line = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function info($message, $method = null, $line = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function notice($message, $method = null, $line = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function warn($message, $method = null, $line = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function warning($message, $method = null, $line = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function err($message, $method = null, $line = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function error($message, $method = null, $line = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function crit($message, $method = null, $line = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function critical($message, $method = null, $line = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function alert($message, $method = null, $line = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function emerg($message, $method = null, $line = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function emergency($message, $method = null, $line = null)
    {
        return $this;
    }

}

class FooHttp implements HttpServiceInterface {

    /**
     * @inheritDoc
     */
    public function execute($url, $parameters = array(), $http_method = HttpMethodsCollection::GET, $credentials = false, $http_headers = array(), $cookies = array())
    {
    }

}

class FooOAuth implements OAuthServiceInterface {
    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        // TODO: Implement getConfig() method.
    }

    /**
     * @inheritDoc
     */
    public function setConfig(Config $config)
    {
        // TODO: Implement setConfig() method.
    }

    /**
     * @inheritDoc
     */
    public function doGetClientToken($endpoint_url)
    {
        // TODO: Implement doGetClientToken() method.
    }

    /**
     * @inheritDoc
     */
    public function storeToken(StoredTokenInterface $token)
    {
        // TODO: Implement storeToken() method.
    }

    /**
     * @inheritDoc
     */
    public function doGetAccessToken($endpoint_url, $code, $redirect_url)
    {
        // TODO: Implement doGetAccessToken() method.
    }

    /**
     * @inheritDoc
     */
    public function doRefreshToken($endpoint_url)
    {
        // TODO: Implement doRefreshToken() method.
    }

    /**
     * @inheritDoc
     */
    public function doValidateBearer($endpoint_url)
    {
        // TODO: Implement doValidateBearer() method.
    }

    /**
     * @inheritDoc
     */
    public function doExchangeSession($endpoint_url, $cookie_value)
    {
        // TODO: Implement doExchangeSession() method.
    }

    /**
     * @inheritDoc
     */
    public function doLogout($endpoint_url)
    {
        // TODO: Implement doLogout() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteStoredToken($name)
    {
        // TODO: Implement deleteStoredToken() method.
    }

    /**
     * @inheritDoc
     */
    public function hasToken($name)
    {
        // TODO: Implement hasToken() method.
    }

    /**
     * @inheritDoc
     */
    public function getStoredToken($name)
    {
        // TODO: Implement getStoredToken() method.
    }

    /**
     * @inheritDoc
     */
    public function doGetOpinator($endpoint_url, $scope, StoredTokenInterface $token)
    {
        // TODO: Implement doGetOpinator() method.
    }

    /**
     * @inheritDoc
     */
    public function doCheckUserCompleted($endpoint_url, $scope)
    {
        // TODO: Implement doCheckUserCompleted() method.
    }

    /**
     * @inheritDoc
     */
    public function doCheckUserNeedAcceptTerms($endpoint_url, $scope)
    {
        // TODO: Implement doCheckUserNeedAcceptTerms() method.
    }

}