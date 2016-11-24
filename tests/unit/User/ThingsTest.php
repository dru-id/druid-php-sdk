<?php
namespace Genetsis\UnitTest\User\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\OAuth\Beans\AccessToken;
use Genetsis\core\OAuth\Beans\ClientToken;
use Genetsis\core\OAuth\Beans\RefreshToken;
use Genetsis\core\User\Beans\LoginStatus;
use Genetsis\core\User\Beans\Things;

/**
 * @package Genetsis
 * @category UnitTest
 */
class ThingsTest extends  Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var Things $things */
    private $things;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
    }

    protected function _after()
    {
    }

    public function testSettersAndGetters()
    {
        $this->things = new Things();

        $this->specify('Checks setter and getter for "client_token" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $this->things->setClientToken(new ClientToken('client-token')));
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\ClientToken', $this->things->getClientToken());
            $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $this->things->setClientToken(null));
            $this->assertNull($this->things->getClientToken());
        });

        $this->specify('Checks setter and getter for "access_token" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $this->things->setAccessToken(new AccessToken('access-token')));
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\AccessToken', $this->things->getAccessToken());
            $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $this->things->setAccessToken(null));
            $this->assertNull($this->things->getAccessToken());
        });

        $this->specify('Checks setter and getter for "refresh_token" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $this->things->setRefreshToken(new RefreshToken('refresh-token')));
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\RefreshToken', $this->things->getRefreshToken());
            $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $this->things->setRefreshToken(null));
            $this->assertNull($this->things->getRefreshToken());
        });

        $this->specify('Checks setter and getter for "login_status" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $this->things->setLoginStatus(new LoginStatus()));
            $this->assertInstanceOf('\Genetsis\core\User\Beans\LoginStatus', $this->things->getLoginStatus());
            $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $this->things->setLoginStatus(null));
            $this->assertNull($this->things->getLoginStatus());
        });
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $this->things = new Things([
            'client_token' => new ClientToken('ctk'),
            'access_token' => new AccessToken('atk'),
            'refresh_token' => new RefreshToken('rtk'),
            'login_status' => new LoginStatus()
        ]);

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\ClientToken', $this->things->getClientToken());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\AccessToken', $this->things->getAccessToken());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\RefreshToken', $this->things->getRefreshToken());
        $this->assertInstanceOf('\Genetsis\core\User\Beans\LoginStatus', $this->things->getLoginStatus());
    }

}
