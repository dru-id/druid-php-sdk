<?php
namespace Genetsis\UnitTest\Core\OAuth\Beans;

use Codeception\Specify;
use Codeception\Test\Unit;
use Doctrine\Common\Cache\VoidCache;
use Genetsis\Core\Logger\VoidLogger;
use Genetsis\Core\OAuth\OAuthConfigFactory;

/**
 * @package Genetsis
 * @category UnitTest
 */
class OAuthConfigFactoryServiceTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;
    /** @var OAuthConfigFactory $factory */
    protected $factory;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->factory = new OAuthConfigFactory(new VoidLogger(), new VoidCache());
    }

    protected function _after()
    {
    }

    public function testBuildFromXml_v1_4()
    {
        $this->specify('Checks XML load from string.', function(){
            $config = $this->factory->buildConfigFromXml(file_get_contents(OAUTHCONFIG_SAMPLE_XML_1_4));
            $this->verificationSteps($config);
        });

        $this->specify('Checks XML load from file.', function(){
            $config = $this->factory->buildConfigFromXmlFile(OAUTHCONFIG_SAMPLE_XML_1_4);
            $this->verificationSteps($config);
        });

        $this->specify('Checks if factory throws an exception if file doesn\'t exist.', function(){
            $config = $this->factory->buildConfigFromXmlFile(OAUTHCONFIG_SAMPLE_XML_1_4.'foo');
            $this->verificationSteps($config);
        }, ['throws' => \InvalidArgumentException::class]);

        $this->specify('Checks if factory throws an exception with an empty XML', function(){
            $this->factory->buildConfigFromXml('');
        }, ['throws' => 'InvalidArgumentException']);

        $this->specify('Checks if factory throws an exception with an invalid XML', function(){
            $this->factory->buildConfigFromXml('<?xml ?><foo</foo>');
        }, ['throws' => 'Exception']);
    }

    /**
     * @param \Genetsis\Core\OAuth\Beans\OAuthConfig\Config $config
     */
    private function verificationSteps($config)
    {
        $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $config);

        $this->assertEquals('1.4', $config->getVersion());
        $this->assertEquals('XXXXXXX', $config->getClientId());
        $this->assertEquals('YYYYYYY', $config->getClientSecret());
        $this->assertEquals('My Awesome App', $config->getAppName());

        $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Brand', $config->getBrand());
        $this->assertEquals('my-brand-key', $config->getBrand()->getKey());
        $this->assertEquals('Brand Name', $config->getBrand()->getName());

        $this->assertEquals('my-opi-tag', $config->getOpi());

        $this->assertCount(2, $config->getHosts());
        $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Host', $config->getHost('my-host-2'));
        $this->assertEquals('//www.foo-host-2.com', $config->getHost('my-host-2')->getUrl());

        $this->assertCount(5, $config->getRedirects());
        $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\RedirectUrl', $config->getRedirect('postLogin'));
        $this->assertEquals('http://www.foo.com/actions', $config->getRedirect('postLogin')->getUrl());

        $this->assertCount(3, $config->getEntryPoints()['entry_points']);
        $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\EntryPoint', $config->getEntryPoint('2222222-main'));
        $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\EntryPoint', $config->getEntryPoint());
        $this->assertEquals('2222222-main', $config->getEntryPoint('2222222-main')->getId());
        $this->assertEquals('1111111-main', $config->getEntryPoint()->getId());

        $this->assertCount(8, $config->getEndPoints());
        $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\EndPoint', $config->getEndPoint('cancel_url'));
        $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize/redirect', $config->getEndPoint('cancel_url')->getUrl());

        $this->assertCount(3, $config->getApis());
        $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Api', $config->getApi('api.activityid'));
        $this->assertEquals('/public/v1/bookmark/acknowledge', $config->getApi('api.activityid')->getEndpoint('click_newsletter'));
    }

}
