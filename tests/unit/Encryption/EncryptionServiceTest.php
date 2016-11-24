<?php
namespace Genetsis\UnitTest\Encryption;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Encryption\Services\Encryption;

/**
 * @package Genetsis
 * @category UnitTest
 */
class EncryptionServiceTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
    }

    protected function _after()
    {
    }

    public function testService()
    {
        $encryption = new Encryption('my-key');
        $this->assertFalse($encryption->encode(''));
        $this->assertEquals('foo', $encryption->decode($encryption->encode('foo')));
        $this->assertEquals('', $encryption->safe_b64encode(''));
        $this->assertEquals('foo', $encryption->safe_b64decode($encryption->safe_b64encode('foo')));
    }

}