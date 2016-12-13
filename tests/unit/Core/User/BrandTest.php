<?php
namespace Genetsis\UnitTest\Core\User\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\User\Beans\Brand;

/**
 * @package Genetsis
 * @category UnitTest
 */
class BrandTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var Brand $brand */
    private $brand;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
    }

    protected function _after()
    {
    }

    public function testSettersAndGetters()
    {
        $this->brand = new Brand();

        $this->specify('Checks setter and getter for "key" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\User\Beans\Brand', $this->brand->setKey('my-key'));
            $this->assertEquals('my-key', $this->brand->getKey());
        });

        $this->specify('Checks setter and getter for "name" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\User\Beans\Brand', $this->brand->setName('my-name'));
            $this->assertEquals('my-name', $this->brand->getName());
        });
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $this->brand = new Brand(['key' => 'my-key', 'name' => 'my-name']);
        $this->assertEquals('my-key', $this->brand->getKey());
        $this->assertEquals('my-name', $this->brand->getName());
    }
}
