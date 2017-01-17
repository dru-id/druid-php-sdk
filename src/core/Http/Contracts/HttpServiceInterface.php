<?php namespace Genetsis\core\Http\Contracts;

use Genetsis\core\Http\Collections\HttpMethods as HttpMethodsCollection;
use Genetsis\core\Http\Exceptions\RequestException;
use Genetsis\core\OAuth\Exceptions\InvalidGrantException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * HTTP service interface.
 *
 * @package   Genetsis
 * @category  Contract
 */
interface HttpServiceInterface {

    /**
     * @param string $uri Endpoint where the request is sent. Without params.
     * @param array $parameters mixed Associative vector with request params. Use key as param name, and value as value. The values shouldn't be prepared.
     * @param string $http_method string HTTP method. One of the values defined in {@link HttpMethodsCollection}
     * @param array $http_headers A vector of strings with HTTP headers or FALSE if no additional headers to sent.
     * @param array $cookies A vector of strings with cookie data or FALSE if no cookies to sent. One line per cookie ("key=value"), without trailing semicolon.
     * @return array An associative array with that items:
     *     - result: An string or array on success, or FALSE if there is no result.
     *     - code: HTTP code.
     *     - content-type: Content-type related to result
     * @throws \Exception If there is an error.
     * @deprecated
     */
    public function execute ($url, $parameters = array(), $http_method = HttpMethodsCollection::GET, $http_headers = array(), $cookies = array());

    /**
     * Sends a request. This implementation follows the same as
     *
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param string|UriInterface $uri
     * @param array $options See {@see http://docs.guzzlephp.org/en/latest/request-options.html}
     * @return ResponseInterface
     * @throws RequestException
     * @throws \InvalidArgumentException
     */
    public function request($method, $uri, array $options = []);

    /**
     * Checks response looking for an error.
     *
     * @param ResponseInterface $response
     * @return void
     * @throws InvalidGrantException
     * @throws \Exception
     */
    public function checkErrorMessage(ResponseInterface $response);
}
