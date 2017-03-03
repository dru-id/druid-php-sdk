<?php
namespace Genetsis\DruID\UnitTest\Core\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\EntryPoint;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class EntryPointBeanTest extends TestCase
{
    use Specify;

    /** @var EntryPoint $entry_point */
    private $entry_point;

    protected function setUp()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->entry_point = new EntryPoint();
    }

    public function testSetterAndGetterId()
    {
        $this->specify('Checks setter and getter for "id" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\EntryPoint', $this->entry_point->setId('my-id'));
            $this->assertEquals('my-id', $this->entry_point->getId());
        });
    }

    public function testSetterAndGetterPromotionId()
    {
        $this->specify('Checks setter and getter for "promotion ID" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\EntryPoint', $this->entry_point->setPromotionId('my-promotion'));
            $this->assertEquals('my-promotion', $this->entry_point->getPromotionId());
        });
    }

    public function testSetterAndGetterPrize()
    {
        $this->specify('Checks setter and getter for "prize" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\EntryPoint', $this->entry_point->setPrizes(['1' => 'prize-1', '2' => 'prize-2']));
            $this->assertCount(1, $this->entry_point->setPrizes(['1' => 'prize-1'])->getPrizes());
            $this->assertEquals('prize-1', $this->entry_point->getPrize('1'));
            $this->assertFalse($this->entry_point->getPrize('3'));
            $this->assertCount(2, $this->entry_point->addPrize('4', 'prize-4')->getPrizes());
            $this->assertEquals('prize-4', $this->entry_point->getPrize(4));
        });
    }

    public function testConstructor()
    {
        $this->entry_point = new EntryPoint([
            'id' => 'aaa',
            'promotion_id' => 'bbb',
            'prizes' => [ ['p1' => 'prize1'], ['p2' => 'prize2'], ['p3' => 'prize3'] ]
        ]);

        $this->specify('Checks constructor.', function(){
            $this->assertEquals('aaa', $this->entry_point->getId());
            $this->assertEquals('bbb', $this->entry_point->getPromotionId());
            $this->assertCount(3, $this->entry_point->getPrizes());
        });
    }
}
