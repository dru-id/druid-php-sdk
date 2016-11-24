<?php
namespace Genetsis\IntegrationTest\OAuth;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Http\Collections\HttpMethods as HttpMethodsCollection;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\Http\Services\Http;
use Genetsis\core\Logger\Collections\LogLevels as LogLevelsCollection;
use Genetsis\core\Logger\Services\SyslogLogger;
use Genetsis\core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\core\OAuth\Services\OAuth;
use Genetsis\core\ServiceContainer\Services\ServiceContainer as SC;

/**
 * @package  Genetsis
 * @category IntegrationTest
 */
class OAuthIntegrationTest extends Unit
{
    use Specify;

    /** @var \IntegrationTester */
    protected $tester;

    /** @var HttpServiceInterface $http */
    protected $http;

    /** @var OAuthServiceInterface $oauth */
    public $oauth;


    protected function _before()
    {
        SC::setLogger(new SyslogLogger(LogLevelsCollection::DEBUG));

        $http = $this->createMock(Http::class);
        $http->method('execute')
            ->will($this->returnValueMap([
                    [
                        'http://auth.ci.dru-id.com/oauth2/token',
                        [
                            'grant_type' => 'client_credentials',
                            'client_id' => '231705665113870',
                            'client_secret' => 'Hy6QBa4nSSgW8g0VRRH9idKxNapCA3'
                        ],
                        HttpMethodsCollection::POST,
                        [],
                        [],
                        [ // Returned value
                            'result' => json_decode('{"access_token":"231705665113870|3|2.ynv06g07QgsQGg.3600.1479906956037|Bcq3G9oU2urZo5U7OH03vYcCa8XjOIkx2aVi0WWyCsk.","token_type":"bearer","expires_in":2739,"expires_at":1479906956037}'),
                            'code' => 200,
                            'content_type' => 'application/json'
                        ]
                    ],
                    [
                        'http://auth.ci.dru-id.com/oauth2/token',
                        [
                            'grant_type' => 'authorization_code',
                            'code' => 'xxxxxxx',
                            'redirect_uri' => 'http://examples.dev.dru-id.com/actions/callback',
                            'client_id' => '231705665113870',
                            'client_secret' => 'Hy6QBa4nSSgW8g0VRRH9idKxNapCA3'
                        ],
                        HttpMethodsCollection::POST,
                        [],
                        [],
                        [ // Returned value
                            'result' => json_decode('{"access_token":"231705665113870|1|2.mVr0GDUF0Bm22Q.900.1479905173005-f385e71af90e7e644b4bead7ffe7380974457de9|-lNLB19jbHsfZAFd-lzZeXGzQWrRhMDbj6AXk_JVokM.","token_type":"bearer","expires_in":899,"expires_at":1479905173005,"refresh_token":"231705665113870|4|2.ExluRwW32ldJ0g.1209600.1481113873012-f385e71af90e7e644b4bead7ffe7380974457de9|_R8LXH_3RrAJB-3ftLskRzo1KfpGJlu33sjb6Vlks4E.","login_status":{"uid":194,"oid":"f385e71af90e7e644b4bead7ffe7380974457de9","connect_state":"connected"}}'),
                            'code' => 200,
                            'content_type' => 'application/json'
                        ]
                    ]
                ]
            ));

        SC::setHttpService($http);

        $config = new Config();
        $config->setClientId('231705665113870')->setClientSecret('Hy6QBa4nSSgW8g0VRRH9idKxNapCA3');
        $this->oauth = new OAuth($config);
    }

    protected function _after()
    {
    }


    public function testOAuth()
    {
        $this->specify('Tests that "doGetClientToken" throws an exception when "endpoint" parameter is not defined.', function() {
            $this->oauth->doGetClientToken('');
        }, ['throws' => 'Exception']);

        $this->specify('Tests that "doGetClientToken" returns a ClientToken instance.', function() {
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\ClientToken', $this->oauth->doGetClientToken('http://auth.ci.dru-id.com/oauth2/token'));
        });

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
                'xxxxxxx',
                'http://examples.dev.dru-id.com/actions/callback'
            );
            $this->assertArrayHasKey('access_token', $response);
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\AccessToken', $response['access_token']);
            $this->assertArrayHasKey('refresh_token', $response);
            $this->assertInstanceOf('\Genetsis\core\OAuth\Beans\RefreshToken', $response['refresh_token']);
            $this->assertArrayHasKey('login_status', $response);
        });
    }
}