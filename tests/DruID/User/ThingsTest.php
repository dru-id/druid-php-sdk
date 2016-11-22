<?php namespace Genetsis\tests\OAuth\Beans;

use Genetsis\core\OAuth\Beans\AccessToken;
use Genetsis\core\OAuth\Beans\ClientToken;
use Genetsis\core\OAuth\Beans\RefreshToken;
use Genetsis\core\User\Beans\LoginStatus;
use Genetsis\core\User\Beans\Things;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis
 * @category TestCase
 */
class ThingsTest extends TestCase {

    public function testSettersAndGetters()
    {
        $things = new Things();

        $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $things->setClientToken(new ClientToken('client-token')));
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\ClientToken', $things->getClientToken());
        $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $things->setClientToken(null));
        $this->assertNull($things->getClientToken());

        $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $things->setAccessToken(new AccessToken('access-token')));
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\AccessToken', $things->getAccessToken());
        $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $things->setAccessToken(null));
        $this->assertNull($things->getAccessToken());

        $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $things->setRefreshToken(new RefreshToken('refresh-token')));
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\RefreshToken', $things->getRefreshToken());
        $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $things->setRefreshToken(null));
        $this->assertNull($things->getRefreshToken());

        $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $things->setLoginStatus(new LoginStatus()));
        $this->assertInstanceOf('\Genetsis\core\User\Beans\LoginStatus', $things->getLoginStatus());
        $this->assertInstanceOf('\Genetsis\core\User\Beans\Things', $things->setLoginStatus(null));
        $this->assertNull($things->getLoginStatus());

    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $things = new Things([
            'client_token' => new ClientToken('ctk'),
            'access_token' => new AccessToken('atk'),
            'refresh_token' => new RefreshToken('rtk'),
            'login_status' => new LoginStatus()
        ]);

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\ClientToken', $things->getClientToken());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\AccessToken', $things->getAccessToken());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\RefreshToken', $things->getRefreshToken());
        $this->assertInstanceOf('\Genetsis\core\User\Beans\LoginStatus', $things->getLoginStatus());
    }

}
