<?php namespace Genetsis\core\Http\Contracts;

use Genetsis\core\Http\Collections\HttpMethods as HttpMethodsCollection;

/**
 * HTTP service interface.
 *
 * @package   Genetsis
 * @category  Contract
 */
interface HttpServiceInterface {

    /**
     * @param string $url Endpoint where the request is sent. Without params.
     * @param array $parameters mixed Associative vector with request params. Use key as param name, and value as value. The values shouldn't be prepared.
     * @param string $http_method string HTTP method. One of the values defined in {@link HttpMethodsCollection}
     * @param bool $credentials If true, client_id and client_secret are included in params
     * @param array $http_headers A vector of strings with HTTP headers or FALSE if no additional headers to sent.
     * @param array $cookies A vector of strings with cookie data or FALSE if no cookies to sent. One line per cookie ("key=value"), without trailing semicolon.
     * @return array An associative array with that items:
     *     - result: An string or array on success, or FALSE if there is no result.
     *     - code: HTTP code.
     *     - content-type: Content-type related to result
     * @throws \Exception If there is an error.
     */
    public function execute ($url, $parameters = array(), $http_method = HttpMethodsCollection::GET, $credentials = false, $http_headers = array(), $cookies = array());

}