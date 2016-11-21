<?php namespace Genetsis\tests\OAuth\Beans;

use PHPUnit\Framework\TestCase;
use Genetsis\core\OAuth\Beans\OAuthConfig\EndPoint;

class EndPointBeanTest extends TestCase
{

    public function testSettersAndGetters()
    {
        $obj = new EndPoint();

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EndPoint', $obj->setId('my-id'));
        $this->assertEquals('my-id-2', $obj->setId('my-id-2')->getId());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EndPoint', $obj->setUrl('http://www.foo.com'));
        $this->assertEquals('http://www.bar.com', $obj->setUrl('http://www.bar.com')->getUrl());

        $this->assertEquals('http://www.bar.com', $obj);
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $obj = new EndPoint([
            'id' => 'my-id',
            'url' => 'http://www.foo.com'
        ]);

        $this->assertEquals('my-id', $obj->getId());
        $this->assertEquals('http://www.foo.com', $obj->getUrl());
    }

}
