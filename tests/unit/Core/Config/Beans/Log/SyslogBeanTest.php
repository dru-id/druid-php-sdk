<?php
namespace Genetsis\UnitTest\Core\Config\Beans\Log;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Config\Beans\Log\Syslog;
use Genetsis\core\Logger\Collections\LogLevels;

/**
 * @package Genetsis
 * @category UnitTest
 */
class SyslogBeanTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;
    /** @var Syslog $log */
    protected $log;

    protected function _before()
    {
        $this->log = new Syslog('default', LogLevels::DEBUG);
    }

    protected function _after()
    {
    }

    public function testSetterAndGetterGroup()
    {
        $this->specify('Checks setter and getter for parameter "group".', function(){
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Log\Syslog', $this->log->setGroup('foo'));
            $this->assertEquals('foo', $this->log->getGroup());
        });
    }

    public function testSetterAndGetterLogLevel()
    {
        $this->specify('Checks setter and getter for parameter "log level".', function(){
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Log\Syslog', $this->log->setLevel(LogLevels::WARNING));
            $this->assertEquals(LogLevels::WARNING, $this->log->getLevel());
        });

        $this->specify('Checks if setting an invalid log level throws an exception.', function(){
            $this->log->setLevel('foo');
        }, ['throws' => 'InvalidArgumentException']);
    }

    public function testConstructor()
    {
        $this->specify('Checks constructor.', function(){
            $log = new Syslog('foo', LogLevels::WARNING);
            $this->assertEquals('foo', $log->getGroup());
            $this->assertEquals(LogLevels::WARNING, $log->getLevel());
        });

        $this->specify('Checks if constructor throws an exception with an invalid log level.', function(){
            new Syslog('default', 'foo-level');
        }, ['throws' => 'InvalidArgumentException']);
    }
}
