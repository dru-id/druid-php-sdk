<?php
namespace Genetsis\DruID\UnitTest\Core\OAuth\Beans;

use Codeception\Specify;
use Genetsis\DruID\Core\OAuth\Beans\ClientToken;
use Genetsis\DruID\Core\OAuth\Collections\TokenTypes as TokenTypesCollection;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class ClientTokenTest extends TestCase
{
    use Specify;

    public function testTokenName()
    {
        $this->specify('Checks that token name has the expected name.', function() {
            $token = new ClientToken('');
            $this->assertEquals(TokenTypesCollection::CLIENT_TOKEN, $token->getName());
        });
    }
}
