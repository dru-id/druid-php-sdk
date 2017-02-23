<?php
namespace Genetsis\UnitTest\Core\User\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\Core\User\Beans\LoginStatus;
use Genetsis\Core\User\Collections\LoginStatusTypes as LoginStatusTypesCollection;

/**
 * @package Genetsis
 * @category UnitTest
 */
class LoginStatusTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var LoginStatus $login_status */
    private $login_status;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->login_status = new LoginStatus();
    }

    protected function _after()
    {
    }

    public function testSetterAndGetterCkusid()
    {
        $this->specify('Checks setter and getter for "ckusid" property.', function() {
            $this->assertInstanceOf('\Genetsis\Core\User\Beans\LoginStatus', $this->login_status->setCkusid('ckusid'));
            $this->assertEquals('ckusid', $this->login_status->getCkusid());
        });
    }

    public function testSetterAndGetterOid()
    {
        $this->specify('Checks setter and getter for "oid" property.', function() {
            $this->assertInstanceOf('\Genetsis\Core\User\Beans\LoginStatus', $this->login_status->setOid('oid'));
            $this->assertEquals('oid', $this->login_status->getOid());
        });
    }

    public function testSetterAndGetterConnectState()
    {
        $this->specify('Checks setter and getter for "connect state" property.', function() {
            $this->assertInstanceOf('\Genetsis\Core\User\Beans\LoginStatus', $this->login_status->setConnectState(LoginStatusTypesCollection::CONNECTED));
            $this->assertEquals(LoginStatusTypesCollection::CONNECTED, $this->login_status->getConnectState());
            $this->assertEquals(LoginStatusTypesCollection::UNKNOWN, $this->login_status->setConnectState('foo')->getConnectState());
        });
    }

    public function testConstructor()
    {
        $this->login_status = new LoginStatus(['ckusid' => 'ckusid-1', 'oid' => 'oid-1', 'connect-state' => LoginStatusTypesCollection::CONNECTED]);

        $this->specify('Checks constructor.', function () {
            $this->assertEquals('ckusid-1', $this->login_status->getCkusid());
            $this->assertEquals('oid-1', $this->login_status->getOid());
            $this->assertEquals(LoginStatusTypesCollection::CONNECTED, $this->login_status->getConnectState());
        });
    }
}
