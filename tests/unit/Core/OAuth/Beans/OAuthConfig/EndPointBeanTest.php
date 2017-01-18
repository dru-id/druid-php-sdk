<?php
namespace Genetsis\UnitTest\Core\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\Core\OAuth\Beans\OAuthConfig\EndPoint;

/**
 * @package Genetsis
 * @category UnitTest
 */
class EndPointBeanTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var EndPoint $endpoint */
    private $endpoint;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->endpoint = new EndPoint();
    }

    protected function _after()
    {
    }

    public function testSetterAndGetterId()
    {
        $this->specify('Checks setter and getter for "id" property.', function() {
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\EndPoint', $this->endpoint->setId('my-id'));
            $this->assertEquals('my-id', $this->endpoint->getId());
        });
    }

    public function testSetterAndGetterUrl()
    {
        $this->specify('Checks setter and getter for "url" property.', function() {
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\EndPoint', $this->endpoint->setUrl('http://www.foo.com'));
            $this->assertEquals('http://www.foo.com', $this->endpoint->getUrl());
        });
    }

    public function testSetterAndGetterEndpoint()
    {
        $this->specify('Checks if an "endpoint" object has converted properly when is required as string.', function() {
            $this->endpoint->setUrl('http://www.bar.com');
            $this->assertEquals('http://www.bar.com', $this->endpoint);
        });
    }

    public function testConstructor()
    {
        $this->endpoint = new EndPoint([
            'id' => 'my-id',
            'url' => 'http://www.foo.com'
        ]);

        $this->specify('Checks constructor.', function() {
            $this->assertEquals('my-id', $this->endpoint->getId());
            $this->assertEquals('http://www.foo.com', $this->endpoint->getUrl());
        });
    }
}
