<?php namespace Genetsis\tests\ServiceContainer;

use Genetsis\core\Encryption\Services\Encryption;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis
 * @category TestCase
 */
class EncryptionServiceTest extends TestCase
{
    public function testService()
    {
        $encryption = new Encryption('my-key');
        $this->assertFalse($encryption->encode(''));
        $this->assertEquals('foo', $encryption->decode($encryption->encode('foo')));
        $this->assertEquals('', $encryption->safe_b64encode(''));
        $this->assertEquals('foo', $encryption->safe_b64decode($encryption->safe_b64encode('foo')));
    }

}