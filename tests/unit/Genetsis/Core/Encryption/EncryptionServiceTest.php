<?php
namespace Genetsis\UnitTest\Core\Encryption;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\Core\Encryption\Services\Encryption;

/**
 * @package Genetsis
 * @category UnitTest
 */
class EncryptionServiceTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testEncryptionService()
    {
        $this->specify('Checks encryption service.', function(){
            $encryption = new Encryption('my-key');
            $this->assertFalse($encryption->encode(''));
            $this->assertEquals('foo', $encryption->decode($encryption->encode('foo')));
            $this->assertFalse($encryption->decode(''));
            $this->assertEquals('', $encryption->safe_b64encode(''));
            $this->assertEquals('foo', $encryption->safe_b64decode($encryption->safe_b64encode('foo')));
        });
    }

}