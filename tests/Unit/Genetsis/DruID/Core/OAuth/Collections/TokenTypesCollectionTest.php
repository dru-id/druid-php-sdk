<?php
namespace Genetsis\DruID\UnitTest\Core\Http;

use Codeception\Specify;
use Genetsis\DruID\Core\OAuth\Collections\TokenTypes;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class TokenTypesCollectionTest extends TestCase
{
    use Specify;

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
