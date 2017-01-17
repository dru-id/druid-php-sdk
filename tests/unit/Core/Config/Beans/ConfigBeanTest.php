<?php
namespace Genetsis\UnitTest\Core\Config\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Config\Beans\Cache\File as FileCacheConfig;
use Genetsis\core\Config\Beans\Cache\Memcached as MemcachedConfig;
use Genetsis\core\Config\Beans\Config;

/**
 * @package Genetsis
 * @category UnitTest
 */
class ConfigBeanTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;
    /** @var Config $config */
    protected $config;

    protected function _before()
    {
        $this->config = new Config('');
    }

    protected function _after()
    {
    }

    public function testSetterAndGetterServerName()
    {
        $this->specify('Checks setter and getter for parameter "server name".', function(){
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Config', $this->config->setServerName('foo'));
            $this->assertEquals('foo', $this->config->getServerName());
        });
    }

    public function testSetterAndGetterCache()
    {
        $this->specify('Checks setter and getter for parameter "cache".', function(){
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Config', $this->config->setCache(new FileCacheConfig('default', '')));
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Cache\File', $this->config->getCache());
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Config', $this->config->setCache(new MemcachedConfig('default', '', '')));
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Cache\Memcached', $this->config->getCache());
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Config', $this->config->setCache(null));
            $this->assertNull($this->config->getCache());
        });

        $this->specify('Checks if setting an invalid log throws an exception.', function(){
            $this->config->setCache(new \stdClass());
        }, ['throws' => \InvalidArgumentException::class]);
    }

    public function testConstructor()
    {
        $this->specify('Checks constructor only with "server name".', function(){
            $config = new Config('foo-bar');
            $this->assertEquals('foo-bar', $config->getServerName());
        });

        $this->specify('Checks constructor with all parameters.', function(){
            $config = new Config('foo-bar', new MemcachedConfig('default', '', ''));
            $this->assertEquals('foo-bar', $config->getServerName());
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Cache\Memcached', $config->getCache());
        });
    }
}
