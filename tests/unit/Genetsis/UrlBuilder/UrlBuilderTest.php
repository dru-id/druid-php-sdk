<?php
namespace UnitTest\Genetsis\UrlBuilder;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\Core\OAuth\Beans\AccessToken;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\Core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\Core\User\Beans\Things;
use Genetsis\Identity\Identity;
use Genetsis\UrlBuilder\UrlBuilder;
use Prophecy\Argument;
use Prophecy\Prophet;
use Psr\Log\LoggerInterface;

/**
 * @package UnitTest\Genetsis\UrlBuilder
 * @category UnitTest
 * @author Ismael Salgado <ismael.salgado@genetsis.com>
 */
class UrlBuilderTest extends Unit
{
    use Specify;
    
    /** @var Prophet $prophet */
    private $prophet;
    
    protected function _before()
    {
        $this->prophet = new Prophet();
    }
    protected function _after()
    {
    }

    public function testConstructor ()
    {
        $this->specify('Checks constructor.', function() {
            $object = new UrlBuilder(
                $this->prophet->prophesize(Identity::class)->reveal(),
                $this->prophet->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertInstanceOf(Identity::class, getProperty($object, 'identity'));
            $this->assertInstanceOf(OAuthServiceInterface::class, getProperty($object, 'oauth'));
            $this->assertInstanceOf(LoggerInterface::class, getProperty($object, 'logger'));
        });
    }

    public function testGetUrlLogin ()
    {
        $this->specify('Checks if the login URL is properly generated.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getEndPoint(Argument::cetera())->will(function(){
                return 'https://auth.ci.dru-id.com/oauth2/authorize';
            });
            $config_proph->getRedirect(Argument::cetera())->will(function() {
                return 'http://examples.dev.dru-id.com/actions/callback';
            });
            $config_proph->getRedirect('postLogin', 'http://www.foo.bar/actions/callback')->will(function() {
                return 'http://www.foo.bar/actions/callback';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $object = new UrlBuilder(
                $this->prophet->prophesize(Identity::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fexamples.dev.dru-id.com%2Factions%2Fcallback&response_type=code', $object->getUrlLogin());
            $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fexamples.dev.dru-id.com%2Factions%2Fcallback&response_type=code&scope=my-entry-point', $object->getUrlLogin('my-entry-point'));
            $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fwww.foo.bar%2Factions%2Fcallback&response_type=code&scope=my-entry-point&ck_auth_provider=facebook', $object->getUrlLogin('my-entry-point', 'facebook', 'http://www.foo.bar/actions/callback'));
        });

        $this->specify('Checks that URL builder works properly if the endpoint URL is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getEndPoint(Argument::cetera())->will(function(){
                return '';
            });
            $config_proph->getRedirect(Argument::cetera())->will(function() {
                return 'http://examples.dev.dru-id.com/actions/callback';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $object = new UrlBuilder(
                $this->prophet->prophesize(Identity::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertFalse($object->getUrlLogin());
        });

        $this->specify('Checks that URL builder works properly if the redirect URL is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getEndPoint(Argument::cetera())->will(function(){
                return 'https://auth.ci.dru-id.com/oauth2/authorize';
            });
            $config_proph->getRedirect(Argument::cetera())->will(function() {
                return '';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $object = new UrlBuilder(
                $this->prophet->prophesize(Identity::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertFalse($object->getUrlLogin());
        });
    }

    public function testGetUrlRegister ()
    {
        $this->specify('Checks if the register URL is properly generated.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getEndPoint(Argument::cetera())->will(function(){
                return 'https://auth.ci.dru-id.com/oauth2/authorize';
            });
            $config_proph->getRedirect(Argument::cetera())->will(function() {
                return 'http://examples.dev.dru-id.com/actions/callback';
            });
            $config_proph->getRedirect('register', 'http://www.foo.bar/actions/callback')->will(function() {
                return 'http://www.foo.bar/actions/callback';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $object = new UrlBuilder(
                $this->prophet->prophesize(Identity::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fexamples.dev.dru-id.com%2Factions%2Fcallback&response_type=code&x_method=sign_up', $object->getUrlRegister());
            $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fexamples.dev.dru-id.com%2Factions%2Fcallback&response_type=code&x_method=sign_up&scope=my-entry-point', $object->getUrlRegister('my-entry-point'));
            $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fwww.foo.bar%2Factions%2Fcallback&response_type=code&x_method=sign_up&scope=my-entry-point', $object->getUrlRegister('my-entry-point', 'http://www.foo.bar/actions/callback'));
        });

        $this->specify('Checks that URL builder works properly if the endpoint URL is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getEndPoint(Argument::cetera())->will(function(){
                return '';
            });
            $config_proph->getRedirect(Argument::cetera())->will(function() {
                return 'http://examples.dev.dru-id.com/actions/callback';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $object = new UrlBuilder(
                $this->prophet->prophesize(Identity::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertFalse($object->getUrlRegister());
        });
    }

    public function testGetUrlEditAccount ()
    {
        $this->specify('Checks if the edit account URL is properly generated.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getRedirect('postEditAccount', Argument::cetera())->will(function() {
                return 'http://dru-id.foo/postEditAccount';
            });
            $config_proph->getRedirect('postEditAccount', 'http://dru-id.foo/another-callback')->will(function() {
                return 'http://dru-id.foo/another-callback';
            });
            $config_proph->getEndPoint('next_url')->will(function(){
                return 'https://dru-id.foo/next-url';
            });
            $config_proph->getEndPoint('cancel_url')->will(function(){
                return 'https://dru-id.foo/cancel-url';
            });
            $config_proph->getEndPoint('edit_account_endpoint')->will(function() {
                return 'https://dru-id.foo/edit-account-endpoint';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getAccessToken()->will(function(){
                return new AccessToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(Identity::class);
            $identity_proph->getThings()->will(function() use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UrlBuilder(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertEquals('https://dru-id.foo/edit-account-endpoint?next=https%3A%2F%2Fdru-id.foo%2Fnext-url%3Fclient_id%3D111111111111111%26redirect_uri%3Dhttp%253A%252F%252Fdru-id.foo%252FpostEditAccount&cancel_url=https%3A%2F%2Fdru-id.foo%2Fcancel-url%3Fclient_id%3D111111111111111%26redirect_uri%3Dhttp%253A%252F%252Fdru-id.foo%252FpostEditAccount&oauth_token=111111111111111%7C3%7C2.AAAAAAAAAAAAAA.3600.1488275118786%7CAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.', $object->getUrlEditAccount());
            $this->assertEquals('https://dru-id.foo/edit-account-endpoint?next=https%3A%2F%2Fdru-id.foo%2Fnext-url%3Fclient_id%3D111111111111111%26redirect_uri%3Dhttp%253A%252F%252Fdru-id.foo%252FpostEditAccount&cancel_url=https%3A%2F%2Fdru-id.foo%2Fcancel-url%3Fclient_id%3D111111111111111%26redirect_uri%3Dhttp%253A%252F%252Fdru-id.foo%252FpostEditAccount&oauth_token=111111111111111%7C3%7C2.AAAAAAAAAAAAAA.3600.1488275118786%7CAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.&scope=my-entry-point', $object->getUrlEditAccount('my-entry-point'));
            $this->assertEquals('https://dru-id.foo/edit-account-endpoint?next=https%3A%2F%2Fdru-id.foo%2Fnext-url%3Fclient_id%3D111111111111111%26redirect_uri%3Dhttp%253A%252F%252Fdru-id.foo%252Fanother-callback&cancel_url=https%3A%2F%2Fdru-id.foo%2Fcancel-url%3Fclient_id%3D111111111111111%26redirect_uri%3Dhttp%253A%252F%252Fdru-id.foo%252Fanother-callback&oauth_token=111111111111111%7C3%7C2.AAAAAAAAAAAAAA.3600.1488275118786%7CAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.&scope=my-entry-point', $object->getUrlEditAccount('my-entry-point', 'http://dru-id.foo/another-callback'));
        });

        $this->specify('Checks that URL builder works properly if the endpoint URL is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getRedirect('postEditAccount', Argument::cetera())->will(function() {
                return 'http://dru-id.foo/postEditAccount';
            });
            $config_proph->getEndPoint('next_url')->will(function(){
                return 'https://dru-id.foo/next-url';
            });
            $config_proph->getEndPoint('cancel_url')->will(function(){
                return 'https://dru-id.foo/cancel-url';
            });
            $config_proph->getEndPoint('edit_account_endpoint')->will(function() {
                return '';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getAccessToken()->will(function(){
                return new AccessToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(Identity::class);
            $identity_proph->getThings()->will(function() use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UrlBuilder($identity_proph->reveal(), $oauth_proph->reveal(), $this->prophet->prophesize(LoggerInterface::class)->reveal());
            $this->assertFalse($object->getUrlEditAccount());
        });

        $this->specify('Checks that URL builder works properly if next URL is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getRedirect('postEditAccount', Argument::cetera())->will(function() {
                return 'http://dru-id.foo/postEditAccount';
            });
            $config_proph->getEndPoint('next_url')->will(function(){
                return '';
            });
            $config_proph->getEndPoint('cancel_url')->will(function(){
                return 'https://dru-id.foo/cancel-url';
            });
            $config_proph->getEndPoint('edit_account_endpoint')->will(function() {
                return 'https://dru-id.foo/edit-account-endpoint';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getAccessToken()->will(function(){
                return new AccessToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(Identity::class);
            $identity_proph->getThings()->will(function() use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UrlBuilder($identity_proph->reveal(), $oauth_proph->reveal(), $this->prophet->prophesize(LoggerInterface::class)->reveal());
            $this->assertFalse($object->getUrlEditAccount());
        });

        $this->specify('Checks that URL builder works properly if cancel URL is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getRedirect('postEditAccount', Argument::cetera())->will(function() {
                return 'http://dru-id.foo/postEditAccount';
            });
            $config_proph->getEndPoint('next_url')->will(function(){
                return 'https://dru-id.foo/next-url';
            });
            $config_proph->getEndPoint('cancel_url')->will(function(){
                return '';
            });
            $config_proph->getEndPoint('edit_account_endpoint')->will(function() {
                return 'https://dru-id.foo/edit-account-endpoint';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getAccessToken()->will(function(){
                return new AccessToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(Identity::class);
            $identity_proph->getThings()->will(function() use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UrlBuilder($identity_proph->reveal(), $oauth_proph->reveal(), $this->prophet->prophesize(LoggerInterface::class)->reveal());
            $this->assertFalse($object->getUrlEditAccount());
        });

        $this->specify('Checks that URL builder works properly if access_token is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getRedirect('postEditAccount', Argument::cetera())->will(function() {
                return 'http://dru-id.foo/postEditAccount';
            });
            $config_proph->getEndPoint('next_url')->will(function(){
                return 'https://dru-id.foo/next-url';
            });
            $config_proph->getEndPoint('cancel_url')->will(function(){
                return 'https://dru-id.foo/cancel-url';
            });
            $config_proph->getEndPoint('edit_account_endpoint')->will(function() {
                return 'https://dru-id.foo/edit-account-endpoint';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getAccessToken()->will(function(){
                return null;
            });
            $identity_proph = $this->prophet->prophesize(Identity::class);
            $identity_proph->getThings()->will(function() use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UrlBuilder($identity_proph->reveal(), $oauth_proph->reveal(), $this->prophet->prophesize(LoggerInterface::class)->reveal());
            $this->assertFalse($object->getUrlEditAccount());
        });
    }

    public function testGetUrlCompleteAccount ()
    {
        $this->specify('Checks if the edit account URL is properly generated.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getRedirect('postEditAccount', Argument::cetera())->will(function() {
                return 'http://dru-id.foo/postEditAccount';
            });
            $config_proph->getRedirect('postEditAccount', 'http://dru-id.foo/another-callback')->will(function() {
                return 'http://dru-id.foo/another-callback';
            });
            $config_proph->getEndPoint('next_url')->will(function(){
                return 'https://dru-id.foo/next-url';
            });
            $config_proph->getEndPoint('cancel_url')->will(function(){
                return 'https://dru-id.foo/cancel-url';
            });
            $config_proph->getEndPoint('complete_account_endpoint')->will(function() {
                return 'https://dru-id.foo/complete-account-endpoint';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getAccessToken()->will(function(){
                return new AccessToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(Identity::class);
            $identity_proph->getThings()->will(function() use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UrlBuilder(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
//            $this->assertFalse('https://dru-id.foo/complete-account-endpoint?next=https%3A%2F%2Fdru-id.foo%2Fnext-url%3Fclient_id%3D111111111111111%26redirect_uri%3Dhttp%253A%252F%252Fdru-id.foo%252FpostEditAccount&cancel_url=https%3A%2F%2Fdru-id.foo%2Fcancel-url%3Fclient_id%3D111111111111111%26redirect_uri%3Dhttp%253A%252F%252Fdru-id.foo%252FpostEditAccount&oauth_token=111111111111111%7C3%7C2.AAAAAAAAAAAAAA.3600.1488275118786%7CAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.', $object->getUrlCompleteAccount());
            $this->assertFalse($object->getUrlCompleteAccount());
            $this->assertEquals('https://dru-id.foo/complete-account-endpoint?next=https%3A%2F%2Fdru-id.foo%2Fnext-url%3Fclient_id%3D111111111111111%26redirect_uri%3Dhttp%253A%252F%252Fdru-id.foo%252FpostEditAccount&cancel_url=https%3A%2F%2Fdru-id.foo%2Fcancel-url%3Fclient_id%3D111111111111111%26redirect_uri%3Dhttp%253A%252F%252Fdru-id.foo%252FpostEditAccount&oauth_token=111111111111111%7C3%7C2.AAAAAAAAAAAAAA.3600.1488275118786%7CAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.&scope=my-entry-point', $object->getUrlCompleteAccount('my-entry-point'));
        });

        $this->specify('Checks that URL builder works properly if the endpoint URL is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getRedirect('postEditAccount', Argument::cetera())->will(function() {
                return 'http://dru-id.foo/postEditAccount';
            });
            $config_proph->getEndPoint('next_url')->will(function(){
                return 'https://dru-id.foo/next-url';
            });
            $config_proph->getEndPoint('cancel_url')->will(function(){
                return 'https://dru-id.foo/cancel-url';
            });
            $config_proph->getEndPoint('complete_account_endpoint')->will(function() {
                return '';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getAccessToken()->will(function(){
                return new AccessToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(Identity::class);
            $identity_proph->getThings()->will(function() use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UrlBuilder($identity_proph->reveal(), $oauth_proph->reveal(), $this->prophet->prophesize(LoggerInterface::class)->reveal());
            $this->assertFalse($object->getUrlCompleteAccount('my-entry-point'));
        });

        $this->specify('Checks that URL builder works properly if next URL is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getRedirect('postEditAccount', Argument::cetera())->will(function() {
                return 'http://dru-id.foo/postEditAccount';
            });
            $config_proph->getEndPoint('next_url')->will(function(){
                return '';
            });
            $config_proph->getEndPoint('cancel_url')->will(function(){
                return 'https://dru-id.foo/cancel-url';
            });
            $config_proph->getEndPoint('complete_account_endpoint')->will(function() {
                return 'https://dru-id.foo/complete-account-endpoint';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getAccessToken()->will(function(){
                return new AccessToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(Identity::class);
            $identity_proph->getThings()->will(function() use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UrlBuilder($identity_proph->reveal(), $oauth_proph->reveal(), $this->prophet->prophesize(LoggerInterface::class)->reveal());
            $this->assertFalse($object->getUrlCompleteAccount('my-entry-point'));
        });

        $this->specify('Checks that URL builder works properly if cancel URL is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getRedirect('postEditAccount', Argument::cetera())->will(function() {
                return 'http://dru-id.foo/postEditAccount';
            });
            $config_proph->getEndPoint('next_url')->will(function(){
                return 'https://dru-id.foo/next-url';
            });
            $config_proph->getEndPoint('cancel_url')->will(function(){
                return '';
            });
            $config_proph->getEndPoint('complete_account_endpoint')->will(function() {
                return 'https://dru-id.foo/complete-account-endpoint';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getAccessToken()->will(function(){
                return new AccessToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(Identity::class);
            $identity_proph->getThings()->will(function() use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UrlBuilder($identity_proph->reveal(), $oauth_proph->reveal(), $this->prophet->prophesize(LoggerInterface::class)->reveal());
            $this->assertFalse($object->getUrlCompleteAccount('my-entry-point'));
        });

        $this->specify('Checks that URL builder works properly if access_token is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getRedirect('postEditAccount', Argument::cetera())->will(function() {
                return 'http://dru-id.foo/postEditAccount';
            });
            $config_proph->getEndPoint('next_url')->will(function(){
                return 'https://dru-id.foo/next-url';
            });
            $config_proph->getEndPoint('cancel_url')->will(function(){
                return 'https://dru-id.foo/cancel-url';
            });
            $config_proph->getEndPoint('complete_account_endpoint')->will(function() {
                return 'https://dru-id.foo/complete-account-endpoint';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getAccessToken()->will(function(){
                return null;
            });
            $identity_proph = $this->prophet->prophesize(Identity::class);
            $identity_proph->getThings()->will(function() use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UrlBuilder($identity_proph->reveal(), $oauth_proph->reveal(), $this->prophet->prophesize(LoggerInterface::class)->reveal());
            $this->assertFalse($object->getUrlCompleteAccount('my-entry-point'));
        });
    }
}
