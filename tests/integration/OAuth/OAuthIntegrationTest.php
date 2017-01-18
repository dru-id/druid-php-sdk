<?php
namespace Genetsis\IntegrationTest\OAuth;

use Codeception\Specify;
use Codeception\Test\Unit;
use Doctrine\Common\Cache\VoidCache;
use Genetsis\Core\Http\Contracts\CookiesServiceInterface;
use Genetsis\Core\Http\Contracts\HttpServiceInterface;
use Genetsis\Core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\Core\OAuth\Collections\TokenTypes;
use Genetsis\Core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\Core\OAuth\Services\OAuth;
use Genetsis\Core\OAuth\Services\OAuthConfigFactory;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Prophecy\Argument;
use Prophecy\Prophet;
use Psr\Log\LogLevel;

/**
 * @package  Genetsis
 * @category IntegrationTest
 */
class OAuthIntegrationTest extends Unit
{
    use Specify;

    /** @var Prophet $prophet */
    protected $prophet;
    /** @var \IntegrationTester */
    protected $tester;
    /** @var HttpServiceInterface $http */
    protected $http;
    /** @var OAuthServiceInterface $oauth */
    public $oauth;


    protected function _before()
    {
        $this->prophet = new Prophet();
        $log_handler = new SyslogHandler('druid');
        $log_handler->setFormatter(new LineFormatter("%level_name% %context.method%[%context.line%]: %message%\n", null, true));
        $logger = new Logger('druid', [$log_handler]);
        $oauth_config = (new OAuthConfigFactory($logger, new VoidCache()))->buildConfigFromXmlFile(OAUTHCONFIG_SAMPLE_XML_1_4);
        $this->oauth = new OAuth($this->prophet->prophesize(DruID::class)->reveal(), $oauth_config, $this->getHttpService(), $this->getCookieService(), $logger);
    }

    protected function _after()
    {
        $this->prophet->checkPredictions();
    }

    public function testSetterAndGetterConfig()
    {
        $this->specify('Checks setter and getter for "config" parameter.', function(){
            $config = new Config();
            $config->setClientId('foobarbiz');
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Contracts\OAuthServiceInterface', $this->oauth->setConfig($config));
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\OAuthConfig\Config', $this->oauth->getConfig());
            $this->assertEquals('foobarbiz', $this->oauth->getConfig()->getClientId());
        });
    }

    public function testDoGetClientToken()
    {
        $this->specify('Tests that "doGetClientToken" throws an exception when "endpoint" parameter is not defined.', function () {
            $this->oauth->doGetClientToken('');
        }, ['throws' => 'Exception']);

        $this->specify('Tests that "doGetClientToken" returns a ClientToken instance.', function () {
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\ClientToken', $this->oauth->doGetClientToken('http://auth.ci.dru-id.com/oauth2/token'));
        });
    }

    public function testDoGetAccessToken()
    {
        $this->specify('Tests that "doGetAccessToken" throws an exception when "endpoint" parameter is not defined.', function() {
            $this->oauth->doGetAccessToken('', '', '');
        }, ['throws' => 'Exception']);

        $this->specify('Tests that "doGetAccessToken" throws an exception when "code" parameter is not defined.', function() {
            $this->oauth->doGetAccessToken('http://auth.ci.dru-id.com/oauth2/token', '', '');
        }, ['throws' => 'Exception']);

        $this->specify('Tests that "doGetAccessToken" throws an exception when "redirect_url" parameter is not defined.', function() {
            $this->oauth->doGetAccessToken('http://auth.ci.dru-id.com/oauth2/token', 'xxxxxxxxxx', '');
        }, ['throws' => 'Exception']);

        $this->specify('Tests that "doGetAccessToken" returns AccessToken data.', function() {
            $response = $this->oauth->doGetAccessToken(
                'http://auth.ci.dru-id.com/oauth2/token',
                'xxxxxxxxxx',
                'http://www.foo.com/actions'
            );
            $this->assertArrayHasKey('access_token', $response);
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\AccessToken', $response['access_token']);
            $this->assertArrayHasKey('refresh_token', $response);
            $this->assertInstanceOf('\Genetsis\Core\OAuth\Beans\RefreshToken', $response['refresh_token']);
            $this->assertArrayHasKey('login_status', $response);
        });
    }

    public function testDoRefreshToken()
    {

    }

    /**
     * @return HttpServiceInterface
     */
    private function getHttpService()
    {
        $prophecy = $this->prophet->prophesize();
        $prophecy->willImplement(HttpServiceInterface::class);

        $prophecy->request(
            'http://auth.ci.dru-id.com/oauth2/token',
            Argument::allOf(
                Argument::withEntry('grant_type', 'client_credentials'),
                Argument::withEntry('client_id', 'XXXXXXX'),
                Argument::withEntry('client_secret', 'YYYYYYY')
            ),
            Argument::cetera()
        )->will(function () {
            return [ // Returned value
                'result' => json_decode('{"access_token":"231705665113870|3|2.ynv06g07QgsQGg.3600.1479906956037|Bcq3G9oU2urZo5U7OH03vYcCa8XjOIkx2aVi0WWyCsk.","token_type":"bearer","expires_in":2739,"expires_at":1479906956037}'),
                'code' => 200,
                'content_type' => 'application/json'
            ];
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