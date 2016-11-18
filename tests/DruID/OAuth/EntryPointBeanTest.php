<?php namespace Genetsis\tests\OAuth;

use PHPUnit\Framework\TestCase;
use Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint;

class EntryPointBeanTest extends TestCase
{

    public function testSettersAndGetters()
    {
        $obj = new EntryPoint();

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint', $obj->setId('my-id'));
        $this->assertEquals('my-id-2', $obj->setId('my-id-2')->getId());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint', $obj->setPromotionId('my-promotion'));
        $this->assertEquals('my-promotion-2', $obj->setPromotionId('my-promotion-2')->getPromotionId());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint', $obj->setPrizes(['1' => 'prize-1', '2' => 'prize-2']));
        $this->assertCount(1, $obj->setPrizes(['1' => 'prize-1'])->getPrizes());
        $this->assertEquals('prize-1', $obj->getPrize('1'));
        $this->assertFalse($obj->getPrize('3'));
        $this->assertCount(2, $obj->addPrize('4', 'prize-4')->getPrizes());
        $this->assertEquals('prize-4', $obj->getPrize(4));
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $obj = new EntryPoint([
            'id' => 'aaa',
            'promotion_id' => 'bbb',
            'prizes' => [ ['p1' => 'prize1'], ['p2' => 'prize2'], ['p3' => 'prize3'] ]
        ]);

        $this->assertEquals('aaa', $obj->getId());
        $this->assertEquals('bbb', $obj->getPromotionId());
        $this->assertCount(3, $obj->getPrizes());
    }

}
