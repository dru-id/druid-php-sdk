<?php namespace Genetsis\tests\OAuth\Beans;

use Genetsis\core\OAuth\Beans\AccessToken;
use Genetsis\core\OAuth\Beans\ClientToken;
use Genetsis\core\OAuth\Beans\RefreshToken;
use Genetsis\core\OAuth\Beans\StoredToken;
use Genetsis\core\OAuth\Collections\TokenTypes as TokenTypesCollection;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis
 * @category TestCase
 */
class StoredTokenTest extends TestCase {

    public function testSettersAndGetters()
    {
        $token = new StoredToken('');

        $this->assertInstanceOf('\Genetsis\core\OAuth\Contracts\StoredTokenInterface', $token->setName(TokenTypesCollection::ACCESS_TOKEN));
        $this->assertEquals(TokenTypesCollection::ACCESS_TOKEN, $token->getName());
        $this->expectException(\InvalidArgumentException::class);
        $token->setName('invalid-name');
        $this->assertEquals(TokenTypesCollection::ACCESS_TOKEN, $token->getName());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Contracts\StoredTokenInterface', $token->setValue('foo'));
        $this->assertEquals('foo', $token->getValue());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Contracts\StoredTokenInterface', $token->setExpiresIn(123));
        $this->assertEquals(123, $token->getExpiresIn());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Contracts\StoredTokenInterface', $token->setExpiresIn(-3));
        $this->assertEquals(0, $token->getExpiresIn());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Contracts\StoredTokenInterface', $token->setExpiresAt(456));
        $this->assertEquals(456, $token->getExpiresAt());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Contracts\StoredTokenInterface', $token->setExpiresAt(-1));
        $this->assertEquals(0, $token->getExpiresAt());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Contracts\StoredTokenInterface', $token->setPath('foo/bar'));
        $this->assertEquals('foo/bar', $token->getPath());
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $token = new AccessToken('my-value', 123, 456, 'foo/bar');
        $this->assertEquals(TokenTypesCollection::ACCESS_TOKEN, $token->getName());
        $this->assertEquals('my-value', $token->getValue());
        $this->assertEquals(123, $token->getExpiresIn());
        $this->assertEquals(456, $token->getExpiresAt());
        $this->assertEquals('foo/bar', $token->getPath());

        $token = new ClientToken('my-value', 123, 456, 'foo/bar');
        $this->assertEquals(TokenTypesCollection::CLIENT_TOKEN, $token->getName());
        $this->assertEquals('my-value', $token->getValue());
        $this->assertEquals(123, $token->getExpiresIn());
        $this->assertEquals(456, $token->getExpiresAt());
        $this->assertEquals('foo/bar', $token->getPath());

        $token = new RefreshToken('my-value', 123, 456, 'foo/bar');
        $this->assertEquals(TokenTypesCollection::REFRESH_TOKEN, $token->getName());
        $this->assertEquals('my-value', $token->getValue());
        $this->assertEquals(123, $token->getExpiresIn());
        $this->assertEquals(456, $token->getExpiresAt());
        $this->assertEquals('foo/bar', $token->getPath());
    }

    /**
     * @depends testConstructor
     */
    public function testFactory()
    {
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\AccessToken', StoredToken::factory(TokenTypesCollection::ACCESS_TOKEN, ''));
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\RefreshToken', StoredToken::factory(TokenTypesCollection::REFRESH_TOKEN, ''));
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\ClientToken', StoredToken::factory(TokenTypesCollection::CLIENT_TOKEN, ''));
        $this->assertFalse(StoredToken::factory('foo', ''));

        $this->assertEquals('access-token', StoredToken::factory(TokenTypesCollection::ACCESS_TOKEN, 'access-token')->getValue());
        $this->assertEquals('refresh-token', StoredToken::factory(TokenTypesCollection::REFRESH_TOKEN, 'refresh-token')->getValue());
        $this->assertEquals('client-token', StoredToken::factory(TokenTypesCollection::CLIENT_TOKEN, 'client-token')->getValue());
    }

}
