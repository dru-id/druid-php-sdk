<?php
namespace Genetsis\UnitTest\Core\Http\Services;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\Core\User\Collections\LoginStatusTypes;

/**
 * @package Genetsis
 * @category UnitTest
 */
class LoginStatusTypesCollectionTest extends Unit
{
    use Specify;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

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
