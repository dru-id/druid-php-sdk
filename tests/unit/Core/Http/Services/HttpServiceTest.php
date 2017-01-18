<?php
namespace Genetsis\UnitTest\Core\Http\Services;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\Core\Http\Exceptions\RequestException;
use Genetsis\Core\Http\Services\Http;
use Genetsis\Core\OAuth\Exceptions\InvalidGrantException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException as GuzzleHttpRequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @package Genetsis
 * @category UnitTest
 */
class HttpServiceTest extends Unit
{

    use Specify;

    /** @var Prophet $prophet */
    protected $prophet;
    /** @var \UnitTester */
    protected $tester;
    /** @var LoggerInterface $logger */
    protected $logger;

    protected function _before()
    {
        $this->prophet = new Prophet();
        $log_handler = new SyslogHandler('druid');
        $log_handler->setFormatter(new LineFormatter("%level_name% %context.method%[%context.line%]: %message%\n", null, true));
        $this->logger = new Logger('druid', [$log_handler]);
    }

    protected function _after()
    {
    }

    public function testConstruct()
    {
        $this->specify('Checks constructor.', function () {
            $http_client_prophecy = $this->prophet->prophesize(ClientInterface::class);
            $http = new Http($http_client_prophecy->reveal(), $this->logger);
            $this->assertInstanceOf(ClientInterface::class, getProperty($http, 'http'));
            $this->assertInstanceOf(LoggerInterface::class, getProperty($http, 'logger'));
        });
    }

    public function testCheckErrorMessage()
    {

        $this->specify('Checks if error detection works properly with a response with generic error data.', function () {
            $http = new Http($this->prophet->prophesize(ClientInterface::class)->reveal(), $this->logger);
            $http->checkErrorMessage($this->getResponseProphecy(200, [], '{"error":"Error description", "type":"error-type-code"}')->reveal());
        }, ['throws' => \Exception::class]);

        $this->specify('Checks if error detection works properly with an invalid grant exception response.', function () {
            $http = new Http($this->prophet->prophesize(ClientInterface::class)->reveal(), $this->logger);
            $http->checkErrorMessage($this->getResponseProphecy(200, [], '{"error":"Error description for an InvalidGrantException error.", "type":"InvalidGrantException"}')->reveal());
        }, ['throws' => InvalidGrantException::class]);

        $this->specify('Checks if error detection works properly with an error without error type.', function () {
            $http = new Http($this->prophet->prophesize(ClientInterface::class)->reveal(), $this->logger);
            $http->checkErrorMessage($this->getResponseProphecy(200, [], '{"error":"Error description without type."}')->reveal());
        }, ['throws' => \Exception::class]);

        $this->specify('Checks if error detection works properly with a response without error.', function () {
            $http = new Http($this->prophet->prophesize(ClientInterface::class)->reveal(), $this->logger);
            $http->checkErrorMessage($this->getResponseProphecy(200, [], '{"foo":"bar"}')->reveal());
        });

        $this->specify('Checks if error detection can handle a response with invalid JSON.', function () {
            $http = new Http($this->prophet->prophesize(ClientInterface::class)->reveal(), $this->logger);
            $http->checkErrorMessage($this->getResponseProphecy(200, [], '{{}')->reveal());
        });
    }

    public function testRequest()
    {
        $this->specify('Checks if providing an empty URI the service throws an error.', function () {
            $http_client_prophecy = $this->prophet->prophesize(ClientInterface::class);
            $http = new Http($http_client_prophecy->reveal(), $this->logger);
            $http->request('GET', '');
        }, ['throws' => \InvalidArgumentException::class]);

        $this->specify('Checks if not providing the method the service sends a "GET" response.', function () {
            $http_client_prophecy = $this->prophet->prophesize(ClientInterface::class);
            $response_prophecy = $this->getResponseProphecy(200, [], '{"success":"GET request received!"}');
            $http_client_prophecy->request('GET', 'http://www.foo.com', Argument::cetera())
                ->will(function () use ($response_prophecy) {
                    return $response_prophecy->reveal();
                });
            $http = new Http($http_client_prophecy->reveal(), $this->logger);
            $response = $http->request('', 'http://www.foo.com');
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals('{"success":"GET request received!"}', (string)$response->getBody());
        });

        $this->specify('Checks if the service returns a valid response.', function () {
            $http_client_prophecy = $this->prophet->prophesize(ClientInterface::class);
            $response_prophecy = $this->getResponseProphecy(200, [], '{"success":"Foo request success!"}');
            $http_client_prophecy->request('GET', 'http://www.foo.com', Argument::cetera())
                ->will(function () use ($response_prophecy) {
                    return $response_prophecy->reveal();
                });
            $http = new Http($http_client_prophecy->reveal(), $this->logger);
            $response = $http->request('GET', 'http://www.foo.com');
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals('{"success":"Foo request success!"}', (string)$response->getBody());
        });

        $this->specify('Checks if the service returns a valid response for a request with options provided.', function () {
            $http_client_prophecy = $this->prophet->prophesize(ClientInterface::class);
            $response_prophecy = $this->getResponseProphecy(200, ['Accept' => 'application/json'], '{"success":"Foo request success!"}');
            $http_client_prophecy->request('GET', 'http://www.foo.com', ['headers' => ['Accept' => 'application/json']])
                ->will(function () use ($response_prophecy) {
                    return $response_prophecy->reveal();
                });
            $http = new Http($http_client_prophecy->reveal(), $this->logger);
            $response = $http->request('GET', 'http://www.foo.com', ['headers' => ['Accept' => 'application/json']]);
            $this->assertInstanceOf(Response::class, $response);
            $this->assertArrayHasKey('Accept', $response->getHeaders());
        });

        $this->specify('Checks if the service handles a response with a non 200 status code with error data.', function () {
            $http_client_prophecy = $this->prophet->prophesize(ClientInterface::class);
            $response_prophecy = $this->getResponseProphecy(400, [], '{"error":"Error description"}');
            $http_client_prophecy->request('GET', 'http://www.foo.com', Argument::cetera())
                ->will(function () use ($response_prophecy) {
                    return $response_prophecy->reveal();
                });
            $http = new Http($http_client_prophecy->reveal(), $this->logger);
            $http->request('GET', 'http://www.foo.com');
        }, ['throws' => RequestException::class]);

        $this->specify('Checks if the service handles a response with an InvalidGrantException error.', function () {
            $http_client_prophecy = $this->prophet->prophesize(ClientInterface::class);
            $response_prophecy = $this->getResponseProphecy(400, [], '{"error":"Error description", "type":"InvalidGrantException"}');
            $http_client_prophecy->request('GET', 'http://www.foo.com', Argument::cetera())
                ->will(function () use ($response_prophecy) {
                    return $response_prophecy->reveal();
                });
            $http = new Http($http_client_prophecy->reveal(), $this->logger);
            $http->request('GET', 'http://www.foo.com');
        }, ['throws' => InvalidGrantException::class]);

        $this->specify('Checks if the service handles a response with a non 200 status code without error data.', function () {
            $http_client_prophecy = $this->prophet->prophesize(ClientInterface::class);
            $response_prophecy = $this->getResponseProphecy(400, [], '{"ups":"oh my gosh"}');
            $http_client_prophecy->request('GET', 'http://www.foo.com', Argument::cetera())
                ->will(function () use ($response_prophecy) {
                    return $response_prophecy->reveal();
                });
            $http = new Http($http_client_prophecy->reveal(), $this->logger);
            $response = $http->request('GET', 'http://www.foo.com');
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(400, $response->getStatusCode());
        });

        $this->specify('Checks if the service handles a response if the provided HTTP client returns an invalid response.', function () {
            $http_client_prophecy = $this->prophet->prophesize(ClientInterface::class);
            $http_client_prophecy->request('GET', 'http://www.foo.com', Argument::cetera())
                ->will(function () {
                    return '';
                });
            $http = new Http($http_client_prophecy->reveal(), $this->logger);
            $http->request('GET', 'http://www.foo.com');
        }, ['throws' => RequestException::class]);

        $this->specify('Checks if the service handles an error thrown by the provided HTTP client.', function () {
            $http_client_prophecy = $this->prophet->prophesize(ClientInterface::class);
            $http_client_prophecy->request('GET', 'http://www.foo.com', Argument::cetera())
                ->will(function () {
                    throw new GuzzleHttpRequestException('Intentional error.', new Request('GET', ''));
                });
            $http = new Http($http_client_prophecy->reveal(), $this->logger);
            $http->request('GET', 'http://www.foo.com');
        }, ['throws' => RequestException::class]);
    }

    /**
     * @param integer $status_code
     * @param array $headers
     * @param string|callable $response
     * @return ResponseInterface
     */
    protected function getResponseProphecy($status_code = 200, $headers = [], $response = '')
    {
        $prophecy = $this->prophet->prophesize(Response::class);
        $prophecy->getStatusCode()->will(function () use ($status_code) {
            return $status_code;
        });
        $prophecy->getHeaders()->will(function () use ($headers) {
            return $headers;
        });
        $prophecy->getBody()->will(function () use ($response) {
            return $response;
        });
        return $prophecy;
    }
}
