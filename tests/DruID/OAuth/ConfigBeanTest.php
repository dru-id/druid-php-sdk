<?php namespace Genetsis\tests\OAuth;

use Genetsis\core\OAuth\Beans\OAuthConfig\Api;
use Genetsis\core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\core\OAuth\Beans\OAuthConfig\EndPoint;
use Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint;
use Genetsis\core\OAuth\Beans\OAuthConfig\RedirectUrl;
use PHPUnit\Framework\TestCase;

class ConfigBeanTest extends TestCase
{

    public function testSettersAndGetters()
    {
        $config = new Config();

        // ClientId
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config->setClientId('my-id'));
        $this->assertEquals('my-id-2', $config->setClientId('my-id-2')->getClientId());

        // ClientSecret
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config->setClientSecret('my-secret'));
        $this->assertEquals('my-secret-2', $config->setClientSecret('my-secret-2')->getClientSecret());

        // Host
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config->setHost('my-host'));
        $this->assertEquals('my-host-2', $config->setHost('my-host-2')->getHost());

        // EndPoints
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config->setEndPoints([
            new EndPoint(['id' => 1, 'url' => 'www.foo.com']),
            new EndPoint(['id' => 2, 'url' => 'www.bar.com']),
        ]));
        $this->assertCount(2, $config->getEndPoints());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config->addEndPoint(new EndPoint(['id' => 3, 'url' => 'www.fuu.com'])));
        $this->assertCount(3, $config->getEndPoints());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EndPoint', $config->getEndPoint(1));
        $this->assertEquals(1, $config->getEndPoint(1)->getId());
        $this->assertFalse($config->getEndPoint(4));

        // EntryPoints
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config->setEntryPoints([
            new EntryPoint(['id' => 1, 'promotion_id' => 'promo1', 'prizes' => []]),
            new EntryPoint(['id' => 2, 'promotion_id' => 'promo2', 'prizes' => []])
        ], 1));
        $this->assertArrayHasKey('entry_points', $config->getEntryPoints());
        $this->assertArrayHasKey('default', $config->getEntryPoints());
        $this->assertCount(2, $config->getEntryPoints()['entry_points']);
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config->addEntryPoint(new EntryPoint(['id' => 3, 'promotion_id' => 'promo3', 'prizes' => []]), true));
        $this->assertCount(3, $config->getEntryPoints()['entry_points']);
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint', $config->getEntryPoint(1));
        $this->assertEquals(1, $config->getEntryPoint(1)->getId());
        $this->assertFalse($config->getEntryPoint(4));
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint', $config->getEntryPoint());
        $this->assertEquals(3, $config->getEntryPoint()->getId());

        // Redirects
        $this->assertCount(0, $config->getRedirects());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config->setRedirects([
            new RedirectUrl(['type' => 'type-1', 'url' => 'www.type-1_1.com', 'is_default' => true ]),
            new RedirectUrl(['type' => 'type-2', 'url' => 'www.type-2_1.com', 'is_default' => true ]),
            new RedirectUrl(['type' => 'type-3', 'url' => 'www.type-3_1.com', 'is_default' => true ]),
            new RedirectUrl(['type' => 'type-1', 'url' => 'www.type-1_2.com', 'is_default' => false ]),
            new RedirectUrl(['type' => 'type-2', 'url' => 'www.type-2_2.com', 'is_default' => false ]),
        ]));
        $this->assertArrayHasKey('type-1', $config->getRedirects());
        $this->assertArrayHasKey('callbacks', $config->getRedirects()['type-1']);
        $this->assertArrayHasKey('default', $config->getRedirects()['type-1']);
        $this->assertCount(2, $config->getRedirects()['type-1']['callbacks']);
        $this->assertEquals('www.type-2_1.com', $config->getRedirect('type-2')->getUrl());
        $this->assertEquals('www.type-2_2.com', $config->getRedirect('type-2', 'www.type-2_2.com')->getUrl());
        $this->assertFalse($config->getRedirect('type-4'));
        $this->assertFalse($config->getRedirect('type-2', 'www.type-2_3.com'));
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config->addRedirect(new RedirectUrl(['type' => 'type-4', 'url' => 'www.type-4_1.com', 'is_default' => false])));
        $this->assertEquals('www.type-4_1.com', $config->getRedirect('type-4')->getUrl());
        $config->addRedirect(new RedirectUrl(['type' => 'type-4', 'url' => 'www.type-4_2.com', 'is_default' => true]));
        $this->assertEquals('www.type-4_2.com', $config->getRedirect('type-4')->getUrl());
        $config->addRedirect(new RedirectUrl(['type' => 'type-4', 'url' => 'www.type-4_3.com', 'is_default' => false]));
        $this->assertEquals('www.type-4_2.com', $config->getRedirect('type-4')->getUrl());
        $this->assertEquals('www.type-4_3.com', $config->getRedirect('type-4', 'www.type-4_3.com')->getUrl());

        // APIs
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config->setApis([
            new Api(['name' => 'api-1', 'base_url' => 'www.api-1.com', 'endpoints' => ['a1' => 'aaa1', 'b1' => 'bbb1']]),
            new Api(['name' => 'api-2', 'base_url' => 'www.api-2.com', 'endpoints' => ['a2' => 'aaa2', 'b2' => 'bbb2']])
        ]));
        $this->assertCount(2, $config->getApis());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Api', $config->getApi('api-1'));
        $this->assertEquals('api-1', $config->getApi('api-1')->getName());
        $this->assertFalse($config->getApi('api-99'));
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config->addApi(new Api(['name' => 'api-3', 'base_url' => 'www.api-3.com', 'endpoints' => ['a3' => 'aaa3', 'b3' => 'bbb3']])));
        $this->assertCount(3, $config->getApis());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config->addApi(new Api(['name' => 'api-1', 'base_url' => 'www.api-111.com', 'endpoints' => ['a1' => 'aaa1', 'b1' => 'bbb1']])));
        $this->assertCount(3, $config->getApis());
        $this->assertEquals('www.api-111.com', $config->getApi('api-1')->getBaseUrl());
    }

}
