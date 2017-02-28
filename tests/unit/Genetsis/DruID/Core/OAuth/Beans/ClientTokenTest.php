<?php
namespace Genetsis\DruID\UnitTest\Core\OAuth\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\DruID\Core\OAuth\Beans\ClientToken;
use Genetsis\DruID\Core\OAuth\Collections\TokenTypes as TokenTypesCollection;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class ClientTokenTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testTokenName()
    {
        $this->specify('Checks that token name has the expected name.', function() {
            $token = new ClientToken('');
            $this->assertEquals(TokenTypesCollection::CLIENT_TOKEN, $token->getName());
        });
    }
}
