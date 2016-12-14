<?php
namespace Genetsis\UnitTest\Core\Config\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Config\Beans\Log\File;
use Genetsis\core\Logger\Collections\LogLevels;

/**
 * @package Genetsis
 * @category UnitTest
 */
class FileBeanTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;
    /** @var File $log */
    protected $log;

    protected function _before()
    {
        $this->log = new File('', LogLevels::DEBUG);
    }

    protected function _after()
    {
    }

    public function testSettersAndGetters()
    {
        $this->specify('Checks setter and getter for parameter "log folder".', function(){
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Log\File', $this->log->setFolder('foo/bar'));
            $this->assertEquals('foo/bar', $this->log->getFolder());
            $this->log->setFolder('foo/bar/biz/');
            $this->assertEquals('foo/bar/biz', $this->log->getFolder());
        });

        $this->specify('Checks setter and getter for parameter "log level".', function(){
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Log\File', $this->log->setLevel(LogLevels::WARNING));
            $this->assertEquals(LogLevels::WARNING, $this->log->getLevel());
        });

        $this->specify('Checks if setting an invalid log level throws an exception.', function(){
            $this->log->setLevel('foo');
        }, ['throws' => 'InvalidArgumentException']);
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $this->specify('Checks constructor only with "log folder".', function(){
            $log = new File('foo/bar');
            $this->assertEquals('foo/bar', $log->getFolder());
            $this->assertEquals(LogLevels::DEBUG, $log->getLevel());
        });

        $this->specify('Checks constructor with all parameters".', function(){
            $log = new File('foo/bar', LogLevels::WARNING);
            $this->assertEquals('foo/bar', $log->getFolder());
            $this->assertEquals(LogLevels::WARNING, $log->getLevel());
        });

        $this->specify('Checks if constructor throws an exception with an invalid log level.', function(){
            new File('foo/bar', 'foo-level');
        }, ['throws' => 'InvalidArgumentException']);
    }
}
