<?php
namespace Genetsis\DruID\UnitTest\Core\User\Beans;

use Codeception\Specify;
use Genetsis\DruID\Core\User\Beans\Brand;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class BrandTest extends TestCase
{
    use Specify;

    /** @var Brand $brand */
    private $brand;

    protected function setUp()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->brand = new Brand();
    }

    public function testSetterAndGetterKey()
    {
        $this->specify('Checks setter and getter for "key" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\User\Beans\Brand', $this->brand->setKey('my-key'));
            $this->assertEquals('my-key', $this->brand->getKey());
        });
    }

    public function testSetterAndGetterName()
    {
        $this->specify('Checks setter and getter for "name" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\User\Beans\Brand', $this->brand->setName('my-name'));
            $this->assertEquals('my-name', $this->brand->getName());
        });
    }

    public function testConstructor()
    {
        $this->brand = new Brand(['key' => 'my-key', 'name' => 'my-name']);

        $this->specify('Checks constructor.', function () {
            $this->assertEquals('my-key', $this->brand->getKey());
            $this->assertEquals('my-name', $this->brand->getName());
        });
    }
}
