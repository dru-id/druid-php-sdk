<?php namespace Genetsis\tests\OAuth\Beans;

use PHPUnit\Framework\TestCase;
use Genetsis\core\OAuth\Beans\OAuthConfig\RedirectUrl;

class RedirectUrlBeanTest extends TestCase
{

    public function testSettersAndGetters()
    {
        $obj = new RedirectUrl();

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\RedirectUrl', $obj->setType('type-1'));
        $this->assertEquals('type-2', $obj->setType('type-2')->getType());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\RedirectUrl', $obj->setUrl('http://www.foo.com'));
        $this->assertEquals('http://www.bar.com', $obj->setUrl('http://www.bar.com')->getUrl());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\RedirectUrl', $obj->setIsDefault(true));
        $this->assertTrue($obj->getIsDefault());
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $obj = new RedirectUrl([
            'type' => 'type-1',
            'url' => 'http://www.foo.com',
            'is_default' => true
        ]);

        $this->assertEquals('type-1', $obj->getType());
        $this->assertEquals('http://www.foo.com', $obj->getUrl());
        $this->assertTrue($obj->getIsDefault());
    }

}
