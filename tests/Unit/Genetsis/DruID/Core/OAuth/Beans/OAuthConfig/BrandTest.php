<?php
namespace Genetsis\DruID\UnitTest\Core\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Brand;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class BrandTest extends TestCase
{
    use Specify;

    /** @var Brand $brand */
    protected $brand;

    protected function setUp()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->brand = new Brand();
    }

    public function testSetterAndGetterKey()
    {
        $this->specify('Checks setter and getter for "key" property.', function () {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Brand', $this->brand->setKey('my-key'));
            $this->assertEquals('my-key', $this->brand->getKey());
        });
    }

    public function testSetterAndGetterName()
    {
        $this->specify('Checks setter and getter for "name" property.', function(){
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Brand', $this->brand->setName('my-name'));
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
