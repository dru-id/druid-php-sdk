<?php
namespace Genetsis\DruID\UnitTest\Core\User\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\DruID\Core\User\Beans\Brand;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class BrandTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var Brand $brand */
    private $brand;

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
