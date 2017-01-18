<?php
namespace Genetsis\UnitTest\Core\OAuth\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\OAuth\Beans\AccessToken;
use Genetsis\core\OAuth\Collections\TokenTypes as TokenTypesCollection;

/**
 * @package Genetsis
 * @category UnitTest
 */
class AccessTokenTest extends Unit
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
        $this->specify('Checks that token name has the expected name.', function(){
            $token = new AccessToken('');
            $this->assertEquals(TokenTypesCollection::ACCESS_TOKEN, $token->getName());
        });
    }
}
