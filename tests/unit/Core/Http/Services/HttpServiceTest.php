<?php
namespace Genetsis\UnitTest\OAuth\Beans\Services;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Http\Collections\HttpMethods;
use Genetsis\core\Http\Services\Http as HttpService;
use Genetsis\core\Logger\Services\SyslogLogger;
use Genetsis\core\Logger\Collections\LogLevels as LogLevelsCollection;

/**
 * @package Genetsis
 * @category UnitTest
 */
class HttpServiceTest extends Unit {
    use Specify;

    /** @var \UnitTester */
    protected $tester;
    /** @var HttpService $my_http */
    protected $my_http;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->my_http = new HttpService(new SyslogLogger(LogLevelsCollection::DEBUG));
    }

    protected function _after()
    {
    }

    public function testSendRequest()
    {
        $this->specify('Checks a GET request without parameters.', function(){
            $result = $this->my_http->execute('http://127.0.0.1:8083/get-without-params');
            $this->assertArrayHasKey('result', $result); $this->assertEquals('GET without params', $result['result']);
            $this->assertArrayHasKey('code', $result); $this->assertEquals('200', $result['code']);
            $this->assertArrayHasKey('content_type', $result); $this->assertEquals('application/json', $result['content_type']);
        });

        $this->specify('Checks a GET request with parameters.', function(){
            $result = $this->my_http->execute('http://127.0.0.1:8083/get-with-params', ['bar' => 'biz'], HttpMethods::GET);
            $this->assertArrayHasKey('result', $result); $this->assertEquals('GET with params', $result['result']);
            $this->assertArrayHasKey('code', $result); $this->assertEquals('200', $result['code']);
            $this->assertArrayHasKey('content_type', $result); $this->assertEquals('application/json', $result['content_type']);
        });

        $this->specify('Checks a POST request without parameters.', function(){
            $result = $this->my_http->execute('http://127.0.0.1:8083/post-without-params', [], HttpMethods::POST);
            $this->assertArrayHasKey('result', $result); $this->assertEquals('POST without params', $result['result']);
            $this->assertArrayHasKey('code', $result); $this->assertEquals('200', $result['code']);
            $this->assertArrayHasKey('content_type', $result); $this->assertEquals('application/json', $result['content_type']);
        });

        $this->specify('Checks a POST request with parameters.', function(){
            $result = $this->my_http->execute('http://127.0.0.1:8083/post-with-params', ['foo' => 'bar'], HttpMethods::POST);
            $this->assertArrayHasKey('result', $result); $this->assertEquals('POST with params', $result['result']);
            $this->assertArrayHasKey('code', $result); $this->assertEquals('200', $result['code']);
            $this->assertArrayHasKey('content_type', $result); $this->assertEquals('application/json', $result['content_type']);
        });

        $this->specify('Checks a request with some headers.', function(){
            $result = $this->my_http->execute('http://127.0.0.1:8083/get-with-headers', [], HttpMethods::GET, ['Authorization' => 'Bearer', 'Accept' => 'application/json']);
            $this->assertArrayHasKey('result', $result); $this->assertEquals('Request with some headers', $result['result']);
            $this->assertArrayHasKey('code', $result); $this->assertEquals('200', $result['code']);
            $this->assertArrayHasKey('content_type', $result); $this->assertEquals('application/json', $result['content_type']);
        });

        $this->specify('Checks a request with some cookies.', function(){
            $result = $this->my_http->execute('http://127.0.0.1:8083/get-with-cookies', [], HttpMethods::GET, [], ['my_cookie=bar-foo-biz']);
            $this->assertArrayHasKey('result', $result); $this->assertEquals('Request with some cookies', $result['result']);
            $this->assertArrayHasKey('code', $result); $this->assertEquals('200', $result['code']);
            $this->assertArrayHasKey('content_type', $result); $this->assertEquals('application/json', $result['content_type']);
        });
    }

}
