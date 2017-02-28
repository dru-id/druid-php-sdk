<?php
namespace Genetsis\DruID\UnitTest\Core\Config\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\DruID\Core\Config\Beans\Cache\File as FileCacheConfig;
use Genetsis\DruID\Core\Config\Beans\Cache\Memcached as MemcachedConfig;
use Genetsis\DruID\Core\Config\Beans\Config;

/**
 * @package Genetsis\DruID
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
            $this->assertInstanceOf('\Genetsis\DruID\Core\Config\Beans\Config', $this->config->setServerName('foo'));
            $this->assertEquals('foo', $this->config->getServerName());
        });
    }

    public function testConstructor()
    {
        $this->specify('Checks constructor.', function(){
            $config = new Config('foo-bar');
            $this->assertEquals('foo-bar', $config->getServerName());
        });
    }
}
