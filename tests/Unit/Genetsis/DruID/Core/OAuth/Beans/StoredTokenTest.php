<?php
namespace Genetsis\DruID\UnitTest\Core\OAuth\Beans;

use Codeception\Specify;
use Genetsis\DruID\Core\OAuth\Beans\AccessToken;
use Genetsis\DruID\Core\OAuth\Beans\ClientToken;
use Genetsis\DruID\Core\OAuth\Beans\RefreshToken;
use Genetsis\DruID\Core\OAuth\Beans\StoredToken;
use Genetsis\DruID\Core\OAuth\Collections\TokenTypes as TokenTypesCollection;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class StoredTokenTest extends TestCase
{
    use Specify;

    /** @var StoredToken $token */
    private $token;

    protected function setUp()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->token = new StoredToken('');
    }

    public function testSetterAndGetterName()
    {
        $this->specify('Checks setter and getter for "name" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Contracts\StoredTokenInterface', $this->token->setName(TokenTypesCollection::ACCESS_TOKEN));
            $this->assertEquals(TokenTypesCollection::ACCESS_TOKEN, $this->token->getName());
        });

        $this->specify('Checks if an exception is thrown when an invalid name is set.', function() {
            $this->token->setName('invalid-name');
            $this->assertEquals(TokenTypesCollection::ACCESS_TOKEN, $this->token->getName());
        }, ['throws' => 'InvalidArgumentException']);
    }

    public function testSetterAndGetterValue()
    {
        $this->specify('Checks setter and getter for "value" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Contracts\StoredTokenInterface', $this->token->setValue('foo'));
            $this->assertEquals('foo', $this->token->getValue());
        });
    }

    public function testSetterAndGetterExpiresIn()
    {
        $this->specify('Checks setter and getter for "expires in" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Contracts\StoredTokenInterface', $this->token->setExpiresIn(123));
            $this->assertEquals(123, $this->token->getExpiresIn());
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Contracts\StoredTokenInterface', $this->token->setExpiresIn(-3));
            $this->assertEquals(0, $this->token->getExpiresIn());
        });
    }

    public function testSetterAndGetterExpiresAt()
    {
        $this->specify('Checks setter and getter for "expires at" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Contracts\StoredTokenInterface', $this->token->setExpiresAt(456));
            $this->assertEquals(456, $this->token->getExpiresAt());
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Contracts\StoredTokenInterface', $this->token->setExpiresAt(-1));
            $this->assertEquals(0, $this->token->getExpiresAt());
        });
    }

    public function testSetterAndGetterPath()
    {
        $this->specify('Checks setter and getter for "path" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Contracts\StoredTokenInterface', $this->token->setPath('foo/bar'));
            $this->assertEquals('foo/bar', $this->token->getPath());
        });
    }

    public function testConstructor()
    {
        $this->specify('Checks that constructor has assigned those variables properly.', function(){
            $token = new AccessToken('my-value', 123, 456, 'foo/bar');
            $this->assertEquals(TokenTypesCollection::ACCESS_TOKEN, $token->getName());
            $this->assertEquals('my-value', $token->getValue());
            $this->assertEquals(123, $token->getExpiresIn());
            $this->assertEquals(456, $token->getExpiresAt());
            $this->assertEquals('foo/bar', $token->getPath());
        });

        $this->specify('Checks that constructor has assigned those variables properly.', function(){
            $token = new ClientToken('my-value', 123, 456, 'foo/bar');
            $this->assertEquals(TokenTypesCollection::CLIENT_TOKEN, $token->getName());
            $this->assertEquals('my-value', $token->getValue());
            $this->assertEquals(123, $token->getExpiresIn());
            $this->assertEquals(456, $token->getExpiresAt());
            $this->assertEquals('foo/bar', $token->getPath());
        });

        $this->specify('Checks that constructor has assigned those variables properly.', function(){
            $token = new RefreshToken('my-value', 123, 456, 'foo/bar');
            $this->assertEquals(TokenTypesCollection::REFRESH_TOKEN, $token->getName());
            $this->assertEquals('my-value', $token->getValue());
            $this->assertEquals(123, $token->getExpiresIn());
            $this->assertEquals(456, $token->getExpiresAt());
            $this->assertEquals('foo/bar', $token->getPath());
        });
    }

    public function testFactory()
    {
        $this->specify('Checks that factory works properly for each type of token.', function () {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\AccessToken', StoredToken::factory(TokenTypesCollection::ACCESS_TOKEN, ''));
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\RefreshToken', StoredToken::factory(TokenTypesCollection::REFRESH_TOKEN, ''));
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\ClientToken', StoredToken::factory(TokenTypesCollection::CLIENT_TOKEN, ''));
            $this->assertFalse(StoredToken::factory('foo', ''));
            $this->assertEquals('access-token', StoredToken::factory(TokenTypesCollection::ACCESS_TOKEN, 'access-token')->getValue());
            $this->assertEquals('refresh-token', StoredToken::factory(TokenTypesCollection::REFRESH_TOKEN, 'refresh-token')->getValue());
            $this->assertEquals('client-token', StoredToken::factory(TokenTypesCollection::CLIENT_TOKEN, 'client-token')->getValue());
        });
    }

}
