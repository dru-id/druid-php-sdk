<?php
namespace Genetsis\DruID\UnitTest\Core\Config\Beans\Cache;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\DruID\Core\Config\Beans\Cache\File;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class FileBeanTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;
    /** @var File $cache */
    protected $cache;

    protected function _before()
    {
        $this->cache = new File('default', 'foo/bar');
    }

    protected function _after()
    {
    }

    public function testSetterAndGetterGroup()
    {
        $this->specify('Checks setter and getter for parameter "group".', function(){
            $this->assertInstanceOf('\Genetsis\DruID\Core\Config\Beans\Cache\File', $this->cache->setGroup('foo'));
            $this->assertEquals('foo', $this->cache->getGroup());
        });
    }

    public function testSetterAndGetterFolder()
    {
        $this->specify('Checks setter and getter for parameter "folder".', function(){
            $this->assertInstanceOf('\Genetsis\DruID\Core\Config\Beans\Cache\File', $this->cache->setFolder('foo/bar/biz'));
            $this->assertEquals('foo/bar/biz', $this->cache->getFolder());
            $this->cache->setFolder('foo/bar/biz/');
            $this->assertEquals('foo/bar/biz', $this->cache->getFolder());
        });
    }

    public function testConstructor()
    {
        $this->specify('Checks constructor.', function () {
            $log = new File('foo', 'foo/bar');
            $this->assertEquals('foo', $log->getGroup());
            $this->assertEquals('foo/bar', $log->getFolder());
        });
    }
}