<?php
namespace Genetsis\UnitTest\Core\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Host;

/**
 * @package Genetsis
 * @category UnitTest
 */
class HostBeanTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var Host $host */
    private $host;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->host = new Host();
    }

    protected function _after()
    {
    }

    public function testSetterAndGetterId()
    {
        $this->specify('Checks setter and getter for "id" property.', function() {
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Host', $this->host->setId('my-id'));
            $this->assertEquals('my-id', $this->host->getId());
        });
    }

    public function testSetterAndGetterUrl()
    {
        $this->specify('Checks setter and getter for "url" property.', function() {
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Host', $this->host->setUrl('http://www.foo.com'));
            $this->assertEquals('http://www.foo.com', $this->host->getUrl());
        });
    }

    public function testSetterAndGetterEndpoint()
    {
        $this->specify('Checks if an "endpoint" object has converted properly when is required as string.', function() {
            $this->host->setUrl('http://www.bar.com');
            $this->assertEquals('http://www.bar.com', $this->host);
        });
    }

    public function testConstructor()
    {
        $this->host = new Host([
            'id' => 'my-id',
            'url' => 'http://www.foo.com'
        ]);

        $this->specify('Checks that constructor has assigned those variables properly.', function() {
            $this->assertEquals('my-id', $this->host->getId());
            $this->assertEquals('http://www.foo.com', $this->host->getUrl());
        });
    }

}
