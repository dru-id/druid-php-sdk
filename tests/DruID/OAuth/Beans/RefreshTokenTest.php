<?php namespace Genetsis\tests\OAuth\Beans;

use Genetsis\core\OAuth\Beans\RefreshToken;
use Genetsis\core\OAuth\Collections\TokenTypes as TokenTypesCollection;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis
 * @category TestCase
 */
class RefreshTokenTest extends TestCase {

    public function testTokenName()
    {
        $token = new RefreshToken('');
        $this->assertEquals(TokenTypesCollection::REFRESH_TOKEN, $token->getName());
    }

}
