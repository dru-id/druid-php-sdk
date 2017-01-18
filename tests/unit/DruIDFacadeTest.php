<?php
namespace Genetsis\UnitTest;

use Codeception\Specify;
use Codeception\Test\Unit;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\VoidCache;
use Genetsis\core\Config\Beans\Cache\File;
use Genetsis\core\Config\Beans\Config;
use Genetsis\core\Http\Services\Cookies;
use Genetsis\core\Http\Services\Http;
use Genetsis\core\Http\Services\Session;
use Genetsis\core\OAuth\Services\OAuth;
use Genetsis\DruID;
use Genetsis\Identity\Services\Identity;
use Genetsis\Opi\Services\Opi;
use Genetsis\UrlBuilder\Services\UrlBuilder;
use Genetsis\UserApi\Services\UserApi;
use Monolog\Logger;

/**
 * @package  DruID
 * @category UnitTest
 */
class DruIDFacadeTest extends Unit
{
    use Specify;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testErrorWhenNotInitialized()
    {
        $this->specify('Checks if an exception is thrown calling "identity" if the library is not initialized.', function () {
            DruID::identity();
        }, ['throws' => \Exception::class]);

        $this->specify('Checks if an exception is thrown calling "urlBuilder" if the library is not initialized.', function () {
            DruID::urlBuilder();
        }, ['throws' => \Exception::class]);

        $this->specify('Checks if an exception is thrown calling "userApi" if the library is not initialized.', function () {
            DruID::userApi();
        }, ['throws' => \Exception::class]);

        $this->specify('Checks if an exception is thrown calling "opi" if the library is not initialized.', function () {
            DruID::opi();
        }, ['throws' => \Exception::class]);

        $this->specify('Checks if an exception is thrown calling "init" if the library is not initialized.', function () {
            DruID::init();
        }, ['throws' => \Exception::class]);
    }

    public function testServiceWithAnInvalidOAuthConfXml()
    {
        $this->specify('Checks if the library throws an exception with an oauthconf file with wrong version.', function () {
            DruID::setup(new Config('www.foo.com'), file_get_contents(OAUTHCONFIG_SAMPLE_XML_WRONG_VERSION), getSyslogLogger('druid-facade-test'), new VoidCache());
        }, ['throws' => \Exception::class]);
    }

    public function testService()
    {
        $this->specify('Checks if the library is properly initialized.', function () {
            DruID::setup(new Config('www.foo.com'), file_get_contents(OAUTHCONFIG_SAMPLE_XML_1_4), getSyslogLogger('druid-facade-test'), new VoidCache());
            DruID::init();
            $this->assertTrue(getStaticProperty(DruID::class, 'setup_done'));
            $this->assertInstanceOf(Identity::class, DruID::identity());
            $this->assertInstanceOf(UrlBuilder::class, DruID::urlBuilder());
            $this->assertInstanceOf(UserApi::class, DruID::userApi());
            $this->assertInstanceOf(Opi::class, DruID::opi());
            $this->assertInstanceOf(Config::class, getStaticProperty(DruID::class, 'config'));
            $this->assertInstanceOf(OAuth::class, getStaticProperty(DruID::class, 'oauth'));
            $this->assertInstanceOf(Http::class, getStaticProperty(DruID::class, 'http'));
            $this->assertInstanceOf(Session::class, getStaticProperty(DruID::class, 'session'));
            $this->assertInstanceOf(Cookies::class, getStaticProperty(DruID::class, 'cookies'));
            $this->assertInstanceOf(Logger::class, getStaticProperty(DruID::class, 'logger'));
            $this->assertInstanceOf(Cache::class, getStaticProperty(DruID::class, 'cache'));
        });
    }
}
