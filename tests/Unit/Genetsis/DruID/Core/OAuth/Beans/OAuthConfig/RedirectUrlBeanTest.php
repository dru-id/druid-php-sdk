<?php
namespace Genetsis\DruID\UnitTest\Core\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\RedirectUrl;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class RedirectUrlBeanTest extends TestCase
{
    use Specify;

    /** @var RedirectUrl $redirect_url */
    private $redirect_url;

    protected function setUp()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->redirect_url = new RedirectUrl();
    }

    public function testSetterAndGetterType()
    {
        $this->specify('Checks setter and getter for "type" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\RedirectUrl', $this->redirect_url->setType('type-1'));
            $this->assertEquals('type-1', $this->redirect_url->getType());
        });
    }

    public function testSetterAndGetterUrl()
    {
        $this->specify('Checks setter and getter for "url" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\RedirectUrl', $this->redirect_url->setUrl('http://www.foo.com'));
            $this->assertEquals('http://www.foo.com', $this->redirect_url->getUrl());
        });
    }

    public function testSetterAndGetterIsDefault()
    {
        $this->specify('Checks setter and getter for "is default" property.', function() {
            $this->assertInstanceOf('\Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\RedirectUrl', $this->redirect_url->setIsDefault(true));
            $this->assertTrue($this->redirect_url->getIsDefault());
        });
    }

    public function testSetterAndGetterEndpoint()
    {
        $this->specify('Checks if an "endpoint" object has converted properly when is required as string.', function() {
            $this->redirect_url->setUrl('http://www.bar.com');
            $this->assertEquals('http://www.bar.com', $this->redirect_url);
        });
    }

    public function testConstructor()
    {
        $this->redirect_url = new RedirectUrl([
            'type' => 'type-1',
            'url' => 'http://www.foo.com',
            'is_default' => true
        ]);

        $this->specify('Checks that constructor has assigned those variables properly.', function() {
            $this->assertEquals('type-1', $this->redirect_url->getType());
            $this->assertEquals('http://www.foo.com', $this->redirect_url->getUrl());
            $this->assertTrue($this->redirect_url->getIsDefault());
        });
    }

}
