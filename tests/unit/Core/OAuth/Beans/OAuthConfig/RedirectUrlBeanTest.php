<?php
namespace Genetsis\UnitTest\Core\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\OAuth\Beans\OAuthConfig\RedirectUrl;

/**
 * @package Genetsis
 * @category UnitTest
 */
class RedirectUrlBeanTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var RedirectUrl $redirect_url */
    private $redirect_url;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
    }

    protected function _after()
    {
    }

    public function testSettersAndGetters()
    {
        $this->redirect_url = new RedirectUrl();

        $this->specify('Checks setter and getter for "type" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\RedirectUrl', $this->redirect_url->setType('type-1'));
            $this->assertEquals('type-1', $this->redirect_url->getType());
        });

        $this->specify('Checks setter and getter for "url" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\RedirectUrl', $this->redirect_url->setUrl('http://www.foo.com'));
            $this->assertEquals('http://www.foo.com', $this->redirect_url->getUrl());
        });

        $this->specify('Checks setter and getter for "is default" property.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\RedirectUrl', $this->redirect_url->setIsDefault(true));
            $this->assertTrue($this->redirect_url->getIsDefault());
        });

        $this->specify('Checks if an "endpoint" object has converted properly when is required as string.', function() {
            $this->redirect_url->setUrl('http://www.bar.com');
            $this->assertEquals('http://www.bar.com', $this->redirect_url);
        });
    }

    /**
     * @depends testSettersAndGetters
     */
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
