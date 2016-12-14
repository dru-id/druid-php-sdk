<?php
namespace Genetsis\UnitTest\Core\Config\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Config\Beans\Cache\File as FileCache;
use Genetsis\core\Config\Beans\Cache\Memcached;
use Genetsis\core\Config\Beans\Config;
use Genetsis\core\Config\Beans\Log\File as FileLog;
use Genetsis\core\Config\Beans\Log\Syslog;
use Genetsis\core\Logger\Collections\LogLevels;

/**
 * @package Genetsis
 * @category UnitTest
 */
class ConfigBeanTest extends Unit {
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

    public function testSettersAndGetters()
    {
        $this->specify('Checks setter and getter for parameter "server name".', function(){
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Config', $this->config->setServerName('foo'));
            $this->assertEquals('foo', $this->config->getServerName());
        });

        $this->specify('Checks setter and getter for parameter "log".', function(){
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Config', $this->config->setLog(new FileLog('', LogLevels::DEBUG)));
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Log\File', $this->config->getLog());
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Config', $this->config->setLog(new Syslog(LogLevels::DEBUG)));
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Log\Syslog', $this->config->getLog());
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Config', $this->config->setLog(null));
            $this->assertNull($this->config->getLog());
        });

        $this->specify('Checks setter and getter for parameter "cache".', function(){
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Config', $this->config->setCache(new FileCache('')));
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Cache\File', $this->config->getCache());
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Config', $this->config->setCache(new Memcached('', '')));
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Cache\Memcached', $this->config->getCache());
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Config', $this->config->setCache(null));
            $this->assertNull($this->config->getCache());
        });
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $this->specify('Checks constructor only with "server name".', function(){
            $config = new Config('foo-bar');
            $this->assertEquals('foo-bar', $config->getServerName());
        });

        $this->specify('Checks constructor only with "log".', function(){
            $config = new Config('foo-bar', new FileLog('', LogLevels::DEBUG));
            $this->assertEquals('foo-bar', $config->getServerName());
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Log\File', $config->getLog());
        });

        $this->specify('Checks constructor only with "cache".', function(){
            $config = new Config('foo-bar', null, new FileCache(''));
            $this->assertEquals('foo-bar', $config->getServerName());
            $this->assertNull($config->getLog());
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Cache\File', $config->getCache());
        });

        $this->specify('Checks constructor with all parameters.', function(){
            $config = new Config('foo-bar', new Syslog(LogLevels::DEBUG), new Memcached('', ''));
            $this->assertEquals('foo-bar', $config->getServerName());
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Log\Syslog', $config->getLog());
            $this->assertInstanceOf('\Genetsis\core\Config\Beans\Cache\Memcached', $config->getCache());
        });
    }
}
