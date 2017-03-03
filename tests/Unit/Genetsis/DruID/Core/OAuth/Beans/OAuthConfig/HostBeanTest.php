<?php
namespace Genetsis\DruID\UnitTest\Core\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Host;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class HostBeanTest extends TestCase
{
    use Specify;

    /** @var Host $host */
    private $host;

    protected function setUp()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->host = new Host();
    }

    public function testSetterAndGetterId()
    {
        $this->specify('Checks setter and getter for "id" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Host', $this->host->setId('my-id'));
            $this->assertEquals('my-id', $this->host->getId());
        });
    }

    public function testSetterAndGetterUrl()
    {
        $this->specify('Checks setter and getter for "url" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Host', $this->host->setUrl('http://www.foo.com'));
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