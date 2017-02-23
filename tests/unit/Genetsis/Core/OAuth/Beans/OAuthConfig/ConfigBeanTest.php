<?php
namespace Genetsis\UnitTest\Core\OAuth\Beans\OAuthConfig;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Api;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Brand;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\Core\OAuth\Beans\OAuthConfig\EndPoint;
use Genetsis\Core\OAuth\Beans\OAuthConfig\EntryPoint;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Host;
use Genetsis\Core\OAuth\Beans\OAuthConfig\RedirectUrl;

/**
 * @package Genetsis
 * @category TestCase
 */
class ConfigBeanTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var Config $config */
    private $config;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->config = new Config();
    }

    protected function _after()
    {
    }

    public function testSetterAndGetterClientId()
    {
        $this->specify('Checks setter and getter for "client_id" property.', function(){
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->setClientId('my-id'));
            $this->assertEquals('my-id', $this->config->getClientId());
        });
    }

    public function testSetterAndGetterClientSecret()
    {
        $this->specify('Checks setter and getter for "client_secret" property.', function(){
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->setClientSecret('my-secret'));
            $this->assertEquals('my-secret', $this->config->getClientSecret());
        });
    }

    public function testSetterAndGetterApplicationName()
    {
        $this->specify('Checks setter and getter for "application name" property.', function(){
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->setAppName('my-app-name'));
            $this->assertEquals('my-app-name', $this->config->getAppName());
        });
    }

    public function testSetterAndGetterBrand()
    {
        $this->specify('Checks setter and getter for "brand" property.', function() {
            $this->assertNull($this->config->getBrand());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->setBrand(new Brand(['key' => 'my-brand-key', 'name' => 'my-brand-name'])));
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Brand', $this->config->getBrand());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->setBrand(new Brand(['key' => 'my-brand-key-2', 'name' => 'my-brand-name-2'])));
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Brand', $this->config->getBrand());
            $this->assertEquals('my-brand-key-2', $this->config->getBrand()->getKey());
        });
    }

    public function testSetterAndGetterOpi()
    {
        $this->specify('Checks setter and getter for "opi" property.', function(){
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->setOpi('my-opi-code'));
            $this->assertEquals('my-opi-code', $this->config->getOpi());
        });
    }

    public function testSetterAndGetterHosts()
    {
        $this->specify('Checks setter and getter for "hosts" property.', function(){
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->setHosts([
                new Host(['id' => 1, 'url' => 'www.foo.com']),
                new Host(['id' => 2, 'url' => 'www.bar.com']),
            ]));
            $this->assertCount(2, $this->config->getHosts());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->addHost(new Host(['id' => 3, 'url' => 'www.fuu.com'])));
            $this->assertCount(3, $this->config->getHosts());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Host', $this->config->getHost(1));
            $this->assertEquals(1, $this->config->getHost(1)->getId());
            $this->assertFalse($this->config->getHost(4));
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->addHost(new Host(['id' => 3, 'url' => 'www.fuu-2.com'])));
            $this->assertCount(3, $this->config->getHosts());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Host', $this->config->getHost(3));
            $this->assertEquals('www.fuu-2.com', $this->config->getHost(3)->getUrl());
        });
    }

    public function testSetterAndGetterEndpoints()
    {
        $this->specify('Checks setter and getter for "endpoints" property.', function(){
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->setEndPoints([
                new EndPoint(['id' => 1, 'url' => 'www.foo.com']),
                new EndPoint(['id' => 2, 'url' => 'www.bar.com']),
            ]));
            $this->assertCount(2, $this->config->getEndPoints());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->addEndPoint(new EndPoint(['id' => 3, 'url' => 'www.fuu.com'])));
            $this->assertCount(3, $this->config->getEndPoints());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\EndPoint', $this->config->getEndPoint(1));
            $this->assertEquals(1, $this->config->getEndPoint(1)->getId());
            $this->assertFalse($this->config->getEndPoint(4));
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->addEndPoint(new EndPoint(['id' => 3, 'url' => 'www.fuu-ep-2.com'])));
            $this->assertCount(3, $this->config->getEndPoints());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\EndPoint', $this->config->getEndPoint(3));
            $this->assertEquals('www.fuu-ep-2.com', $this->config->getEndPoint(3)->getUrl());
        });
    }

    public function testSetterAndGetterEntryPoints()
    {
        $this->specify('Checks setter and getter for "entry points" property.', function(){
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->setEntryPoints([
                new EntryPoint(['id' => 1, 'promotion_id' => 'promo1', 'prizes' => []]),
                new EntryPoint(['id' => 2, 'promotion_id' => 'promo2', 'prizes' => []])
            ], 1));
            $this->assertArrayHasKey('entry_points', $this->config->getEntryPoints());
            $this->assertArrayHasKey('default', $this->config->getEntryPoints());
            $this->assertCount(2, $this->config->getEntryPoints()['entry_points']);
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->addEntryPoint(new EntryPoint(['id' => 3, 'promotion_id' => 'promo3', 'prizes' => []]), true));
            $this->assertCount(3, $this->config->getEntryPoints()['entry_points']);
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\EntryPoint', $this->config->getEntryPoint(1));
            $this->assertEquals(1, $this->config->getEntryPoint(1)->getId());
            $this->assertFalse($this->config->getEntryPoint(4));

            // Default entry point.
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\EntryPoint', $this->config->getEntryPoint());
            $this->assertEquals(3, $this->config->getEntryPoint()->getId());

            // Checks if an existing entry point is updated.
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->addEntryPoint(new EntryPoint(['id' => 3, 'promotion_id' => 'promo3_2', 'prizes' => []]), true));
            $this->assertCount(3, $this->config->getEntryPoints()['entry_points']);
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\EntryPoint', $this->config->getEntryPoint(3));
            $this->assertEquals('promo3_2', $this->config->getEntryPoint(3)->getPromotionId());
        });
    }

    public function testSetterAndGetterRedirects()
    {
        $this->specify('Checks setter and getter for "redirects" property.', function(){
            $this->assertCount(0, $this->config->getRedirects());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->setRedirects([
                new RedirectUrl(['type' => 'type-1', 'url' => 'www.type-1_1.com', 'is_default' => true ]),
                new RedirectUrl(['type' => 'type-2', 'url' => 'www.type-2_1.com', 'is_default' => true ]),
                new RedirectUrl(['type' => 'type-3', 'url' => 'www.type-3_1.com', 'is_default' => true ]),
                new RedirectUrl(['type' => 'type-1', 'url' => 'www.type-1_2.com', 'is_default' => false ]),
                new RedirectUrl(['type' => 'type-2', 'url' => 'www.type-2_2.com', 'is_default' => false ]),
            ]));
            $this->assertArrayHasKey('type-1', $this->config->getRedirects());
            $this->assertArrayHasKey('callbacks', $this->config->getRedirects()['type-1']);
            $this->assertArrayHasKey('default', $this->config->getRedirects()['type-1']);
            $this->assertCount(2, $this->config->getRedirects()['type-1']['callbacks']);
            $this->assertEquals('www.type-2_1.com', $this->config->getRedirect('type-2')->getUrl());
            $this->assertEquals('www.type-2_2.com', $this->config->getRedirect('type-2', 'www.type-2_2.com')->getUrl());
            $this->assertFalse($this->config->getRedirect('type-4'));
            $this->assertFalse($this->config->getRedirect('type-2', 'www.type-2_3.com'));
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->addRedirect(new RedirectUrl(['type' => 'type-4', 'url' => 'www.type-4_1.com', 'is_default' => false])));
            $this->assertEquals('www.type-4_1.com', $this->config->getRedirect('type-4')->getUrl());
            $this->config->addRedirect(new RedirectUrl(['type' => 'type-4', 'url' => 'www.type-4_2.com', 'is_default' => true]));
            $this->assertEquals('www.type-4_2.com', $this->config->getRedirect('type-4')->getUrl());
            $this->config->addRedirect(new RedirectUrl(['type' => 'type-4', 'url' => 'www.type-4_3.com', 'is_default' => false]));
            $this->assertEquals('www.type-4_2.com', $this->config->getRedirect('type-4')->getUrl());
            $this->assertEquals('www.type-4_3.com', $this->config->getRedirect('type-4', 'www.type-4_3.com')->getUrl());
        });
    }

    public function testSetterAndGetterApi()
    {
        $this->specify('Checks setter and getter for "api" property.', function(){
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->setApis([
                new Api(['name' => 'api-1', 'base_url' => 'www.api-1.com', 'endpoints' => ['a1' => 'aaa1', 'b1' => 'bbb1']]),
                new Api(['name' => 'api-2', 'base_url' => 'www.api-2.com', 'endpoints' => ['a2' => 'aaa2', 'b2' => 'bbb2']])
            ]));
            $this->assertCount(2, $this->config->getApis());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Api', $this->config->getApi('api-1'));
            $this->assertEquals('api-1', $this->config->getApi('api-1')->getName());
            $this->assertFalse($this->config->getApi('api-99'));
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->addApi(new Api(['name' => 'api-3', 'base_url' => 'www.api-3.com', 'endpoints' => ['a3' => 'aaa3', 'b3' => 'bbb3']])));
            $this->assertCount(3, $this->config->getApis());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->config->addApi(new Api(['name' => 'api-1', 'base_url' => 'www.api-111.com', 'endpoints' => ['a1' => 'aaa1', 'b1' => 'bbb1']])));
            $this->assertCount(3, $this->config->getApis());
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Api', $this->config->getApi('api-1'));
            $this->assertEquals('www.api-111.com', $this->config->getApi('api-1')->getBaseUrl());
        });
    }
}
