<?php
namespace Genetsis\UnitTest\User\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\User\Beans\LoginStatus;
use Genetsis\core\User\Collections\LoginStatusTypes as LoginStatusTypesCollection;

/**
 * @package Genetsis
 * @category UnitTest
 */
class LoginStatusTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var LoginStatus $login_status */
    private $login_status;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
    }

    protected function _after()
    {
    }

    public function testSettersAndGetters()
    {
        $this->login_status = new LoginStatus();

        $this->specify('Checks setter and getter for "ckusid" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\User\Beans\LoginStatus', $this->login_status->setCkusid('ckusid'));
            $this->assertEquals('ckusid', $this->login_status->getCkusid());
        });

        $this->specify('Checks setter and getter for "oid" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\User\Beans\LoginStatus', $this->login_status->setOid('oid'));
            $this->assertEquals('oid', $this->login_status->getOid());
        });

        $this->specify('Checks setter and getter for "connect state" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\User\Beans\LoginStatus', $this->login_status->setConnectState(LoginStatusTypesCollection::CONNECTED));
            $this->assertEquals(LoginStatusTypesCollection::CONNECTED, $this->login_status->getConnectState());
            $this->assertEquals(LoginStatusTypesCollection::UNKNOWN, $this->login_status->setConnectState('foo')->getConnectState());
        });
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $this->login_status = new LoginStatus(['ckusid' => 'ckusid-1', 'oid' => 'oid-1', 'connect-state' => LoginStatusTypesCollection::CONNECTED]);

        $this->assertEquals('ckusid-1', $this->login_status->getCkusid());
        $this->assertEquals('oid-1', $this->login_status->getOid());
        $this->assertEquals(LoginStatusTypesCollection::CONNECTED, $this->login_status->getConnectState());
    }

}
