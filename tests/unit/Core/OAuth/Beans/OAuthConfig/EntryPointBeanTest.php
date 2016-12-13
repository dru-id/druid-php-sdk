<?php
namespace Genetsis\UnitTest\Core\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint;

/**
 * @package Genetsis
 * @category UnitTest
 */
class EntryPointBeanTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var EntryPoint $entry_point */
    private $entry_point;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
    }

    protected function _after()
    {
    }

    public function testSettersAndGetters()
    {
        $this->entry_point = new EntryPoint();

        $this->specify('Checks setter and getter for "id" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint', $this->entry_point->setId('my-id'));
            $this->assertEquals('my-id', $this->entry_point->getId());
        });

        $this->specify('Checks setter and getter for "promotion ID" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint', $this->entry_point->setPromotionId('my-promotion'));
            $this->assertEquals('my-promotion', $this->entry_point->getPromotionId());
        });

        $this->specify('Checks setter and getter for "prize" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint', $this->entry_point->setPrizes(['1' => 'prize-1', '2' => 'prize-2']));
            $this->assertCount(1, $this->entry_point->setPrizes(['1' => 'prize-1'])->getPrizes());
            $this->assertEquals('prize-1', $this->entry_point->getPrize('1'));
            $this->assertFalse($this->entry_point->getPrize('3'));
            $this->assertCount(2, $this->entry_point->addPrize('4', 'prize-4')->getPrizes());
            $this->assertEquals('prize-4', $this->entry_point->getPrize(4));
        });
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $this->entry_point = new EntryPoint([
            'id' => 'aaa',
            'promotion_id' => 'bbb',
            'prizes' => [ ['p1' => 'prize1'], ['p2' => 'prize2'], ['p3' => 'prize3'] ]
        ]);

        $this->specify('Checks that constructor has assigned those variables properly.', function(){
            $this->assertEquals('aaa', $this->entry_point->getId());
            $this->assertEquals('bbb', $this->entry_point->getPromotionId());
            $this->assertCount(3, $this->entry_point->getPrizes());
        });
    }

}
