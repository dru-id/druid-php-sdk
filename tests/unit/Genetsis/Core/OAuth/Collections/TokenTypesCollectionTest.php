<?php
namespace Genetsis\UnitTest\Core\Http;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\Core\OAuth\Collections\TokenTypes;

/**
 * @package Genetsis
 * @category UnitTest
 */
class TokenTypesCollectionTest extends Unit
{

    use Specify;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testTokenTypesCollectionVerification()
    {
        $this->specify('Checks all collections values.', function(){
            $this->assertTrue(TokenTypes::check(TokenTypes::CLIENT_TOKEN));
            $this->assertTrue(TokenTypes::check(TokenTypes::ACCESS_TOKEN));
            $this->assertTrue(TokenTypes::check(TokenTypes::REFRESH_TOKEN));
            $this->assertFalse(TokenTypes::check('nope'));
        });
    }

}
