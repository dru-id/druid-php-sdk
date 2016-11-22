<?php namespace Genetsis\tests\OAuth\Beans\OAuthConfig;

use Genetsis\core\OAuth\Beans\OAuthConfig\Brand;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis
 * @category TestCase
 */
class BrandTest extends TestCase {

    public function testSettersAndGetters()
    {
        $brand = new Brand();

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Brand', $brand->setKey('my-key'));
        $this->assertEquals('my-key', $brand->getKey());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Brand', $brand->setName('my-name'));
        $this->assertEquals('my-name', $brand->getName());
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $brand = new Brand(['key' => 'my-key', 'name' => 'my-name']);

        $this->assertEquals('my-key', $brand->getKey());
        $this->assertEquals('my-name', $brand->getName());
    }

}
