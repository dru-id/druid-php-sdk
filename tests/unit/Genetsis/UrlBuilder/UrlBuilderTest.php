<?php
namespace UnitTest\Genetsis\UrlBuilder;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\Core\OAuth\Contracts\OAuthServiceInterface;
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
                return '231705665113870';
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
            $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize?client_id=231705665113870&redirect_uri=http%3A%2F%2Fexamples.dev.dru-id.com%2Factions%2Fcallback&response_type=code', $object->getUrlLogin());
            $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize?client_id=231705665113870&redirect_uri=http%3A%2F%2Fexamples.dev.dru-id.com%2Factions%2Fcallback&response_type=code&scope=my-entry-point', $object->getUrlLogin('my-entry-point'));
            $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize?client_id=231705665113870&redirect_uri=http%3A%2F%2Fwww.foo.bar%2Factions%2Fcallback&response_type=code&scope=my-entry-point&ck_auth_provider=facebook', $object->getUrlLogin('my-entry-point', 'facebook', 'http://www.foo.bar/actions/callback'));
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
                return '231705665113870';
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
            $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize?client_id=231705665113870&redirect_uri=http%3A%2F%2Fexamples.dev.dru-id.com%2Factions%2Fcallback&response_type=code&x_method=sign_up', $object->getUrlRegister());
            $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize?client_id=231705665113870&redirect_uri=http%3A%2F%2Fexamples.dev.dru-id.com%2Factions%2Fcallback&response_type=code&x_method=sign_up&scope=my-entry-point', $object->getUrlRegister('my-entry-point'));
            $this->assertEquals('https://auth.ci.dru-id.com/oauth2/authorize?client_id=231705665113870&redirect_uri=http%3A%2F%2Fwww.foo.bar%2Factions%2Fcallback&response_type=code&x_method=sign_up&scope=my-entry-point', $object->getUrlRegister('my-entry-point', 'http://www.foo.bar/actions/callback'));
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
}
