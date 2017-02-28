<?php
namespace UnitTest\Genetsis\DruID\UserApi;

use Codeception\Specify;
use Codeception\Test\Unit;
use Doctrine\Common\Cache\Cache;
use Genetsis\DruID\Core\Http\Contracts\HttpServiceInterface;
use Genetsis\DruID\Core\OAuth\Beans\ClientToken;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Api;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\DruID\Core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\DruID\Core\User\Beans\LoginStatus;
use Genetsis\DruID\Core\User\Beans\Things;
use Genetsis\DruID\Core\User\Collections\LoginStatusTypes;
use Genetsis\DruID\Identity\Contracts\IdentityServiceInterface;
use Genetsis\DruID\UserApi\UserApi;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use Prophecy\Prophet;
use Psr\Log\LoggerInterface;

/**
 * @package UnitTest\Genetsis\DruID\UserApi
 * @category UnitTest
 * @author Ismael Salgado <ismael.salgado@genetsis.com>
 */
class UserApiTest extends Unit
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

    public function testConstruct ()
    {
        $this->specify('Checks constructor.', function() {
            $object = new UserApi(
                $this->prophet->prophesize(IdentityServiceInterface::class)->reveal(),
                $this->prophet->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophet->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal(),
                $this->prophet->prophesize(Cache::class)->reveal()
            );
            $this->assertInstanceOf(IdentityServiceInterface::class, getProperty($object, 'identity'));
            $this->assertInstanceOf(OAuthServiceInterface::class, getProperty($object, 'oauth'));
            $this->assertInstanceOf(HttpServiceInterface::class, getProperty($object, 'http'));
            $this->assertInstanceOf(LoggerInterface::class, getProperty($object, 'logger'));
            $this->assertInstanceOf(Cache::class, getProperty($object, 'cache'));
        });
    }
    
    public function testGetUsers ()
    {
        $this->specify('Checks that we can get a set of users data from its identifiers.', function() {
            $cache_proph = $this->prophet->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $http = $this->prophet->prophesize(HttpServiceInterface::class);
            $http->request('POST', 'http://dru-id.foo/user', Argument::withEntry('form_params', [
                'oauth_token' => '111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.',
                's' => '*',
                'f' => 'User',
                'w.0' => 1,
                'w.1' => 2
            ]))->will(function(){
                return new Response(200, [], '{"count": 2, "data": [{"id": "1"}, {"id": "2"}]}');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getUsers('foo'));
            $this->assertCount(2, $object->getUsers([1, 2]));
        });

        $this->specify('Checks if works properly if client_token is not provided.', function() {
            $cache_proph = $this->prophet->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return null;
            });
            $identity_proph = $this->prophet->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophet->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophet->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getUsers([1, 2]));
        });

        $this->specify('Checks if works properly if "remote" server responds with a status code different to 200.', function() {
            $cache_proph = $this->prophet->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $http = $this->prophet->prophesize(HttpServiceInterface::class);
            $http->request('POST', 'http://dru-id.foo/user', Argument::withEntry('form_params', [
                'oauth_token' => '111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.',
                's' => '*',
                'f' => 'User',
                'w.0' => 1,
                'w.1' => 2
            ]))->will(function(){
                return new Response(404, [], '{"count": 2, "data": [{"id": "1"}, {"id": "2"}]}');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getUsers([1, 2]));
        });

        $this->specify('Checks if works properly with an invalid JSON string.', function() {
            $cache_proph = $this->prophet->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $http = $this->prophet->prophesize(HttpServiceInterface::class);
            $http->request('POST', 'http://dru-id.foo/user', Argument::withEntry('form_params', [
                'oauth_token' => '111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.',
                's' => '*',
                'f' => 'User',
                'w.0' => 1,
                'w.1' => 2
            ]))->will(function(){
                return new Response(200, [], '{{}');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getUsers([1, 2]));
        });

        $this->specify('Checks if works properly with a valid JSON string without "data" field.', function() {
            $cache_proph = $this->prophet->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $http = $this->prophet->prophesize(HttpServiceInterface::class);
            $http->request('POST', 'http://dru-id.foo/user', Argument::withEntry('form_params', [
                'oauth_token' => '111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.',
                's' => '*',
                'f' => 'User',
                'w.0' => 1,
                'w.1' => 2
            ]))->will(function(){
                return new Response(200, [], '{}');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getUsers([1, 2]));
        });

        $this->specify('Checks if works properly with a valid JSON string with "count" property equals to 0.', function() {
            $cache_proph = $this->prophet->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophet->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophet->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophet->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $http = $this->prophet->prophesize(HttpServiceInterface::class);
            $http->request('POST', 'http://dru-id.foo/user', Argument::withEntry('form_params', [
                'oauth_token' => '111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.',
                's' => '*',
                'f' => 'User',
                'w.0' => 1,
                'w.1' => 2
            ]))->will(function(){
                return new Response(200, [], '{"data": [], "count": 0}');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophet->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getUsers([1, 2]));
        });

        $this->specify('Checks if returns data stored from cache.', function() {
            $cache_proph = $this->prophet->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return true;
            });
            $cache_proph->fetch(Argument::cetera())->will(function(){
                return [['id' => 'foo']];
            });

            $object = new UserApi(
                $this->prophet->prophesize(IdentityServiceInterface::class)->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(1, $object->getUsers([1, 2]));
        });
    }

    public function testGetUserLogged ()
    {
        $this->specify('Checks that returns a valid user data.', function() {
//            $things_proph = $this->prophet->prophesize(Things::class);
//            $things_proph->getLoginStatus()->will(function(){
//                return (new LoginStatus())
//                    ->setCkusid('123')
//                    ->setOid('abc')
//                    ->setConnectState(LoginStatusTypes::CONNECTED);
//            });
//            $things_proph->getClientToken()->will(function(){
//                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
//            });
//
//            $identity_proph = $this->prophet->prophesize(IdentityServiceInterface::class);
//            $identity_proph->getThings()->will(function() use ($things_proph){
//                return $things_proph->reveal();
//            });


        });
    }
}

