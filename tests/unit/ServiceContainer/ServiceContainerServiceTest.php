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
use Genetsis\core\ServiceContainer\Contracts\ServiceContainerInterface;
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

    /** @var ServiceContainer */
    protected $sc;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
    }

    protected function _after()
    {
    }

    public function testServiceContainer()
    {
        $this->sc = new ServiceContainer();

        $this->specify('Checks if an empty service name throws an exception when registration.', function() {
            $this->sc->register('', function(){});
        }, ['throws' => 'InvalidArgumentException']);

        $this->specify('Checks if an invalid closure throws an exception.', function() {
            $this->sc->register('foo', 'bar');
        }, ['throws' => 'InvalidArgumentException']);

        $this->specify('Checks service registration.', function() {
            $this->assertInstanceOf('\Genetsis\core\ServiceContainer\Services\ServiceContainer', $this->sc->register('foo', function(){
                return 'bar';
            }));
            $this->assertTrue($this->sc->registered('foo'));
        });

        $this->specify('Checks if an empty service name throws an exception when instantiation.', function() {
            $this->sc->need('');
        }, ['throws' => 'InvalidArgumentException']);

        $this->specify('Checks service instantiation.', function() {
            $this->sc->register('foo', function(){
                return 'bar';
            });
            $this->assertFalse($this->sc->resolved('foo'));
            $this->assertEquals('bar', $this->sc->need('foo'));
            $this->assertTrue($this->sc->resolved('foo'));
        });

        $this->specify('Checks if a service is replaced.', function() {
            $this->sc->register('foo', function(){
                return 'bar';
            });
            $this->assertEquals('bar', $this->sc->need('foo'));
            $this->sc->register('foo', function(){
                return 'biz';
            });
            $this->assertEquals('biz', $this->sc->need('foo'));
        });

        $this->specify('Checks if a service is removed.', function() {
            $this->sc->register('foo', function(){
                return 'bar';
            });
            $this->assertTrue($this->sc->registered('foo'));
            $this->assertEquals('bar', $this->sc->need('foo'));
            $this->assertTrue($this->sc->resolved('foo'));
            $this->sc->remove('foo');
            $this->assertFalse($this->sc->registered('foo'));
            $this->assertFalse($this->sc->resolved('foo'));
        });
    }
}
