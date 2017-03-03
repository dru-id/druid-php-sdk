<?php
namespace Genetsis\DruID\UnitTest\Core\Encryption;

use Codeception\Specify;
use Genetsis\DruID\Core\Encryption\Encryption;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class EncryptionServiceTest extends TestCase
{
    use Specify;

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
