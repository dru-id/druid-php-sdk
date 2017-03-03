<?php
namespace Genetsis\DruID\UnitTest\Core\Config\Beans;

use Codeception\Specify;
use Genetsis\DruID\Core\Config\Beans\Config;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class ConfigBeanTest extends TestCase
{
    use Specify;

    /** @var \UnitTester */
    protected $tester;
    /** @var Config $config */
    protected $config;

    protected function setUp()
    {
        $this->config = new Config('');
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
