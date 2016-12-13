<?php
namespace Genetsis\UnitTest\OAuth\Beans\Services;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Http\Services\Http;
use Genetsis\core\Logger\Services\EmptyLogger;
use Genetsis\core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\core\OAuth\Services\OAuth;

/**
 * @package Genetsis
 * @category UnitTest
 */
class OAuthServiceTest extends Unit {
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

    public function testSettersAndGetters()
    {
        $this->specify('Checks setter and getter for "config" property.', function(){
            $oauth = new OAuth((new Config())->setClientId('foo'), new Http(new EmptyLogger()), new EmptyLogger());
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $oauth->getConfig());
            $this->assertEquals('foo', $oauth->getConfig()->getClientId());
            $this->assertInstanceOf('\Genetsis\core\OAuth\Services\OAuth', $oauth->setConfig((new Config())->setClientId('bar')));
            $this->assertEquals('bar', $oauth->getConfig()->getClientId());
        });
    }

}
