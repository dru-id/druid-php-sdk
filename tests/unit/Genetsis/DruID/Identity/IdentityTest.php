<?php
namespace Genetsis\DruID\UnitTest\Identity;

use Codeception\Specify;
use Codeception\Test\Unit;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\VoidCache;
use Genetsis\DruID\Core\Config\Beans\Config;
use Genetsis\DruID\Core\Http\Contracts\CookiesServiceInterface;
use Genetsis\DruID\Core\Http\Contracts\SessionServiceInterface;
use Genetsis\DruID\Core\Http\Cookies;
use Genetsis\DruID\Core\OAuth\Beans\AccessToken;
use Genetsis\DruID\Core\OAuth\OAuth;
use Genetsis\DruID\Core\OAuth\OAuthConfigFactory;
use Genetsis\DruID\Core\User\Beans\Things;
use Genetsis\DruID\DruID;
use Genetsis\DruID\Identity\Identity;
use Genetsis\DruID\UserApi\UserApi;
use Prophecy\Argument;
use Prophecy\Prophet;
use Psr\Log\LoggerInterface;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class IdentityTest extends Unit
{
    use Specify;

    /** @var \UnitTester */
    protected $tester;
    /** @var Prophet $prophet */
    protected $prophet;
    /** @var DruID $druid */
    protected $druid;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->prophet = new Prophet();
        $user_api_prophecy = $this->prophet->prophesize(UserApi::class);
        $druid_prophecy = $this->prophet->prophesize(DruID::class);
        $druid_prophecy->userApi()->will(function() use ($user_api_prophecy) {
            return $user_api_prophecy->reveal();
        });
        $this->druid = $druid_prophecy->reveal();
    }

    protected function _after()
    {
    }

    public function testConstructor()
    {
        $this->specify('Checks constructor.', function(){
            $identity = new Identity(
                $this->prophet->prophesize(OAuth::class)->reveal(),
                $this->prophet->prophesize(SessionServiceInterface::class)->reveal(),
                $this->prophet->prophesize(CookiesServiceInterface::class)->reveal(),
                $this->getDefaultLoggerService(),
                $this->getDefaultCacheService()
            );
            $this->assertInstanceOf(OAuth::class, getProperty($identity, 'oauth'));
            $this->assertInstanceOf(SessionServiceInterface::class, getProperty($identity, 'session'));
            $this->assertInstanceOf(CookiesServiceInterface::class, getProperty($identity, 'cookie'));
            $this->assertInstanceOf(LoggerInterface::class, getProperty($identity, 'logger'));
            $this->assertInstanceOf(CacheProvider::class, getProperty($identity, 'cache'));
            $this->assertInstanceOf(Things::class, getProperty($identity, 'gid_things'));
        });
    }

    public function testIsConnected()
    {
        $this->specify('Checks is connected property.', function(){
            $session_prophecy = $this->prophet->prophesize(SessionServiceInterface::class);
            $session_prophecy->has(Argument::is('things'))->will(function(){
                return true;
            });
            $access_token_prophecy = $this->prophet->prophesize(AccessToken::class);
//            $access_token_prophecy->
            $session_prophecy->get(Argument::is('things'))->will(function(){
                return serialize(new Things());
            });
            $identity = new Identity(
                $this->prophet->prophesize(OAuth::class)->reveal(),
                $session_prophecy->reveal(),
                $this->prophet->prophesize(CookiesServiceInterface::class)->reveal(),
                $this->getDefaultLoggerService(),
                $this->getDefaultCacheService()
            );
        });
    }

    private function getDefaultLoggerService()
    {
        return getSyslogLogger('druid-facade-test');
    }

    private function getDefaultCacheService()
    {
        return new VoidCache();
    }
}
