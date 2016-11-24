<?php
namespace Genetsis\UnitTest\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\OAuth\Beans\OAuthConfig\EndPoint;

/**
 * @package Genetsis
 * @category UnitTest
 */
class EndPointBeanTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var EndPoint $endpoint */
    private $endpoint;

    public function testSettersAndGetters()
    {
        $this->endpoint = new EndPoint();

        $this->specify('Checks setter and getter for "id" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EndPoint', $this->endpoint->setId('my-id'));
            $this->assertEquals('my-id', $this->endpoint->getId());
        });

        $this->specify('Checks setter and getter for "url" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EndPoint', $this->endpoint->setUrl('http://www.foo.com'));
            $this->assertEquals('http://www.foo.com', $this->endpoint->getUrl());
        });

        $this->specify('Checks if an "endpoint" object has converted properly when is required as string.', function() {
            $this->endpoint->setUrl('http://www.bar.com');
            $this->assertEquals('http://www.bar.com', $this->endpoint);
        });
    }

    /**
     * @depends testSettersAndGetters
     */
    public function testConstructor()
    {
        $this->endpoint = new EndPoint([
            'id' => 'my-id',
            'url' => 'http://www.foo.com'
        ]);

        $this->specify('Checks that constructor has assigned those variables properly.', function() {
            $this->assertEquals('my-id', $this->endpoint->getId());
            $this->assertEquals('http://www.foo.com', $this->endpoint->getUrl());
        });
    }

}
