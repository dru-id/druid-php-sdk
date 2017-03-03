<?php
namespace UnitTest\Genetsis\DruID\UserApi;

use Codeception\Specify;
use Doctrine\Common\Cache\Cache;
use Genetsis\DruID\Core\Http\Contracts\HttpServiceInterface;
use Genetsis\DruID\Core\Http\Exceptions\RequestException;
use Genetsis\DruID\Core\OAuth\Beans\AccessToken;
use Genetsis\DruID\Core\OAuth\Beans\ClientToken;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Api;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\EndPoint;
use Genetsis\DruID\Core\OAuth\Beans\RefreshToken;
use Genetsis\DruID\Core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\DruID\Core\User\Beans\Brand;
use Genetsis\DruID\Core\User\Beans\LoginStatus;
use Genetsis\DruID\Core\User\Beans\Things;
use Genetsis\DruID\Core\User\Collections\LoginStatusTypes;
use Genetsis\DruID\Identity\Contracts\IdentityServiceInterface;
use Genetsis\DruID\UserApi\UserApi;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophet;
use Psr\Log\LoggerInterface;

/**
 * @package UnitTest\Genetsis\DruID\UserApi
 * @category UnitTest
 * @author Ismael Salgado <ismael.salgado@genetsis.com>
 */
class UserApiTest extends TestCase
{
    use Specify;
    
    /** @var Prophet $prophet */
    private $prophet;

    protected function setUp()
    {
        $this->prophet = new Prophet();
    }

    public function testConstruct ()
    {
        $this->specify('Checks constructor.', function() {
            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
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
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
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
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getUsers('foo'));
            $this->assertCount(2, $object->getUsers([1, 2]));
        });

        $this->specify('Checks if works properly if client_token is not provided.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return null;
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getUsers([1, 2]));
        });

        $this->specify('Checks if works properly if "remote" server responds with a status code different to 200.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
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
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getUsers([1, 2]));
        });

        $this->specify('Checks if works properly with an invalid JSON string.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
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
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getUsers([1, 2]));
        });

        $this->specify('Checks if works properly with a valid JSON string without "data" field.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
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
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getUsers([1, 2]));
        });

        $this->specify('Checks if works properly with a valid JSON string with "count" property equals to 0.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
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
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getUsers([1, 2]));
        });

        $this->specify('Checks if returns data stored from cache.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return true;
            });
            $cache_proph->fetch(Argument::cetera())->will(function(){
                return [['id' => 'foo']];
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
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
        $this->specify('Checks that returns data from a logged user.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function(){
                return (new LoginStatus())
                    ->setCkusid('123')
                    ->setOid('abc')
                    ->setConnectState(LoginStatusTypes::CONNECTED);
            });
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('POST', 'http://dru-id.foo/user', Argument::withEntry('form_params', [
                'oauth_token' => '111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.',
                's' => '*',
                'f' => 'User',
                'w.id' => 123
            ]))->will(function(){
                return new Response(200, [], '{"count": 1, "data": [{"id": "1"}, {"id": "2"}]}');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertInstanceOf(\stdClass::class, $object->getUserLogged());
        });

        $this->specify('Checks if there is no data returned by remote server.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function(){
                return (new LoginStatus())
                    ->setCkusid('123')
                    ->setOid('abc')
                    ->setConnectState(LoginStatusTypes::CONNECTED);
            });
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('POST', 'http://dru-id.foo/user', Argument::withEntry('form_params', [
                'oauth_token' => '111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.',
                's' => '*',
                'f' => 'User',
                'w.id' => 123
            ]))->will(function(){
                return new Response(200, [], '{"count": 0, "data": []}');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertNull($object->getUserLogged());
        });

        $this->specify('Checks if there is no login status.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function(){
                return null;
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertNull($object->getUserLogged());
        });

        $this->specify('Checks if there is no user logged in.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function(){
                return (new LoginStatus())
                    ->setCkusid('123')
                    ->setOid('abc')
                    ->setConnectState(LoginStatusTypes::NOT_CONNECTED);
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertNull($object->getUserLogged());
        });
    }

    public function testGetUserLoggedCkusid ()
    {
        $this->specify('Checks if returns ckusid', function() {
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function(){
                return (new LoginStatus())
                    ->setCkusid('123')
                    ->setOid('abc')
                    ->setConnectState(LoginStatusTypes::CONNECTED);
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertEquals('123', $object->getUserLoggedCkusid());
        });

        $this->specify('Checks if there is no login status.', function() {
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function () {
                return null;
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function () use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertNull($object->getUserLoggedCkusid());
        });

        $this->specify('Checks if there is no user connected.', function() {
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function(){
                return (new LoginStatus())
                    ->setCkusid('123')
                    ->setOid('abc')
                    ->setConnectState(LoginStatusTypes::NOT_CONNECTED);
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertNull($object->getUserLoggedCkusid());
        });
    }

    public function testGetUserLoggedOid ()
    {
        $this->specify('Checks if returns oid', function() {
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function(){
                return (new LoginStatus())
                    ->setCkusid('123')
                    ->setOid('abc')
                    ->setConnectState(LoginStatusTypes::CONNECTED);
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertEquals('abc', $object->getUserLoggedOid());
        });

        $this->specify('Checks if there is no login status.', function() {
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function () {
                return null;
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function () use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertNull($object->getUserLoggedOid());
        });

        $this->specify('Checks if there is no user connected.', function() {
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function(){
                return (new LoginStatus())
                    ->setCkusid('123')
                    ->setOid('abc')
                    ->setConnectState(LoginStatusTypes::NOT_CONNECTED);
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertNull($object->getUserLoggedOid());
        });
    }

    public function testDeleteCacheUser ()
    {
        $this->specify('Checks if logged user cache is deleted.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->delete('user-123')->will(function(){
                return true;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function () {
                return (new LoginStatus())
                    ->setCkusid('123')
                    ->setOid('abc')
                    ->setConnectState(LoginStatusTypes::CONNECTED);
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function () use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertNull($object->deleteCacheUser());
        });

        $this->specify('Checks if specific user cache is deleted.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->delete('user-456')->will(function(){
                return true;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function () {
                return (new LoginStatus())
                    ->setCkusid('123')
                    ->setOid('abc')
                    ->setConnectState(LoginStatusTypes::CONNECTED);
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function () use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertNull($object->deleteCacheUser('456'));
        });
        
        $this->specify('Checks if there is no login status.', function() {
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function () {
                return null;
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function () use ($things_proph) {
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertNull($object->deleteCacheUser());
        });

        $this->specify('Checks if there is no user connected.', function() {
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function(){
                return (new LoginStatus())
                    ->setCkusid('123')
                    ->setOid('abc')
                    ->setConnectState(LoginStatusTypes::NOT_CONNECTED);
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertNull($object->deleteCacheUser());
        });

        $this->specify('Checks if handle threw exceptions.', function() {
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function(){
                throw new \Exception('Intentional');
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertNull($object->deleteCacheUser());
        });
    }

    public function testGetAvatar ()
    {
        $this->specify('Checks if we get the avatar URL.', function() {
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('public_image', '/public/v1/image'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/public/v1/image/1', Argument::withEntry('query', [
                'width' => 100,
                'height' => 200,
                'redirect' => 'true'
            ]))->will(function(){
                return new Response(200, [], 'http://dru-id.foo/avatar/1');
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertEquals('http://dru-id.foo/avatar/1', callMethod($object, 'getAvatar', [1, 100, 200, true]));
        });

        $this->specify('Checks if we get the avatar URL with no redirect.', function() {
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('public_image', '/public/v1/image'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/public/v1/image/1', Argument::withEntry('query', [
                'width' => 100,
                'height' => 200,
                'redirect' => 'false'
            ]))->will(function(){
                return new Response(200, [], '{"url": "http://dru-id.foo/avatar/1"}');
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertEquals('http://dru-id.foo/avatar/1', callMethod($object, 'getAvatar', [1, 100, 200, false]));
        });

        $this->specify('Checks if we get the default avatar URL.', function() {
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('public_image', '/public/v1/image'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/public/v1/image/1', Argument::withEntry('query', [
                'width' => 100,
                'height' => 200,
                'redirect' => 'false'
            ]))->will(function(){
                return new Response(204, [], 'http://dru-id.foo/avatar/1');
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertEquals('/assets/img/placeholder.png', callMethod($object, 'getAvatar', [1, 100, 200, false]));
        });

        $this->specify('Checks if throws an exception if server response is invalid.', function() {
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('public_image', '/public/v1/image'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/public/v1/image/1', Argument::withEntry('query', [
                'width' => 100,
                'height' => 200,
                'redirect' => 'false'
            ]))->will(function(){
                return new Response(200, [], '{{}');
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            callMethod($object, 'getAvatar', [1, 100, 200, false]);
        }, ['throws' => RequestException::class]);

        $this->specify('Checks if throws an exception if server response with an unexpected status code.', function() {
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('public_image', '/public/v1/image'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/public/v1/image/1', Argument::withEntry('query', [
                'width' => 100,
                'height' => 200,
                'redirect' => 'false'
            ]))->will(function(){
                return new Response(404, [], '{}');
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            callMethod($object, 'getAvatar', [1, 100, 200, false]);
        }, ['throws' => \Exception::class]);

        $this->specify('Checks if does not return URL information.', function() {
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('public_image', '/public/v1/image'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/public/v1/image/1', Argument::withEntry('query', [
                'width' => 100,
                'height' => 200,
                'redirect' => 'false'
            ]))->will(function(){
                return new Response(200, [], '{}');
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertNull(callMethod($object, 'getAvatar', [1, 100, 200, false]));
        });

        $this->specify('Checks if throws an exception if response returns a 204 status code and redirect is true.', function() {
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('public_image', '/public/v1/image'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/public/v1/image/1', Argument::withEntry('query', [
                'width' => 100,
                'height' => 200,
                'redirect' => 'true'
            ]))->will(function(){
                return new Response(204, [], 'http://dru-id.foo/avatar/1');
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertEquals('/assets/img/placeholder.png', callMethod($object, 'getAvatar', [1, 100, 200, true]));
        }, ['throws' => \Exception::class]);
    }

    public function testGetAvatarImg ()
    {
        $this->specify('Checks if we get the avatar URL with custom size.', function() {
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('public_image', '/public/v1/image'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/public/v1/image/1', Argument::withEntry('query', [
                'width' => 100,
                'height' => 200,
                'redirect' => 'true'
            ]))->will(function(){
                return new Response(200, [], 'http://dru-id.foo/avatar/1');
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertEquals('http://dru-id.foo/avatar/1', $object->getAvatarImg(1, 100, 200));
        });

        $this->specify('Checks if we provide an invalid user ID.', function() {
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('public_image', '/public/v1/image'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/public/v1/image/1', Argument::withEntry('query', [
                'width' => 100,
                'height' => 200,
                'redirect' => 'true'
            ]))->will(function(){
                return new Response(404, [], '');
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertEquals('', $object->getAvatarImg(1, 100, 200));
        });
    }

    public function testGetAvatarUrl ()
    {
        $this->specify('Checks if we get the avatar URL.', function() {
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('public_image', '/public/v1/image'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/public/v1/image/1', Argument::withEntry('query', [
                'width' => 100,
                'height' => 200,
                'redirect' => 'true'
            ]))->will(function(){
                return new Response(200, [], 'http://dru-id.foo/avatar/1');
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertEquals('http://dru-id.foo/avatar/1', $object->getAvatarUrl(1, 100, 200));
        });

        $this->specify('Checks if we provide an invalid user ID.', function() {
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('public_image', '/public/v1/image'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/public/v1/image/1', Argument::withEntry('query', [
                'width' => 100,
                'height' => 200,
                'redirect' => 'true'
            ]))->will(function(){
                return new Response(404, [], '');
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertEquals('', $object->getAvatarUrl(1, 100, 200));
        });
    }

    public function testGetUserLoggedAvatarUrl ()
    {
        $this->specify('Checks if we get the avatar URL for the current logged user.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return true;
            });
            $cache_proph->fetch(Argument::cetera())->will(function(){
                return [['user' => ['oid' => 1]]];
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function(){
                return (new LoginStatus())
                    ->setCkusid('1')
                    ->setOid('abc')
                    ->setConnectState(LoginStatusTypes::CONNECTED);
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('public_image', '/public/v1/image'));;
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/public/v1/image/1', Argument::withEntry('query', [
                'width' => 100,
                'height' => 200,
                'redirect' => 'true'
            ]))->will(function(){
                return new Response(200, [], 'http://dru-id.foo/avatar/1');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/avatar/1', $object->getUserLoggedAvatarUrl(100, 200));
        });
    }

    public function testGetBrands ()
    {
        $this->specify('Checks that we can get brands data.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $cache_proph->save(Argument::cetera())->will(function(){
                return true;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('brands', 'brands'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/brands', Argument::withEntry('headers', [
                'Authorization' => 'Bearer 111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.',
                'Content-Type' => 'application/json',
                'From' => '452200208393481-main'
            ]))->will(function(){
                return new Response(200, [], '{"items": [{"id": "b1", "displayName": {"es_ES": "Brand 1"}}, {"id": "b2", "displayName": {"es_ES": "Brand 2"}}]}');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                getSyslogLogger('user-api'),
                $cache_proph->reveal()
            );
            $this->assertEquals([
                    (new Brand())->setKey('b1')->setName('Brand 1'),
                    (new Brand())->setKey('b2')->setName('Brand 2')
                ]
                , $object->getBrands()
            );
        });

        $this->specify('Checks that we can get brands data from cached system.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return true;
            });
            $cache_proph->fetch('brands')->will(function(){
                return serialize([
                    (new Brand())->setKey('b1')->setName('Brand 1'),
                    (new Brand())->setKey('b2')->setName('Brand 2')
                ]);
            });

            $object = new UserApi(
                $this->prophesize(IdentityServiceInterface::class)->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                getSyslogLogger('user-api'),
                $cache_proph->reveal()
            );
            $this->assertEquals([
                    (new Brand())->setKey('b1')->setName('Brand 1'),
                    (new Brand())->setKey('b2')->setName('Brand 2')
                ]
                , $object->getBrands()
            );
        });

        $this->specify('Checks that no data is returned if we do not have a valid client_token.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return null;
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                getSyslogLogger('user-api'),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getBrands());
        });

        $this->specify('Checks if no data is returned if server responds with a non 200 status code.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('brands', 'brands'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/brands', Argument::withEntry('headers', [
                'Authorization' => 'Bearer 111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.',
                'Content-Type' => 'application/json',
                'From' => '452200208393481-main'
            ]))->will(function(){
                return new Response(404, [], '{}');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                getSyslogLogger('user-api'),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getBrands());
        });

        $this->specify('Checks if no data is returned if server responds with an invalid data.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->contains(Argument::cetera())->will(function(){
                return false;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getClientToken()->will(function(){
                return new ClientToken('111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.');
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function() use ($things_proph){
                return $things_proph->reveal();
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.activityid')
                        ->addEndpoint('brands', 'brands'));
            });
            $http = $this->prophesize(HttpServiceInterface::class);
            $http->request('GET', 'http://dru-id.foo/brands', Argument::withEntry('headers', [
                'Authorization' => 'Bearer 111111111111111|3|2.AAAAAAAAAAAAAA.3600.1488275118786|AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.',
                'Content-Type' => 'application/json',
                'From' => '452200208393481-main'
            ]))->will(function(){
                return new Response(200, [], '{{}');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $http->reveal(),
                getSyslogLogger('user-api'),
                $cache_proph->reveal()
            );
            $this->assertCount(0, $object->getBrands());
        });
    }

    public function testLogoutUser ()
    {
        $this->specify('Checks if we do logout logged user.', function() {
            $cache_proph = $this->prophesize(Cache::class);
            $cache_proph->delete('user-123')->will(function(){
                return true;
            });
            $things_proph = $this->prophesize(Things::class);
            $things_proph->getLoginStatus()->will(function () {
                return (new LoginStatus())
                    ->setCkusid('123')
                    ->setOid('abc')
                    ->setConnectState(LoginStatusTypes::CONNECTED);
            });
            $things_proph->getAccessToken()->will(function () {
                return new AccessToken('AAA');
            });
            $things_proph->getRefreshToken()->will(function () {
                return new RefreshToken('BBB');
            });
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function () use ($things_proph) {
                return $things_proph->reveal();
            });
            $identity_proph->clearLocalSessionData()->will(function(){
                return true;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())->addEndPoint((new EndPoint())->setId('logout_endpoint')->setUrl('http://dru-id.foo'));
            });
            $oauth_proph->doLogout(Argument::cetera())->will(function(){
                return true;
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $cache_proph->reveal()
            );
            $this->assertNull($object->logoutUser());
        });

        $this->specify('Checks if properly handles an error.', function() {
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->getThings()->will(function () {
                throw new \Exception('Intended exception.');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                getSyslogLogger('user-api'),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertNull($object->logoutUser());
        });
    }

    public function testCheckUserComplete ()
    {
        $this->specify('Checks if user need to be completed if the user is logged.', function() {
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->isConnected()->will(function(){
                return true;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $oauth_proph->doCheckUserCompleted(Argument::cetera())->will(function(){
                return true;
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertTrue($object->checkUserComplete('my-scope'));
        });

        $this->specify('Checks if user need to be completed if the user is not logged.', function() {
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->isConnected()->will(function(){
                return false;
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertFalse($object->checkUserComplete('my-scope'));
        });

        $this->specify('Checks if properly handles an error.', function() {
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->isConnected()->will(function () {
                throw new \Exception('Intended exception.');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertFalse($object->checkUserComplete('my-scope'));
        });
    }

    public function testCheckUserNeedAcceptTerms ()
    {
        $this->specify('Checks if user need to accept terms if the user is logged.', function() {
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->isConnected()->will(function(){
                return true;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function(){
                return (new Config())
                    ->addApi((new Api())
                        ->setBaseUrl('http://dru-id.foo')
                        ->setName('api.user')
                        ->addEndpoint('user', 'user'));
            });
            $oauth_proph->doCheckUserNeedAcceptTerms(Argument::cetera())->will(function(){
                return true;
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $oauth_proph->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertTrue($object->checkUserNeedAcceptTerms('my-scope'));
        });

        $this->specify('Checks if user need to accept terms if the user is not logged.', function() {
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->isConnected()->will(function(){
                return false;
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertFalse($object->checkUserNeedAcceptTerms('my-scope'));
        });

        $this->specify('Checks if properly handles an error.', function() {
            $identity_proph = $this->prophesize(IdentityServiceInterface::class);
            $identity_proph->isConnected()->will(function () {
                throw new \Exception('Intended exception.');
            });

            $object = new UserApi(
                $identity_proph->reveal(),
                $this->prophesize(OAuthServiceInterface::class)->reveal(),
                $this->prophesize(HttpServiceInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(Cache::class)->reveal()
            );
            $this->assertFalse($object->checkUserNeedAcceptTerms('my-scope'));
        });
    }
}

