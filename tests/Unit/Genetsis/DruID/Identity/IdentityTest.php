<?php
namespace Genetsis\DruID\UnitTest\Identity;

use Codeception\Specify;
use Doctrine\Common\Cache\CacheProvider;
use Genetsis\DruID\Core\Http\Contracts\CookiesServiceInterface;
use Genetsis\DruID\Core\Http\Contracts\SessionServiceInterface;
use Genetsis\DruID\Core\OAuth\Beans\AccessToken;
use Genetsis\DruID\Core\OAuth\OAuth;
use Genetsis\DruID\Core\User\Beans\Things;
use Genetsis\DruID\Identity\Identity;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophet;
use Psr\Log\LoggerInterface;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class IdentityTest extends TestCase
{
    use Specify;

    /** @var Prophet $prophet */
    protected $prophet;

    protected function setUp()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->prophet = new Prophet();
    }

    public function testConstructor()
    {
        $this->specify('Checks constructor.', function(){
            $identity = new Identity(
                $this->prophesize(OAuth::class)->reveal(),
                $this->prophesize(SessionServiceInterface::class)->reveal(),
                $this->prophesize(CookiesServiceInterface::class)->reveal(),
                getDefaultLoggerService('druid-identity'),
                getDefaultCacheService()
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
                getDefaultLoggerService('druid-identity'),
                getDefaultCacheService()
            );
        });
    }
}
