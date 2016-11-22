<?php namespace Genetsis\tests\OAuth\Beans;

use Genetsis\core\User\Beans\Brand;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis
 * @category TestCase
 */
class BrandTest extends TestCase {

    public function testSettersAndGetters()
    {
        $brand = new Brand();

        $this->assertInstanceOf('\Genetsis\core\User\Beans\Brand', $brand->setKey('my-key'));
        $this->assertEquals('my-key', $brand->getKey());

        $this->assertInstanceOf('\Genetsis\core\User\Beans\Brand', $brand->setName('my-name'));
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
