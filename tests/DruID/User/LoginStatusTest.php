<?php namespace Genetsis\tests\OAuth\Beans;

use Genetsis\core\User\Beans\LoginStatus;
use Genetsis\core\User\Collections\LoginStatusTypes as LoginStatusTypesCollection;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis
 * @category TestCase
 */
class LoginStatusTest extends TestCase {

    public function testSettersAndGetters()
    {
        $ls = new LoginStatus();

        $this->assertInstanceOf('\Genetsis\core\User\Beans\LoginStatus', $ls->setCkusid('ckusid'));
        $this->assertEquals('ckusid', $ls->getCkusid());

        $this->assertInstanceOf('\Genetsis\core\User\Beans\LoginStatus', $ls->setOid('oid'));
        $this->assertEquals('oid', $ls->getOid());

        $this->assertInstanceOf('\Genetsis\core\User\Beans\LoginStatus', $ls->setConnectState(LoginStatusTypesCollection::CONNECTED));
        $this->assertEquals(LoginStatusTypesCollection::CONNECTED, $ls->getConnectState());
        $this->assertEquals(LoginStatusTypesCollection::UNKNOWN, $ls->setConnectState('foo')->getConnectState());
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $ls = new LoginStatus(['ckusid' => 'ckusid-1', 'oid' => 'oid-1', 'connect-state' => LoginStatusTypesCollection::CONNECTED]);

        $this->assertEquals('ckusid-1', $ls->getCkusid());
        $this->assertEquals('oid-1', $ls->getOid());
        $this->assertEquals(LoginStatusTypesCollection::CONNECTED, $ls->getConnectState());
    }

}
