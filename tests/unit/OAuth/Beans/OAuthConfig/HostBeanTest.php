<?php
namespace Genetsis\UnitTest\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\OAuth\Beans\OAuthConfig\Host;

/**
 * @package Genetsis
 * @category UnitTest
 */
class HostBeanTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var Host $host */
    private $host;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
    }

    protected function _after()
    {
    }

    public function testSettersAndGetters()
    {
        $this->host = new Host();

        $this->specify('Checks setter and getter for "id" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Host', $this->host->setId('my-id'));
            $this->assertEquals('my-id', $this->host->getId());
        });

        $this->specify('Checks setter and getter for "url" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Host', $this->host->setUrl('http://www.foo.com'));
            $this->assertEquals('http://www.foo.com', $this->host->getUrl());
        });

        $this->specify('Checks if an "endpoint" object has converted properly when is required as string.', function() {
            $this->host->setUrl('http://www.bar.com');
            $this->assertEquals('http://www.bar.com', $this->host);
        });
    }

    /**
     * @depends testSettersAndGetters
     */
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
