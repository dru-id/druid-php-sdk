<?php namespace Genetsis\tests\OAuth\Beans\OAuthConfig;

use PHPUnit\Framework\TestCase;
use Genetsis\core\OAuth\Beans\OAuthConfig\Api;

/**
 * @package Genetsis
 * @category TestCase
 */
class ApiBeanTest extends TestCase
{

    public function testSettersAndGetters()
    {
        $obj = new Api();

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Api', $obj->setName('my-name'));
        $this->assertEquals('my-name-2', $obj->setName('my-name-2')->getName());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Api', $obj->setBaseUrl('www.foo.com'));
        $this->assertEquals('www.bar.com', $obj->setBaseUrl('www.bar.com')->getBaseUrl());
        $this->assertEquals('www.fuu.com', $obj->setBaseUrl('www.fuu.com/')->getBaseUrl());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Api', $obj->setEndpoints(['a' => '/aaa', 'b' => '/bbb']));
        $this->assertCount(2, $obj->getEndpoints());
        $this->assertEquals('/bbb', $obj->getEndpoint('b'));
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Api', $obj->addEndpoint('c', '/ccc'));
        $this->assertCount(3, $obj->getEndpoints());
        $this->assertEquals('/ccc', $obj->getEndpoint('c'));
        $this->assertCount(3, $obj->addEndpoint('b', '/bbb')->getEndpoints());
        $this->assertEquals('www.cucurucu.com/aaa', $obj->setBaseUrl('www.cucurucu.com')->setEndpoints(['a' => 'aaa'])->getEndpoint('a', true));
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $obj = new Api([
            'name' => 'my-name',
            'base_url' => 'www.foo.com',
            'endpoints' => ['a' => '/aaa', 'b' => '/bbb']
        ]);

        $this->assertEquals('my-name', $obj->getName());
        $this->assertEquals('www.foo.com', $obj->getBaseUrl());
        $this->assertEquals('/aaa', $obj->getEndpoint('a'));
        $this->assertCount(2, $obj->getEndpoints());
    }

}
