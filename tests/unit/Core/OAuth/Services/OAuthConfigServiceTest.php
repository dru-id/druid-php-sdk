<?php
namespace Genetsis\UnitTest\Core\OAuth\Beans\Services;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\OAuth\Services\OAuthConfig;

/**
 * @package Genetsis
 * @category UnitTest
 */
class OAuthConfigServiceTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
    }

    protected function _after()
    {
    }

    public function testBuildFromXml_v1_4()
    {
        $this->specify('Checks XML load from string.', function(){
            $config = OAuthConfig::buildConfigFromXml(file_get_contents(OAUTHCONFIG_SAMPLE_XML_1_4), '1.4');
            $this->verificationSteps($config);
        });

        $this->specify('Checks XML load from file.', function(){
            $config = OAuthConfig::buildConfigFromXmlFile(OAUTHCONFIG_SAMPLE_XML_1_4, '1.4');
            $this->verificationSteps($config);
        });
    }

    /**
     * @param \Genetsis\core\OAuth\Beans\OAuthConfig\Config $config
     */
    private function verificationSteps($config)
    {
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Config', $config);

        $this->assertEquals('XXXXXXX', $config->getClientId());
        $this->assertEquals('YYYYYYY', $config->getClientSecret());
        $this->assertEquals('My Awesome App', $config->getAppName());

        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Brand', $config->getBrand());
        $this->assertEquals('my-brand-key', $config->getBrand()->getKey());
        $this->assertEquals('Brand Name', $config->getBrand()->getName());

        $this->assertEquals('my-opi-tag', $config->getOpi());

        $this->assertCount(2, $config->getHosts());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Host', $config->getHost('my-host-2'));
        $this->assertEquals('//www.foo-host-2.com', $config->getHost('my-host-2')->getUrl());

        $this->assertCount(5, $config->getRedirects());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\RedirectUrl', $config->getRedirect('postLogin'));
        $this->assertEquals('http://www.foo.com/actions', $config->getRedirect('postLogin')->getUrl());

        $this->assertCount(3, $config->getEntryPoints()['entry_points']);
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint', $config->getEntryPoint('2222222-main'));
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EntryPoint', $config->getEntryPoint());
        $this->assertEquals('2222222-main', $config->getEntryPoint('2222222-main')->getId());
        $this->assertEquals('1111111-main', $config->getEntryPoint()->getId());

        $this->assertCount(8, $config->getEndPoints());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\EndPoint', $config->getEndPoint('cancel_url'));
        $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize/redirect', $config->getEndPoint('cancel_url')->getUrl());

        $this->assertCount(3, $config->getApis());
        $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\OAuthConfig\Api', $config->getApi('api.activityid'));
        $this->assertEquals('/public/v1/bookmark/acknowledge', $config->getApi('api.activityid')->getEndpoint('click_newsletter'));
    }

}
