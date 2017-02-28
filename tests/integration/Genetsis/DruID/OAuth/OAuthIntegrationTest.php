<?php
namespace Genetsis\DruID\IntegrationTest\OAuth;

use Codeception\Specify;
use Codeception\Test\Unit;
use Doctrine\Common\Cache\VoidCache;
use Genetsis\DruID\Core\Http\Contracts\CookiesServiceInterface;
use Genetsis\DruID\Core\Http\Contracts\HttpServiceInterface;
use Genetsis\DruID\Core\OAuth\Beans\AccessToken;
use Genetsis\DruID\Core\OAuth\Beans\ClientToken;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\DruID\Core\OAuth\Beans\RefreshToken;
use Genetsis\DruID\Core\OAuth\Collections\AuthMethods;
use Genetsis\DruID\Core\OAuth\Collections\TokenTypes;
use Genetsis\DruID\Core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\DruID\Core\OAuth\Exceptions\InvalidGrantException;
use Genetsis\DruID\Core\OAuth\OAuth;
use Genetsis\DruID\Core\OAuth\OAuthConfigFactory;
use Genetsis\DruID\Core\User\Beans\LoginStatus;
use Genetsis\DruID\Core\User\Collections\LoginStatusTypes;
use Genetsis\DruID\DruID;
use Genetsis\DruID\Identity\Identity;
use Genetsis\DruID\UnitTest\Core\Http\AuthMethodsCollectionTest;
use GuzzleHttp\Psr7\Response;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Prophecy\Argument;
use Prophecy\Prophet;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @package  Genetsis\DruID
 * @category IntegrationTest
 */
class OAuthIntegrationTest extends Unit
{
    use Specify;

    /** @var Prophet $prophet */
    protected $prophet;
    /** @var \IntegrationTester */
    protected $tester;
    /** @var Config $config */
    protected $config;
    /** @var HttpServiceInterface $http */
    protected $http;
    /** @var LoggerInterface $logger */
    protected $logger;
    /** @var OAuthServiceInterface $oauth */
    public $oauth;


    protected function _before()
    {
        $this->prophet = new Prophet();
        $log_handler = new SyslogHandler('druid');
        $log_handler->setFormatter(new LineFormatter("%level_name% %context.method%[%context.line%]: %message%\n", null, true));
        $this->logger = new Logger('druid', [$log_handler]);
        $this->config = (new OAuthConfigFactory($this->logger, new VoidCache()))->buildConfigFromXmlFile(OAUTHCONFIG_SAMPLE_XML_1_4);
//        $this->oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $this->getHttpService(), $this->getCookieService(), $this->logger);
    }

    protected function _after()
    {
        $this->prophet->checkPredictions();
    }

    public function testDoGetClientToken()
    {
        $this->specify('Tests that "doGetClientToken" throws an exception when "endpoint" parameter is not defined.', function () {
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $this->prophet->prophesize(HttpServiceInterface::class)->reveal(), $this->getCookieService(), $this->logger);
            $oauth->doGetClientToken('');
        }, ['throws' => 'Exception']);

        $this->specify('Checks that "doGetClientToken" returns a ClientToken instance.', function () {
            $http_prophecy = $this->prophet->prophesize(HttpServiceInterface::class);
            $http_prophecy->request(
                'POST',
                'http://auth.ci.dru-id.com/oauth2/token',
                Argument::withEntry('form_params', [
                    'grant_type' => 'client_credentials',
                    'client_id' => 'XXXXXXX',
                    'client_secret' => 'YYYYYYY'
                ]),
                Argument::cetera()
            )->will(function () {
                return new Response(200, ['Content-type: application/json'], '{"access_token":"231705665113870|3|2.ynv06g07QgsQGg.3600.1479906956037|Bcq3G9oU2urZo5U7OH03vYcCa8XjOIkx2aVi0WWyCsk.","token_type":"bearer","expires_in":3600,"expires_at":'.(time()+3600).'}');
            });
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $http_prophecy->reveal(), $this->getCookieService(), $this->logger);
            $this->assertInstanceOf(ClientToken::class, $oauth->doGetClientToken('http://auth.ci.dru-id.com/oauth2/token'));
        });

        $this->specify('Checks that "doGetClientToken" throws an exception when server\'s response is not a valid JSON string.', function () {
            $http_prophecy = $this->prophet->prophesize(HttpServiceInterface::class);
            $http_prophecy->request(
                'POST',
                'http://auth.ci.dru-id.com/oauth2/token',
                Argument::withEntry('form_params', [
                    'grant_type' => 'client_credentials',
                    'client_id' => 'XXXXXXX',
                    'client_secret' => 'YYYYYYY'
                ]),
                Argument::cetera()
            )->will(function () {
                return new Response(200, ['Content-type: application/json'], '{{}');
            });
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $http_prophecy->reveal(), $this->getCookieService(), $this->logger);
            $this->assertInstanceOf(ClientToken::class, $oauth->doGetClientToken('http://auth.ci.dru-id.com/oauth2/token'));
        }, ['throws' => \Exception::class]);

        $this->specify('Tests that "doGetClientToken" throws an exception when server\'s response does not contains information about the token.', function () {
            $http_prophecy = $this->prophet->prophesize(HttpServiceInterface::class);
            $http_prophecy->request(
                'POST',
                'http://auth.ci.dru-id.com/oauth2/token',
                Argument::withEntry('form_params', [
                    'grant_type' => 'client_credentials',
                    'client_id' => 'XXXXXXX',
                    'client_secret' => 'YYYYYYY'
                ]),
                Argument::cetera()
            )->will(function () {
                return new Response(200, ['Content-type: application/json'], '{}');
            });
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $http_prophecy->reveal(), $this->getCookieService(), $this->logger);
            $this->assertInstanceOf(ClientToken::class, $oauth->doGetClientToken('http://auth.ci.dru-id.com/oauth2/token'));
        }, ['throws' => \Exception::class]);

        $this->specify('Checks that "doGetClientToken" set "expires_in" value to default if this one is not present in server\'s response.', function () {
            $http_prophecy = $this->prophet->prophesize(HttpServiceInterface::class);
            $http_prophecy->request(
                'POST',
                'http://auth.ci.dru-id.com/oauth2/token',
                Argument::withEntry('form_params', [
                    'grant_type' => 'client_credentials',
                    'client_id' => 'XXXXXXX',
                    'client_secret' => 'YYYYYYY'
                ]),
                Argument::cetera()
            )->will(function () {
                return new Response(200, ['Content-type: application/json'], '{"access_token":"231705665113870|3|2.ynv06g07QgsQGg.3600.1479906956037|Bcq3G9oU2urZo5U7OH03vYcCa8XjOIkx2aVi0WWyCsk.","token_type":"bearer","expires_at":'.(time()+3600).'}');
            });
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $http_prophecy->reveal(), $this->getCookieService(), $this->logger);
            $token = $oauth->doGetClientToken('http://auth.ci.dru-id.com/oauth2/token');
            $this->assertInstanceOf(ClientToken::class, $token);
            $this->assertEquals((OAuth::DEFAULT_EXPIRES_IN - (OAuth::DEFAULT_EXPIRES_IN * OAuth::SAFETY_RANGE_EXPIRES_IN)), $token->getExpiresIn());
        });
    }

    public function testDoGetAccessToken()
    {
        $this->specify('Tests that "doGetAccessToken" throws an exception when "endpoint" parameter is not defined.', function() {
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $this->prophet->prophesize(HttpServiceInterface::class)->reveal(), $this->getCookieService(), $this->logger);
            $oauth->doGetAccessToken('', '', '');
        }, ['throws' => 'Exception']);

        $this->specify('Tests that "doGetAccessToken" throws an exception when "code" parameter is not defined.', function() {
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $this->prophet->prophesize(HttpServiceInterface::class)->reveal(), $this->getCookieService(), $this->logger);
            $oauth->doGetAccessToken('http://auth.ci.dru-id.com/oauth2/token', '', '');
        }, ['throws' => 'Exception']);

        $this->specify('Tests that "doGetAccessToken" throws an exception when "redirect_url" parameter is not defined.', function() {
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $this->prophet->prophesize(HttpServiceInterface::class)->reveal(), $this->getCookieService(), $this->logger);
            $oauth->doGetAccessToken('http://auth.ci.dru-id.com/oauth2/token', 'xxxxxxxxxx', '');
        }, ['throws' => 'Exception']);

        $this->specify('Checks that "doGetAccessToken" returns a AccessToken instance.', function () {
            $http_prophecy = $this->prophet->prophesize(HttpServiceInterface::class);
            $http_prophecy->request(
                'POST',
                'http://auth.ci.dru-id.com/oauth2/token',
                Argument::withEntry('form_params', [
                    'grant_type' => AuthMethods::GRANT_TYPE_AUTH_CODE,
                    'code' => 'xxxxxxxxxx',
                    'redirect_uri' => 'http://www.foo.com/actions',
                    'client_id' => 'XXXXXXX',
                    'client_secret' => 'YYYYYYY'
                ]),
                Argument::cetera()
            )->will(function () {
                return new Response(200, ['Content-type: application/json'], '{"access_token":"231705665113870|1|2.0h9mw60JM7iD6g.900.1484654484343-2f91af5e4f41eebd03c92f616b35fccb4ec64995|OuuEQx1gVfe5YGC4nz1FVoVGxgngU6XT1rHAuAryRls.","token_type":"bearer","expires_in":900,"expires_at":'.(time()+900).',"refresh_token":"231705665113870|4|2.sNR2wanZ1tdJ4Q.1209600.1485795426335-2f91af5e4f41eebd03c92f616b35fccb4ec64995|qxvceuX5dwwoJQm3yeel2zfgal4L73vGvap-mHzu_pY.","login_status":{"uid":11,"oid":"2f91af5e4f41eebd03c92f616b35fccb4ec64995","connect_state":"connected"}}');
            });
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $http_prophecy->reveal(), $this->getCookieService(), $this->logger);
            $response = $oauth->doGetAccessToken('http://auth.ci.dru-id.com/oauth2/token', 'xxxxxxxxxx', 'http://www.foo.com/actions');
            $this->assertArrayHasKey('access_token', $response);
            $this->assertInstanceOf(AccessToken::class, $response['access_token']);
            $this->assertArrayHasKey('refresh_token', $response);
            $this->assertInstanceOf(RefreshToken::class, $response['refresh_token']);
            $this->assertArrayHasKey('login_status', $response);
            $this->assertInstanceOf(LoginStatus::class, $response['login_status']);
            $this->assertEquals('11', $response['login_status']->getCkusid());
            $this->assertEquals('2f91af5e4f41eebd03c92f616b35fccb4ec64995', $response['login_status']->getOid());
            $this->assertEquals(LoginStatusTypes::CONNECTED, $response['login_status']->getConnectState());
        });

        $this->specify('Checks that "doGetAccessToken" throws an exception when server\'s response is not a valid JSON string.', function () {
            $http_prophecy = $this->prophet->prophesize(HttpServiceInterface::class);
            $http_prophecy->request(
                'POST',
                'http://auth.ci.dru-id.com/oauth2/token',
                Argument::withEntry('form_params', [
                    'grant_type' => AuthMethods::GRANT_TYPE_AUTH_CODE,
                    'code' => 'xxxxxxxxxx',
                    'redirect_uri' => 'http://www.foo.com/actions',
                    'client_id' => 'XXXXXXX',
                    'client_secret' => 'YYYYYYY'
                ]),
                Argument::cetera()
            )->will(function () {
                return new Response(200, ['Content-type: application/json'], '{{}');
            });
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $http_prophecy->reveal(), $this->getCookieService(), $this->logger);
            $oauth->doGetAccessToken('http://auth.ci.dru-id.com/oauth2/token', 'xxxxxxxxxx', 'http://www.foo.com/actions');
        }, ['throws' => \Exception::class]);

        $this->specify('Checks that "doGetAccessToken" throws an exception when server\'s response does not contains "access_token" information.', function () {
            $http_prophecy = $this->prophet->prophesize(HttpServiceInterface::class);
            $http_prophecy->request(
                'POST',
                'http://auth.ci.dru-id.com/oauth2/token',
                Argument::withEntry('form_params', [
                    'grant_type' => AuthMethods::GRANT_TYPE_AUTH_CODE,
                    'code' => 'xxxxxxxxxx',
                    'redirect_uri' => 'http://www.foo.com/actions',
                    'client_id' => 'XXXXXXX',
                    'client_secret' => 'YYYYYYY'
                ]),
                Argument::cetera()
            )->will(function () {
                return new Response(200, ['Content-type: application/json'], '{"token_type":"bearer","expires_in":900,"expires_at":'.(time()+900).',"refresh_token":"231705665113870|4|2.sNR2wanZ1tdJ4Q.1209600.1485795426335-2f91af5e4f41eebd03c92f616b35fccb4ec64995|qxvceuX5dwwoJQm3yeel2zfgal4L73vGvap-mHzu_pY.","login_status":{"uid":11,"oid":"2f91af5e4f41eebd03c92f616b35fccb4ec64995","connect_state":"connected"}}');
            });
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $http_prophecy->reveal(), $this->getCookieService(), $this->logger);
            $oauth->doGetAccessToken('http://auth.ci.dru-id.com/oauth2/token', 'xxxxxxxxxx', 'http://www.foo.com/actions');
        }, ['throws' => \Exception::class]);

        $this->specify('Checks that "doGetAccessToken" throws an exception when server\'s response does not contains "refresh_token" information.', function () {
            $http_prophecy = $this->prophet->prophesize(HttpServiceInterface::class);
            $http_prophecy->request(
                'POST',
                'http://auth.ci.dru-id.com/oauth2/token',
                Argument::withEntry('form_params', [
                    'grant_type' => AuthMethods::GRANT_TYPE_AUTH_CODE,
                    'code' => 'xxxxxxxxxx',
                    'redirect_uri' => 'http://www.foo.com/actions',
                    'client_id' => 'XXXXXXX',
                    'client_secret' => 'YYYYYYY'
                ]),
                Argument::cetera()
            )->will(function () {
                return new Response(200, ['Content-type: application/json'], '{"access_token":"231705665113870|1|2.0h9mw60JM7iD6g.900.1484654484343-2f91af5e4f41eebd03c92f616b35fccb4ec64995|OuuEQx1gVfe5YGC4nz1FVoVGxgngU6XT1rHAuAryRls.","token_type":"bearer","expires_in":900,"expires_at":'.(time()+900).',"login_status":{"uid":11,"oid":"2f91af5e4f41eebd03c92f616b35fccb4ec64995","connect_state":"connected"}}');
            });
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $http_prophecy->reveal(), $this->getCookieService(), $this->logger);
            $oauth->doGetAccessToken('http://auth.ci.dru-id.com/oauth2/token', 'xxxxxxxxxx', 'http://www.foo.com/actions');
        }, ['throws' => \Exception::class]);

        $this->specify('Checks that "doGetAccessToken" set "expires_in" value to default if this one is not present in the response.', function () {
            $http_prophecy = $this->prophet->prophesize(HttpServiceInterface::class);
            $http_prophecy->request(
                'POST',
                'http://auth.ci.dru-id.com/oauth2/token',
                Argument::withEntry('form_params', [
                    'grant_type' => AuthMethods::GRANT_TYPE_AUTH_CODE,
                    'code' => 'xxxxxxxxxx',
                    'redirect_uri' => 'http://www.foo.com/actions',
                    'client_id' => 'XXXXXXX',
                    'client_secret' => 'YYYYYYY'
                ]),
                Argument::cetera()
            )->will(function () {
                return new Response(200, ['Content-type: application/json'], '{"access_token":"231705665113870|1|2.0h9mw60JM7iD6g.900.1484654484343-2f91af5e4f41eebd03c92f616b35fccb4ec64995|OuuEQx1gVfe5YGC4nz1FVoVGxgngU6XT1rHAuAryRls.","token_type":"bearer","expires_at":'.(time()+900).',"refresh_token":"231705665113870|4|2.sNR2wanZ1tdJ4Q.1209600.1485795426335-2f91af5e4f41eebd03c92f616b35fccb4ec64995|qxvceuX5dwwoJQm3yeel2zfgal4L73vGvap-mHzu_pY.","login_status":{"uid":11,"oid":"2f91af5e4f41eebd03c92f616b35fccb4ec64995","connect_state":"connected"}}');
            });
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $http_prophecy->reveal(), $this->getCookieService(), $this->logger);
            $response = $oauth->doGetAccessToken('http://auth.ci.dru-id.com/oauth2/token', 'xxxxxxxxxx', 'http://www.foo.com/actions');
            $this->assertArrayHasKey('access_token', $response);
            $this->assertInstanceOf(AccessToken::class, $response['access_token']);
            $this->assertEquals((OAuth::DEFAULT_EXPIRES_IN - (OAuth::DEFAULT_EXPIRES_IN * OAuth::SAFETY_RANGE_EXPIRES_IN)), $response['access_token']->getExpiresIn());
        });

        $this->specify('Checks that "doGetAccessToken" is able to handle the response if does not contains information about the login status at all.', function () {
            $http_prophecy = $this->prophet->prophesize(HttpServiceInterface::class);
            $http_prophecy->request(
                'POST',
                'http://auth.ci.dru-id.com/oauth2/token',
                Argument::withEntry('form_params', [
                    'grant_type' => AuthMethods::GRANT_TYPE_AUTH_CODE,
                    'code' => 'xxxxxxxxxx',
                    'redirect_uri' => 'http://www.foo.com/actions',
                    'client_id' => 'XXXXXXX',
                    'client_secret' => 'YYYYYYY'
                ]),
                Argument::cetera()
            )->will(function () {
                return new Response(200, ['Content-type: application/json'], '{"access_token":"231705665113870|1|2.0h9mw60JM7iD6g.900.1484654484343-2f91af5e4f41eebd03c92f616b35fccb4ec64995|OuuEQx1gVfe5YGC4nz1FVoVGxgngU6XT1rHAuAryRls.","token_type":"bearer","expires_in":900,"expires_at":'.(time()+900).',"refresh_token":"231705665113870|4|2.sNR2wanZ1tdJ4Q.1209600.1485795426335-2f91af5e4f41eebd03c92f616b35fccb4ec64995|qxvceuX5dwwoJQm3yeel2zfgal4L73vGvap-mHzu_pY."}');
            });
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $http_prophecy->reveal(), $this->getCookieService(), $this->logger);
            $response = $oauth->doGetAccessToken('http://auth.ci.dru-id.com/oauth2/token', 'xxxxxxxxxx', 'http://www.foo.com/actions');
            $this->assertArrayHasKey('login_status', $response);
            $this->assertInstanceOf(LoginStatus::class, $response['login_status']);
            $this->assertEquals('', $response['login_status']->getCkusid());
            $this->assertEquals('', $response['login_status']->getOid());
            $this->assertEquals(LoginStatusTypes::UNKNOWN, $response['login_status']->getConnectState());
        });

        $this->specify('Checks that "doGetAccessToken" is able to handle the response if does contains partial information about the login status.', function () {
            $http_prophecy = $this->prophet->prophesize(HttpServiceInterface::class);
            $http_prophecy->request(
                'POST',
                'http://auth.ci.dru-id.com/oauth2/token',
                Argument::withEntry('form_params', [
                    'grant_type' => AuthMethods::GRANT_TYPE_AUTH_CODE,
                    'code' => 'xxxxxxxxxx',
                    'redirect_uri' => 'http://www.foo.com/actions',
                    'client_id' => 'XXXXXXX',
                    'client_secret' => 'YYYYYYY'
                ]),
                Argument::cetera()
            )->will(function () {
                return new Response(200, ['Content-type: application/json'], '{"access_token":"231705665113870|1|2.0h9mw60JM7iD6g.900.1484654484343-2f91af5e4f41eebd03c92f616b35fccb4ec64995|OuuEQx1gVfe5YGC4nz1FVoVGxgngU6XT1rHAuAryRls.","token_type":"bearer","expires_in":900,"expires_at":'.(time()+900).',"refresh_token":"231705665113870|4|2.sNR2wanZ1tdJ4Q.1209600.1485795426335-2f91af5e4f41eebd03c92f616b35fccb4ec64995|qxvceuX5dwwoJQm3yeel2zfgal4L73vGvap-mHzu_pY.","login_status":{}}');
            });
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $http_prophecy->reveal(), $this->getCookieService(), $this->logger);
            $response = $oauth->doGetAccessToken('http://auth.ci.dru-id.com/oauth2/token', 'xxxxxxxxxx', 'http://www.foo.com/actions');
            $this->assertArrayHasKey('login_status', $response);
            $this->assertInstanceOf(LoginStatus::class, $response['login_status']);
            $this->assertEquals('', $response['login_status']->getCkusid());
            $this->assertEquals('', $response['login_status']->getOid());
            $this->assertEquals(LoginStatusTypes::UNKNOWN, $response['login_status']->getConnectState());
        });

        $this->specify('Checks that "doGetAccessToken" throws an exception if server respond with an "InvalidGrantException".', function () {
            $http_prophecy = $this->prophet->prophesize(HttpServiceInterface::class);
            $http_prophecy->request(
                'POST',
                'http://auth.ci.dru-id.com/oauth2/token',
                Argument::withEntry('form_params', [
                    'grant_type' => AuthMethods::GRANT_TYPE_AUTH_CODE,
                    'code' => 'xxxxxxxxxx',
                    'redirect_uri' => 'http://www.foo.com/actions',
                    'client_id' => 'XXXXXXX',
                    'client_secret' => 'YYYYYYY'
                ]),
                Argument::cetera()
            )->will(function () {
                throw new InvalidGrantException('Testing an invalid grant exception error.');
            });
            $oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $this->config, $http_prophecy->reveal(), $this->getCookieService(), $this->logger);
            $oauth->doGetAccessToken('http://auth.ci.dru-id.com/oauth2/token', 'xxxxxxxxxx', 'http://www.foo.com/actions');
        }, ['throws' => InvalidGrantException::class]);
    }
//
//    public function testDoRefreshToken()
//    {
//
//    }

    /**
     * @return HttpServiceInterface
     */
    private function getHttpService()
    {
        $prophecy = $this->prophet->prophesize();
        $prophecy->willImplement(HttpServiceInterface::class);

        $prophecy->request(
            'POST',
            'http://auth.ci.dru-id.com/oauth2/token',
            Argument::withEntry('form_params', [
                'grant_type' => 'client_credentials',
                'client_id' => 'XXXXXXX',
                'client_secret' => 'YYYYYYY'
            ]),
            Argument::cetera()
        )->will(function () {
            return new Response(200, ['Content-type: application/json'], '{"access_token":"231705665113870|3|2.ynv06g07QgsQGg.3600.1479906956037|Bcq3G9oU2urZo5U7OH03vYcCa8XjOIkx2aVi0WWyCsk.","token_type":"bearer","expires_in":2739,"expires_at":1479906956037}');
        });

        $prophecy->request(
            'http://auth.ci.dru-id.com/oauth2/token',
            Argument::allOf(
                Argument::withEntry('grant_type', 'authorization_code'),
                Argument::withEntry('code', 'xxxxxxxxxx'),
                Argument::withEntry('redirect_uri', 'http://www.foo.com/actions'),
                Argument::withEntry('client_id', 'XXXXXXX'),
                Argument::withEntry('client_secret', 'YYYYYYY')
            ),
            Argument::cetera()
        )->will(function(){
            return [ // Returned value
                'result' => json_decode('{"access_token":"231705665113870|1|2.mVr0GDUF0Bm22Q.900.1479905173005-f385e71af90e7e644b4bead7ffe7380974457de9|-lNLB19jbHsfZAFd-lzZeXGzQWrRhMDbj6AXk_JVokM.","token_type":"bearer","expires_in":899,"expires_at":1479905173005,"refresh_token":"231705665113870|4|2.ExluRwW32ldJ0g.1209600.1481113873012-f385e71af90e7e644b4bead7ffe7380974457de9|_R8LXH_3RrAJB-3ftLskRzo1KfpGJlu33sjb6Vlks4E.","login_status":{"uid":194,"oid":"f385e71af90e7e644b4bead7ffe7380974457de9","connect_state":"connected"}}'),
                'code' => 200,
                'content_type' => 'application/json'
            ];
        });

        $prophecy->request(
            'http://auth.ci.dru-id.com/oauth2/token',
            Argument::allOf(
                Argument::withEntry('grant_type', 'urn:es.cocacola:oauth2:grant_type:validate_bearer'),
                Argument::withEntry('oauth_token', '231705665113870|1|2.mVr0GDUF0Bm22Q.900.1479905173005-f385e71af90e7e644b4bead7ffe7380974457de9|-lNLB19jbHsfZAFd-lzZeXGzQWrRhMDbj6AXk_JVokM.'),
                Argument::withEntry('client_id', 'XXXXXXX'),
                Argument::withEntry('client_secret', 'YYYYYYY')
            ),
            Argument::cetera()
        )->will(function(){
            return [
                'result' => json_decode('{"access_token":"231705665113870|1|2.mVr0GDUF0Bm22Q.900.1479905173005-f385e71af90e7e644b4bead7ffe7380974457de9|-lNLB19jbHsfZAFd-lzZeXGzQWrRhMDbj6AXk_JVokM.","token_type":"bearer","expires_in":899,"expires_at":1479905173005,"login_status":{"uid":194,"oid":"f385e71af90e7e644b4bead7ffe7380974457de9","connect_state":"connected"}}'),
                'code' => 200,
                'content_type' => 'application/json'
            ];
        });

        $prophecy->request(
            'http://api.ci.dru-id.com/api/user',
            Argument::allOf(
                Argument::withEntry('oauth_token', '231705665113870|3|2.ynv06g07QgsQGg.3600.1479906956037|Bcq3G9oU2urZo5U7OH03vYcCa8XjOIkx2aVi0WWyCsk.'),
                Argument::withEntry('s', '*'),
                Argument::withEntry('f', 'User'),
                Argument::withEntry('w.id', 194)
            ),
            Argument::cetera()
        )->will(function(){
            return [
                'result' => json_decode('{"data":[{"user":{"id":194,"app":"Test Client","entry-point":"231705665113870-main","country_iso_code":"es","typologies":{"Consumer":{"value":"Consumer","label":"consumer"}},"confirmed":true,"user_ids":{"email":{"value":"cucurucu3@yopmail.net","label":"email","confirmed":true,"is_social":false}},"user_assertions":{"terms":{"consumer":{"value":"true"}},"optin":{"consumer":{"value":"false"}},"none":{}},"oid":"f385e71af90e7e644b4bead7ffe7380974457de9"}}],"count":1}'),
                'code' => 200,
                'content_type' => 'application/json'
            ];
        });

        $prophecy->request(
            'https://auth.ci.dru-id.com/oauth2/revoke',
            Argument::allOf(
                Argument::withEntry('token', '231705665113870|4|2.ExluRwW32ldJ0g.1209600.1481113873012-f385e71af90e7e644b4bead7ffe7380974457de9|_R8LXH_3RrAJB-3ftLskRzo1KfpGJlu33sjb6Vlks4E.'),
                Argument::withEntry('token_type', 'refresh_token'),
                Argument::withEntry('client_id', 'XXXXXXX'),
                Argument::withEntry('client_secret', 'YYYYYYY')
            ),
            Argument::cetera()
        )->will(function(){
            return null;
        });

        return $prophecy->reveal();
    }

    /**
     * @return CookiesServiceInterface
     */
    private function getCookieService()
    {
        $prophecy = $this->prophet->prophesize();
        $prophecy->willImplement(CookiesServiceInterface::class);

        $prophecy->set(TokenTypes::ACCESS_TOKEN, Argument::cetera())->willReturn(true);
        $prophecy->set(TokenTypes::REFRESH_TOKEN, Argument::cetera())->willReturn(true);
        $prophecy->set(TokenTypes::CLIENT_TOKEN, Argument::cetera())->willReturn(true);

        $prophecy->delete(Argument::cetera())->willReturn(null);

        return $prophecy->reveal();
    }
}