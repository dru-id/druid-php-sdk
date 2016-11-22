<?php namespace Genetsis\tests\OAuth\Beans;

use Genetsis\core\OAuth\Beans\ClientToken;
use Genetsis\core\OAuth\Collections\TokenTypes as TokenTypesCollection;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis
 * @category TestCase
 */
class ClientTokenTest extends TestCase {

    public function testTokenName()
    {
        $token = new ClientToken('');
        $this->assertEquals(TokenTypesCollection::CLIENT_TOKEN, $token->getName());
    }

}
