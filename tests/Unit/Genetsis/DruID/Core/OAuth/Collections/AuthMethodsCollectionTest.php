<?php
namespace Genetsis\DruID\UnitTest\Core\Http;

use Codeception\Specify;
use Genetsis\DruID\Core\OAuth\Collections\AuthMethods;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class AuthMethodsCollectionTest extends TestCase
{
    use Specify;

    public function testAuthMethodsCollectionVerification()
    {
        $this->specify('Checks all collections values.', function(){
            $this->assertTrue(AuthMethods::check(AuthMethods::GRANT_TYPE_AUTH_CODE));
            $this->assertTrue(AuthMethods::check(AuthMethods::GRANT_TYPE_REFRESH_TOKEN));
            $this->assertTrue(AuthMethods::check(AuthMethods::GRANT_TYPE_CLIENT_CREDENTIALS));
            $this->assertTrue(AuthMethods::check(AuthMethods::GRANT_TYPE_VALIDATE_BEARER));
            $this->assertTrue(AuthMethods::check(AuthMethods::GRANT_TYPE_EXCHANGE_SESSION));
            $this->assertFalse(AuthMethods::check('nope'));
        });
    }

}
