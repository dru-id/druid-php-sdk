<?php
namespace UnitTest\Genetsis\DruID\UrlBuilder;

use Codeception\Specify;
use Genetsis\DruID\Core\OAuth\Beans\AccessToken;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\DruID\Core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\DruID\Core\User\Beans\Things;
use Genetsis\DruID\Identity\Identity;
use Genetsis\DruID\UrlBuilder\UrlBuilder;
use Genetsis\DruID\UserApi\UserApi;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophet;
use Psr\Log\LoggerInterface;

/**
 * @package UnitTest\Genetsis\DruID\UrlBuilder
 * @category UnitTest
 * @author Ismael Salgado <ismael.salgado@genetsis.com>
 */
class UrlBuilderTest extends TestCase
{
    use Specify;
    
    /** @var Prophet $prophet */
    private $prophet;
    
    protected function setUp()
    {
        $this->prophet = new Prophet();
    }

    public function testConstructor ()
    {
        $this->specify('Checks constructor.', function() {
            $object = new UrlBuilder(
                $this->prophet->prophesize(Identity::class)->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
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
                return 'https://dru-id.foo/oauth2/authorize';
            });
            $config_proph->getRedirect(Argument::cetera())->will(function() {
                return 'http://dru-id.foo/actions/callback';
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
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertEquals('https://dru-id.foo/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fdru-id.foo%2Factions%2Fcallback&response_type=code', $object->getUrlLogin());
            $this->assertEquals('https://dru-id.foo/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fdru-id.foo%2Factions%2Fcallback&response_type=code&scope=my-entry-point', $object->getUrlLogin('my-entry-point'));
            $this->assertEquals('https://dru-id.foo/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fwww.foo.bar%2Factions%2Fcallback&response_type=code&scope=my-entry-point&ck_auth_provider=facebook', $object->getUrlLogin('my-entry-point', 'facebook', 'http://www.foo.bar/actions/callback'));
            $this->assertEquals('https://dru-id.foo/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fdru-id.foo%2Factions%2Fcallback&response_type=code&x_prefill=eyJvYmplY3RUeXBlIjoidXNlciIsImlkcyI6eyJlbWFpbCI6eyJ2YWx1ZSI6ImZvb0BiYXIuY29tIn19fQ%3D%3D', $object->getUrlLogin(null, null, null, ['email' => 'foo@bar.com']));
        });

        $this->specify('Checks that URL builder works properly if the endpoint URL is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getEndPoint(Argument::cetera())->will(function(){
                return '';
            });
            $config_proph->getRedirect(Argument::cetera())->will(function() {
                return 'http://dru-id.foo/actions/callback';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $object = new UrlBuilder(
                $this->prophet->prophesize(Identity::class)->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertFalse($object->getUrlLogin());
        });

        $this->specify('Checks that URL builder works properly if the redirect URL is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getEndPoint(Argument::cetera())->will(function(){
                return 'https://dru-id.foo/oauth2/authorize';
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
                $this->prophet->prophesize(UserApi::class)->reveal(),
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
                return 'https://dru-id.foo/oauth2/authorize';
            });
            $config_proph->getRedirect(Argument::cetera())->will(function() {
                return 'http://dru-id.foo/actions/callback';
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
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertEquals('https://dru-id.foo/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fdru-id.foo%2Factions%2Fcallback&response_type=code&x_method=sign_up', $object->getUrlRegister());
            $this->assertEquals('https://dru-id.foo/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fdru-id.foo%2Factions%2Fcallback&response_type=code&x_method=sign_up&scope=my-entry-point', $object->getUrlRegister('my-entry-point'));
            $this->assertEquals('https://dru-id.foo/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fwww.foo.bar%2Factions%2Fcallback&response_type=code&x_method=sign_up&scope=my-entry-point', $object->getUrlRegister('my-entry-point', 'http://www.foo.bar/actions/callback'));
            $this->assertEquals('https://dru-id.foo/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fdru-id.foo%2Factions%2Fcallback&response_type=code&x_method=sign_up&x_prefill=eyJvYmplY3RUeXBlIjoidXNlciIsImlkcyI6eyJlbWFpbCI6eyJ2YWx1ZSI6ImZvb0BiYXIuY29tIn19fQ%3D%3D', $object->getUrlRegister(null, null, ['email' => 'foo@bar.com']));
        });

        $this->specify('Checks that URL builder works properly if the endpoint URL is empty.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getEndPoint(Argument::cetera())->will(function(){
                return '';
            });
            $config_proph->getRedirect(Argument::cetera())->will(function() {
                return 'http://dru-id.foo/actions/callback';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $object = new UrlBuilder(
                $this->prophet->prophesize(Identity::class)->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
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
                $this->prophet->prophesize(UserApi::class)->reveal(),
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

            $object = new UrlBuilder(
                $identity_proph->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
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

            $object = new UrlBuilder(
                $identity_proph->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
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

            $object = new UrlBuilder(
                $identity_proph->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
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

            $object = new UrlBuilder(
                $identity_proph->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertFalse($object->getUrlEditAccount());
        });
    }

    public function testGetUrlCompleteAccount ()
    {
        $this->specify('Checks if the complete account URL is properly generated.', function() {
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
                $this->prophet->prophesize(UserApi::class)->reveal(),
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

            $object = new UrlBuilder(
                $identity_proph->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
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

            $object = new UrlBuilder(
                $identity_proph->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
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

            $object = new UrlBuilder(
                $identity_proph->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
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

            $object = new UrlBuilder(
                $identity_proph->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertFalse($object->getUrlCompleteAccount('my-entry-point'));
        });
    }

    public function testBuildSignupPromotionUrl ()
    {
        $this->specify('Checks if the build signup URL is properly generated if user is not connected.', function() {
            $config_proph = $this->prophet->prophesize(Config::class);
            $config_proph->getClientId()->will(function(){
                return '111111111111111';
            });
            $config_proph->getEndPoint(Argument::cetera())->will(function(){
                return 'https://dru-id.foo/oauth2/authorize';
            });
            $config_proph->getRedirect(Argument::cetera())->will(function() {
                return 'http://dru-id.foo/actions/callback';
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig(Argument::cetera())->will(function() use ($config_proph) {
                return $config_proph->reveal();
            });

            $identity_proph = $this->prophet->prophesize(Identity::class);
            $identity_proph->isConnected()->will(function(){
                return false;
            });

            $object = new UrlBuilder(
                $identity_proph->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertEquals('https://dru-id.foo/oauth2/authorize?client_id=111111111111111&redirect_uri=http%3A%2F%2Fdru-id.foo%2Factions%2Fcallback&response_type=code&scope=my-entry-point', $object->buildSignupPromotionUrl('my-entry-point'));
        });

        $this->specify('Checks if the build signup URL is properly generated if user is connected.', function() {
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
            $identity_proph->isConnected()->will(function(){
                return true;
            });

            $user_api_proph = $this->prophet->prophesize(UserApi::class);
            $user_api_proph->checkUserComplete(Argument::cetera())->will(function(){
                return false;
            });

            $object = new UrlBuilder(
                $identity_proph->reveal(),
                $user_api_proph->reveal(),
                $oauth_proph->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertEquals('https://dru-id.foo/complete-account-endpoint?next=https%3A%2F%2Fdru-id.foo%2Fnext-url%3Fclient_id%3D111111111111111%26redirect_uri%3Dhttp%253A%252F%252Fdru-id.foo%252FpostEditAccount&cancel_url=https%3A%2F%2Fdru-id.foo%2Fcancel-url%3Fclient_id%3D111111111111111%26redirect_uri%3Dhttp%253A%252F%252Fdru-id.foo%252FpostEditAccount&oauth_token=111111111111111%7C3%7C2.AAAAAAAAAAAAAA.3600.1488275118786%7CAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.&scope=my-entry-point', $object->buildSignupPromotionUrl('my-entry-point'));
        });

        $this->specify('Checks that URL builder works properly if entry_point is empty.', function() {
            $object = new UrlBuilder(
                $this->prophet->prophesize(Identity::class)->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $this->prophet->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertFalse($object->buildSignupPromotionUrl(''));
        });
    }
    
    public function testArrayToUserJson ()
    {
        $this->specify('Checks if json data is properly built.', function() {
            $object = new UrlBuilder(
                $this->prophet->prophesize(Identity::class)->reveal(),
                $this->prophet->prophesize(UserApi::class)->reveal(),
                $this->prophet->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal()
            );
            $this->assertEquals(json_encode(['objectType' => 'user']), callMethod($object, 'arrayToUserJson', [[]]));
            $this->assertEquals(
                json_encode([
                    'objectType' => 'user',
                    'datas' => [
                        'foo' => [
                            'value' => 'bar'
                        ]
                    ]
                ])
                , callMethod($object, 'arrayToUserJson', [['foo' => 'bar']])
            );
            $this->assertEquals(
                json_encode([
                    'objectType' => 'user',
                    'ids' => [
                        'email' => [
                            'value' => 'foo@bar.com'
                        ],
                        'screen_name' => [
                            'value' => 'foo'
                        ],
                        'national_id' => [
                            'value' => '12345678Z'
                        ],
                        'phone_number' => [
                            'value' => '665454545'
                         ]
                    ],
                    'location' => [
                        'telephone' => '916543212',
                        'address' => [
                            'streetAddress' => 'Av. Foo',
                            'locality' => 'Foo City',
                            'region' => 'Foo Region',
                            'postalCode' => '11122',
                            'country' => 'Foo Land'
                        ]
                    ],
                ])
                ,callMethod($object, 'arrayToUserJson', [[
                    'email' => 'foo@bar.com',
                    'screen_name' => 'foo',
                    'national_id' => '12345678Z',
                    'phone_number' => '665454545',
                    'telephone' => '916543212',
                    'streetAddress' => 'Av. Foo',
                    'locality' => 'Foo City',
                    'region' => 'Foo Region',
                    'postalCode' => '11122',
                    'country' => 'Foo Land'
                ]])
            );
        });
    }
}
