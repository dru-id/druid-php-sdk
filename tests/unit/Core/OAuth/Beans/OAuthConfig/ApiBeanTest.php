<?php
namespace Genetsis\UnitTest\Core\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\OAuth\Beans\OAuthConfig\Api;

/**
 * @package Genetsis
 * @category UnitTest
 */
class ApiBeanTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var Api $api */
    private $api;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
    }

    protected function _after()
    {
    }

    public function testSettersAndGetters()
    {
        $this->api = new Api();

        $this->specify('Checks setter and getter for "name" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Api', $this->api->setName('my-name'));
            $this->assertEquals('my-name-2', $this->api->setName('my-name-2')->getName());
        });

        $this->specify('Checks setter and getter for "base url" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Api', $this->api->setBaseUrl('www.foo.com'));
            $this->assertEquals('www.bar.com', $this->api->setBaseUrl('www.bar.com')->getBaseUrl());
            $this->assertEquals('www.fuu.com', $this->api->setBaseUrl('www.fuu.com/')->getBaseUrl());
        });

        $this->specify('Checks setter and getter for "endpoints" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Api', $this->api->setEndpoints(['a' => '/aaa', 'b' => '/bbb']));
            $this->assertCount(2, $this->api->getEndpoints());
            $this->assertEquals('/bbb', $this->api->getEndpoint('b'));
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Api', $this->api->addEndpoint('c', '/ccc'));
            $this->assertCount(3, $this->api->getEndpoints());
            $this->assertEquals('/ccc', $this->api->getEndpoint('c'));
            $this->assertCount(3, $this->api->addEndpoint('b', '/bbb')->getEndpoints());
            $this->assertEquals('www.cucurucu.com/aaa', $this->api->setBaseUrl('www.cucurucu.com')->setEndpoints(['a' => 'aaa'])->getEndpoint('a', true));
        });
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $this->api = new Api([
            'name' => 'my-name',
            'base_url' => 'www.foo.com',
            'endpoints' => ['a' => '/aaa', 'b' => '/bbb']
        ]);

        $this->specify('Checks that constructor has assigned those variables properly.', function(){
            $this->assertEquals('my-name', $this->api->getName());
            $this->assertEquals('www.foo.com', $this->api->getBaseUrl());
            $this->assertEquals('/aaa', $this->api->getEndpoint('a'));
            $this->assertCount(2, $this->api->getEndpoints());
        });
    }
}