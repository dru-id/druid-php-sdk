<?php
namespace Genetsis\DruID\UnitTest\Core\User\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\DruID\Core\OAuth\Beans\AccessToken;
use Genetsis\DruID\Core\OAuth\Beans\ClientToken;
use Genetsis\DruID\Core\OAuth\Beans\RefreshToken;
use Genetsis\DruID\Core\User\Beans\LoginStatus;
use Genetsis\DruID\Core\User\Beans\Things;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class ThingsTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var Things $things */
    private $things;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->things = new Things();
    }

    protected function _after()
    {
    }

    public function testSetterAndGetterClientToken()
    {
        $this->specify('Checks setter and getter for "client_token" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\User\Beans\Things', $this->things->setClientToken(new ClientToken('client-token')));
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\ClientToken', $this->things->getClientToken());
            $this->assertInstanceOf('\Genetsis\DruID\Core\User\Beans\Things', $this->things->setClientToken(null));
            $this->assertNull($this->things->getClientToken());
        });
    }

    public function testSetterAndGetterAccessToken()
    {
        $this->specify('Checks setter and getter for "access_token" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\User\Beans\Things', $this->things->setAccessToken(new AccessToken('access-token')));
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\AccessToken', $this->things->getAccessToken());
            $this->assertInstanceOf('\Genetsis\DruID\Core\User\Beans\Things', $this->things->setAccessToken(null));
            $this->assertNull($this->things->getAccessToken());
        });
    }

    public function testSetterAndGetterRefreshToken()
    {
        $this->specify('Checks setter and getter for "refresh_token" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\User\Beans\Things', $this->things->setRefreshToken(new RefreshToken('refresh-token')));
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\RefreshToken', $this->things->getRefreshToken());
            $this->assertInstanceOf('\Genetsis\DruID\Core\User\Beans\Things', $this->things->setRefreshToken(null));
            $this->assertNull($this->things->getRefreshToken());
        });
    }

    public function testSetterAndGetterLoginStatus()
    {
        $this->specify('Checks setter and getter for "login_status" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\User\Beans\Things', $this->things->setLoginStatus(new LoginStatus()));
            $this->assertInstanceOf('\Genetsis\DruID\Core\User\Beans\LoginStatus', $this->things->getLoginStatus());
            $this->assertInstanceOf('\Genetsis\DruID\Core\User\Beans\Things', $this->things->setLoginStatus(null));
            $this->assertNull($this->things->getLoginStatus());
        });
    }

    public function testConstructor()
    {
        $this->things = new Things([
            'client_token' => new ClientToken('ctk'),
            'access_token' => new AccessToken('atk'),
            'refresh_token' => new RefreshToken('rtk'),
            'login_status' => new LoginStatus()
        ]);

        $this->specify('Checks constructor.', function () {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\ClientToken', $this->things->getClientToken());
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\AccessToken', $this->things->getAccessToken());
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\RefreshToken', $this->things->getRefreshToken());
            $this->assertInstanceOf('\Genetsis\DruID\Core\User\Beans\LoginStatus', $this->things->getLoginStatus());
        });
    }
}
