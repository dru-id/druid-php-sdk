<?php
namespace Genetsis\UnitTest\Core\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Brand;

/**
 * @package Genetsis
 * @category UnitTest
 */
class BrandTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var Brand $brand */
    protected $brand;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->brand = new Brand();
    }

    protected function _after()
    {
    }

    public function testSetterAndGetterKey()
    {
        $this->specify('Checks setter and getter for "key" property.', function () {
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Brand', $this->brand->setKey('my-key'));
            $this->assertEquals('my-key', $this->brand->getKey());
        });
    }

    public function testSetterAndGetterName()
    {
        $this->specify('Checks setter and getter for "name" property.', function(){
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Brand', $this->brand->setName('my-name'));
            $this->assertEquals('my-name', $this->brand->getName());
        });
    }

    public function testConstructor()
    {
        $this->brand = new Brand(['key' => 'my-key', 'name' => 'my-name']);

        $this->specify('Checks constructor.', function() {
            $this->assertEquals('my-key', $this->brand->getKey());
            $this->assertEquals('my-name', $this->brand->getName());
        });
    }

}
