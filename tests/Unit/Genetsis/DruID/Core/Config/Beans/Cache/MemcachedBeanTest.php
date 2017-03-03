<?php
namespace Genetsis\DruID\UnitTest\Core\Config\Beans\Cache;

use Codeception\Specify;
use Genetsis\DruID\Core\Config\Beans\Cache\Memcached;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class MemcachedBeanTest extends TestCase
{
    use Specify;

    /** @var \UnitTester */
    protected $tester;
    /** @var Memcached $cache */
    protected $cache;

    protected function setUp()
    {
        $this->cache = new Memcached('default', 'foo.com', '123');
    }

    public function testSetterAndGetterGroup()
    {
        $this->specify('Checks setter and getter for parameter "group".', function(){
            $this->assertInstanceOf('\Genetsis\DruID\Core\Config\Beans\Cache\Memcached', $this->cache->setGroup('foo'));
            $this->assertEquals('foo', $this->cache->getGroup());
        });
    }

    public function testSetterAndGetterHost()
    {
        $this->specify('Checks setter and getter for parameter "host".', function(){
            $this->assertInstanceOf('\Genetsis\DruID\Core\Config\Beans\Cache\Memcached', $this->cache->setHost('bar.com'));
            $this->assertEquals('bar.com', $this->cache->getHost());
        });
    }

    public function testSetterAndGetterPort()
    {
        $this->specify('Checks setter and getter for parameter "port".', function(){
            $this->assertInstanceOf('\Genetsis\DruID\Core\Config\Beans\Cache\Memcached', $this->cache->setPort('456'));
            $this->assertEquals('456', $this->cache->getPort());
        });
    }

    public function testConstructor()
    {
        $this->specify('Checks constructor.', function () {
            $log = new Memcached('foo', 'bar.com', '456');
            $this->assertEquals('foo', $log->getGroup());
            $this->assertEquals('bar.com', $log->getHost());
            $this->assertEquals('456', $log->getPort());
        });
    }
}
