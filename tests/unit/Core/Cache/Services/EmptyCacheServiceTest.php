<?php
namespace Genetsis\UnitTest\Core\Cache\Services;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Cache\Services\EmptyCache;

/**
 * @package Genetsis
 * @category UnitTest
 */
class EmptyCacheServiceTest extends Unit {
    use Specify;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testEmptyCacheService()
    {
        $this->specify('Checks methods.', function(){
            $cache = new EmptyCache();
            $this->assertFalse($cache->set('foo'));
            $this->assertEquals('bar', $cache->get('foo', 'bar'));
            $this->assertFalse($cache->has('foo'));
            $this->assertTrue($cache->delete('foo'));
            $this->assertTrue($cache->clean());
        });
    }
}
