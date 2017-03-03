<?php
namespace Genetsis\DruID\UnitTest\Core\Http;

use Codeception\Specify;
use Genetsis\DruID\Core\User\Collections\LoginStatusTypes;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class LoginStatusTypesCollectionTest extends TestCase
{
    use Specify;

    public function testLoginStatusTypesCollectionVerification()
    {
        $this->specify('Checks all collections values.', function(){
            $this->assertTrue(LoginStatusTypes::check(LoginStatusTypes::CONNECTED));
            $this->assertTrue(LoginStatusTypes::check(LoginStatusTypes::NOT_CONNECTED));
            $this->assertTrue(LoginStatusTypes::check(LoginStatusTypes::UNKNOWN));
            $this->assertFalse(LoginStatusTypes::check('nope'));
        });
    }
}
