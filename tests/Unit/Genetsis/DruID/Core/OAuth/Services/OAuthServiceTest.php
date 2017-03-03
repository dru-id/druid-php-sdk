<?php
namespace Genetsis\DruID\UnitTest\OAuth\Beans;

use Codeception\Specify;
use Genetsis\DruID\Core\Http\Cookies;
use Genetsis\DruID\Core\Http\Http;
use Genetsis\DruID\Core\Logger\VoidLogger;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\DruID\Core\OAuth\OAuth;
use Genetsis\DruID\DruID;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class OAuthServiceTest extends TestCase
{
    use Specify;

    /** @var Prophet $prophet */
    protected $prophet;

    protected function setUp()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->prophet = new Prophet();
    }

    public function testSettersAndGetters()
    {
        $this->specify('Checks setter and getter for "config" property.', function(){
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), (new Config())->setClientId('foo'), new Http(new Client(), new VoidLogger()), new Cookies(), new VoidLogger());
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Config', $oauth->getConfig());
            $this->assertEquals('foo', $oauth->getConfig()->getClientId());
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\OAuth', $oauth->setConfig((new Config())->setClientId('bar')));
            $this->assertEquals('bar', $oauth->getConfig()->getClientId());
        });
    }

}
