<?php
namespace Genetsis\UnitTest\ServiceContainer;

use Codeception\Specify;
use Codeception\Test\Unit;
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

/**
 * @package Genetsis
 * @category UnitTest
 */
class ServiceContainerServiceTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
    }

    protected function _after()
    {
    }

    public function testInitEmpty()
    {
        $this->specify('Checks if an exception is thrown when the "ServiceContainer" is empty initialized and we require a service.', function() {
            ServiceContainer::init();
            ServiceContainer::getHttpService();
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);
    }

    /**
     * @depends testInitEmpty
     */
    public function testLogger()
    {
        $this->specify('Checks default logger.', function() {
            $this->assertInstanceOf('\Genetsis\core\Logger\Services\EmptyLogger', ServiceContainer::getLogger());
        });

        $this->specify('Checks setter and getter for "Logger" service.', function() {
            $logger = new FooLogger();
            ServiceContainer::setLogger($logger);
            $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooLogger', ServiceContainer::getLogger());
        });

        $this->specify('Checks if the service has been reset.', function() {
            $logger = new FooLogger();
            ServiceContainer::setLogger($logger);
            $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooLogger', ServiceContainer::getLogger());
            ServiceContainer::setLogger(null);
            $this->assertInstanceOf('\Genetsis\core\Logger\Services\EmptyLogger', ServiceContainer::getLogger());
        });
    }

    /**
     * @depends testInitEmpty
     */
    public function testHttp()
    {
        $this->specify('Checks if an exception is thrown when we request a service and the "ServiceContainer" is not been initialized.', function() {
            ServiceContainer::getHttpService();
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);

        $this->specify('Checks setter and getter for "Http" service.', function() {
            $http = new FooHttp();
            ServiceContainer::setHttpService($http);
            $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooHttp', ServiceContainer::getHttpService());
        });

        $this->specify('Checks if the service has been reset.', function() {
            $http = new FooHttp();
            ServiceContainer::setHttpService($http);
            $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooHttp', ServiceContainer::getHttpService());
            ServiceContainer::setHttpService(null);
            ServiceContainer::getHttpService();
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);
    }

    /**
     * @depends testInitEmpty
     */
    public function testOAuth()
    {
        $this->specify('Checks if an exception is thrown when we request a service and the "ServiceContainer" is not been initialized.', function() {
            ServiceContainer::getOAuthService();
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);

        $this->specify('Checks setter and getter for "OAuth" service.', function() {
            $oauth = new FooOAuth();
            ServiceContainer::setOAuthService($oauth);
            $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooOAuth', ServiceContainer::getOAuthService());
        });

        $this->specify('Checks if the service has been reset.', function() {
            $oauth = new FooOAuth();
            ServiceContainer::setOAuthService($oauth);
            $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooOAuth', ServiceContainer::getOAuthService());
            ServiceContainer::setOAuthService(null);
            ServiceContainer::getOAuthService();
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);
    }

    /**
     * @depends testLogger
     * @depends testHttp
     * @depends testOAuth
     */
    public function testInitInvalidValue()
    {
        $this->specify('Checks if an exception is thrown when trying to register invalid services.', function() {
            ServiceContainer::init([ new FooClass() ]);
            ServiceContainer::init([ 'foobar' ]);
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);
    }

    /**
     * @depends testLogger
     * @depends testHttp
     * @depends testOAuth
     */
    public function testInitWithServices()
    {
        ServiceContainer::init([ new FooHttp(), new FooLogger(), new FooOAuth() ]);
        $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooLogger', ServiceContainer::getLogger());
        $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooHttp', ServiceContainer::getHttpService());
        $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooOAuth', ServiceContainer::getOAuthService());
    }

    /**
     * @depends testLogger
     * @depends testHttp
     * @depends testOAuth
     */
    public function testResetServices()
    {
        ServiceContainer::init([ new FooHttp(), new FooLogger(), new FooOAuth() ]);
        $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooLogger', ServiceContainer::getLogger());
        $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooHttp', ServiceContainer::getHttpService());
        $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooOAuth', ServiceContainer::getOAuthService());
        ServiceContainer::reset();
        $this->specify('Checks if the service has been reset.', function() {
            $this->assertInstanceOf('\Genetsis\core\Logger\Services\EmptyLogger', ServiceContainer::getLogger());
        });
        $this->specify('Checks if the service has been reset.', function() {
            ServiceContainer::getHttpService();
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);
        $this->specify('Checks if the service has been reset.', function() {
            ServiceContainer::getOAuthService();
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);
    }
}

/**
 * Foo class for testing purpose.
 *
 * @package Genetsis
 * @category UnitTest\FooClass
 */
class FooClass {
}

/**
 * Foo class for testing purpose.
 *
 * @package Genetsis
 * @category UnitTest\FooClass
 */
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

/**
 * Foo class for testing purpose.
 *
 * @package Genetsis
 * @category UnitTest\FooClass
 */
class FooHttp implements HttpServiceInterface {

    /**
     * @inheritDoc
     */
    public function execute($url, $parameters = array(), $http_method = HttpMethodsCollection::GET, $http_headers = array(), $cookies = array())
    {
    }

}

/**
 * Foo class for testing purpose.
 *
 * @package Genetsis
 * @category UnitTest\FooClass
 */
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