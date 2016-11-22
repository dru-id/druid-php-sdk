<?php namespace Genetsis\tests\OAuth\Beans;

use Genetsis\core\OAuth\Beans\AccessToken;
use Genetsis\core\OAuth\Collections\TokenTypes as TokenTypesCollection;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis
 * @category TestCase
 */
class AccessTokenTest extends TestCase {

    public function testTokenName()
    {
        $token = new AccessToken('');
        $this->assertEquals(TokenTypesCollection::ACCESS_TOKEN, $token->getName());
    }

}
