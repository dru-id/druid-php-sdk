<?php
namespace Genetsis\UnitTest\OAuth\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\Core\Http\Cookies;
use Genetsis\Core\Http\Http;
use Genetsis\Core\Logger\VoidLogger;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\Core\OAuth\OAuth;
use Genetsis\DruID;
use GuzzleHttp\Client;
use Prophecy\Prophet;

/**
 * @package Genetsis
 * @category UnitTest
 */
class OAuthServiceTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;
    /** @var Prophet $prophet */
    protected $prophet;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->prophet = new Prophet();
    }

    protected function _after()
    {
    }

    public function testSettersAndGetters()
    {
        $this->specify('Checks setter and getter for "config" property.', function(){
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), (new Config())->setClientId('foo'), new Http(new Client(), new VoidLogger()), new Cookies(), new VoidLogger());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $oauth->getConfig());
            $this->assertEquals('foo', $oauth->getConfig()->getClientId());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\OAuth', $oauth->setConfig((new Config())->setClientId('bar')));
            $this->assertEquals('bar', $oauth->getConfig()->getClientId());
        });
    }

}