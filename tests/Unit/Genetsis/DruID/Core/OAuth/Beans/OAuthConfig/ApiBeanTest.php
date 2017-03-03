<?php
namespace Genetsis\DruID\UnitTest\Core\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Api;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class ApiBeanTest extends TestCase
{
    use Specify;

    /** @var Api $api */
    private $api;

    protected function setUp()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->api = new Api();
    }

    public function testSetterAndGetterName()
    {
        $this->specify('Checks setter and getter for "name" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Api', $this->api->setName('my-name'));
            $this->assertEquals('my-name-2', $this->api->setName('my-name-2')->getName());
        });
    }

    public function testSetterAndGetterBaseUrl()
    {
        $this->specify('Checks setter and getter for "base url" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Api', $this->api->setBaseUrl('www.foo.com'));
            $this->assertEquals('www.bar.com', $this->api->setBaseUrl('www.bar.com')->getBaseUrl());
            $this->assertEquals('www.fuu.com', $this->api->setBaseUrl('www.fuu.com/')->getBaseUrl());
        });
    }

    public function testSetterAndGetterEndpoints()
    {
        $this->specify('Checks setter and getter for "endpoints" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Api', $this->api->setEndpoints(['a' => '/aaa', 'b' => '/bbb']));
            $this->assertCount(2, $this->api->getEndpoints());
            $this->assertEquals('/bbb', $this->api->getEndpoint('b'));
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Api', $this->api->addEndpoint('c', '/ccc'));
            $this->assertCount(3, $this->api->getEndpoints());
            $this->assertEquals('/ccc', $this->api->getEndpoint('c'));
            $this->assertCount(3, $this->api->addEndpoint('b', '/bbb')->getEndpoints());
            $this->assertEquals('www.cucurucu.com/aaa', $this->api->setBaseUrl('www.cucurucu.com')->setEndpoints(['a' => 'aaa'])->getEndpoint('a', true));
        });
    }

    public function testConstructor()
    {
        $this->api = new Api([
            'name' => 'my-name',
            'base_url' => 'www.foo.com',
            'endpoints' => ['a' => '/aaa', 'b' => '/bbb']
        ]);

        $this->specify('Checks constructor.', function(){
            $this->assertEquals('my-name', $this->api->getName());
            $this->assertEquals('www.foo.com', $this->api->getBaseUrl());
            $this->assertEquals('/aaa', $this->api->getEndpoint('a'));
            $this->assertCount(2, $this->api->getEndpoints());
        });
    }
}