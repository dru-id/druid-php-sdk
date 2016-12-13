<?php
namespace Genetsis\UnitTest\ServiceContainer;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Http\Collections\HttpMethods as HttpMethodsCollection;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
use Genetsis\core\Logger\Services\EmptyLogger;
use Genetsis\core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\core\OAuth\Contracts\StoredTokenInterface;
use Genetsis\core\ServiceContainer\Contracts\ServiceContainerFacadeInterface;
use Genetsis\core\ServiceContainer\Facades\ServiceContainer as SC;
use Genetsis\core\ServiceContainer\Services\ServiceContainer;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis
 * @category UnitTest
 */
class ServiceContainerFacadeTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var ServiceContainerFacadeInterface $sc */
    protected $sc;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testLogger()
    {
        $this->sc = new SC(new ServiceContainer());

        $this->specify('Checks logger setter with a valid closure.', function(){
            $this->assertTrue($this->sc->setLogger(function(){
                return new EmptyLogger();
            }));
        });

        $this->specify('Checks if the logger setter throws an exception with an invalid closure.', function(){
            $this->sc->setLogger('');
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);

        $this->specify('Checks logger getter with a valid closure.', function(){
            $this->assertTrue($this->sc->setLogger(function(){
                return new EmptyLogger();
            }));
            $this->assertInstanceOf('\Genetsis\core\Logger\Services\EmptyLogger', $this->sc->getLogger());
            $this->assertTrue($this->sc->setLogger(function(){
                return new FooLogger();
            }));
            $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooLogger', $this->sc->getLogger());
        });

        $this->specify('Checks if the logger getter throws an exception with a closure that returns an object which is not a valid logger.', function(){
            $this->assertTrue($this->sc->setLogger(function(){
                return new \stdClass();
            }));
            $this->sc->getLogger();
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);
    }

    public function testHttp()
    {
        $this->sc = new SC(new ServiceContainer());

        $this->specify('Checks http setter with a valid closure.', function(){
            $this->assertTrue($this->sc->setHttpService(function(){
                return new FooHttp();
            }));
        });

        $this->specify('Checks if the http setter throws an exception with an invalid closure.', function(){
            $this->sc->setHttpService('');
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);

        $this->specify('Checks http getter with a valid closure.', function(){
            $this->assertTrue($this->sc->setHttpService(function(){
                return new FooHttp();
            }));
            $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooHttp', $this->sc->getHttpService());
            $this->assertTrue($this->sc->setHttpService(function(){
                return new FooHttp2();
            }));
            $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooHttp2', $this->sc->getHttpService());
        });

        $this->specify('Checks if the http getter throws an exception with a closure that returns an object which is not a valid http service.', function(){
            $this->assertTrue($this->sc->setHttpService(function(){
                return new \stdClass();
            }));
            $this->sc->getHttpService();
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);
    }

    public function testOAuth()
    {
        $this->sc = new SC(new ServiceContainer());

        $this->specify('Checks oauth setter with a valid closure.', function(){
            $this->assertTrue($this->sc->setOAuthService(function(){
                return new FooOAuth();
            }));
        });

        $this->specify('Checks if the oauth setter throws an exception with an invalid closure.', function(){
            $this->sc->setOAuthService('');
        }, ['throws' => 'Genetsis\core\ServiceContainer\Exceptions\InvalidServiceException']);

        $this->specify('Checks oauth getter with a valid closure.', function(){
            $this->assertTrue($this->sc->setOAuthService(function(){
                return new FooOAuth();
            }));
            $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooOAuth', $this->sc->getOAuthService());
            $this->assertTrue($this->sc->setOAuthService(function(){
                return new FooOAuth2();
            }));
            $this->assertInstanceOf('\Genetsis\UnitTest\ServiceContainer\FooOAuth2', $this->sc->getOAuthService());
        });

        $this->specify('Checks if the oauth getter throws an exception with a closure that returns an object which is not a valid oauth service.', function(){
            $this->assertTrue($this->sc->setOAuthService(function(){
                return new \stdClass();
            }));
            $this->sc->getOAuthService();
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
class FooHttp2 extends FooHttp {}

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

/**
 * Foo class for testing purpose.
 *
 * @package Genetsis
 * @category UnitTest\FooClass
 */
class FooOAuth2 extends FooOAuth {}