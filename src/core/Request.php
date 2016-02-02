<?php
namespace Genetsis\core;

use \Exception;
use Genetsis\Config;
use Genetsis\Identity;
use Genetsis\Logger;


class Request
{
    /** Http Methods */
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_GET = 'GET';
    const HTTP_DELETE = 'DELETE';
    const HTTP_HEAD = 'HEAD';

    const SECURED = true;
    const NOT_SECURED = false;
    /**
     * @param string $url Endpoint where the request is sent. Without params.
     * @param array $parameters mixed Associative vector with request params. Use key as param name, and value as value. The values shouldn't be prepared.
     * @param string $http_method string HTTP method. One of them:
     *        - {@link self::HTTP_GET}
     *        - {@link self::HTTP_POST}
     *        - {@link self::HTTP_METHOD_HEAD}
     *        - {@link self::HTTP_METHOD_PUT}
     *        - {@link self::HTTP_METHOD_DELETE}
     * @param bool $credentials If true, client_id and client_secret are included in params
     * @param array $http_headers A vector of strings with HTTP headers or FALSE if no additional headers to sent.
     * @param array $cookies A vector of strings with cookie data or FALSE if no cookies to sent. One line per cookie ("key=value"), without trailing semicolon.
     * @return array An associative array with that items:
     *     - result: An string or array on success, or FALSE if there is no result.
     *     - code: HTTP code.
     *     - content-type: Content-type related to result
     * @throws \Exception If there is an error.
     */
    public static function execute($url, $parameters = array(), $http_method = self::HTTP_GET, $credentials = self::NOT_SECURED, $http_headers = array(), $cookies = array())
    {
        if (!extension_loaded('curl')) {
            throw new Exception('The PHP extension curl must be installed to use this library.');
        }

        if (($url = trim($url)) == '') {
            return array(
                'result' => false,
                'code' => 0,
                'content_type' => ''
            );
        }
        $is_ssl = (preg_match('#^https#Usi', $url)) ? true : false;

        $curl_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $http_method,
            CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT']
        );

        if ($is_ssl) {
            $curl_options[CURLOPT_SSL_VERIFYPEER] = false;
            $curl_options[CURLOPT_SSL_VERIFYHOST] = 0;
        } else {
            $curl_options[CURLOPT_SSL_VERIFYPEER] = true;
        }

        if ($credentials) {
            $parameters['client_id'] = OAuthConfig::getClientId();
            $parameters['client_secret'] = OAuthConfig::getClientSecret();
        }

        switch ($http_method) {
            case self::HTTP_POST:
                $curl_options[CURLOPT_POST] = true;
                // Check if parameters must to be in json format
                if (isset($http_headers['Content-Type'])
                    && $http_headers['Content-Type'] == 'application/json'
                    && !empty($parameters) && is_array($parameters)
                ) {
                    //echo (json_encode($parameters));
                    $curl_options[CURLOPT_POSTFIELDS] = json_encode($parameters);
                } // No Json format
                else {
                    $curl_options[CURLOPT_POSTFIELDS] = http_build_query($parameters);
                }
                break;

            case self::HTTP_PUT:
                $curl_options[CURLOPT_POSTFIELDS] = http_build_query($parameters);
                break;

            case self::HTTP_HEAD:
                $curl_options[CURLOPT_NOBODY] = true;
            /* No break */
            case self::HTTP_DELETE:
                // Check if parameters are in json
                if (isset($http_headers['Content-Type'])
                    && $http_headers['Content-Type'] == 'application/json'
                    && !empty($parameters) && is_array($parameters)
                ) {
                    $curl_options[CURLOPT_POSTFIELDS] = json_encode($parameters);
                } // No Json format
                else {
                    $url .= '?' . http_build_query($parameters, null, '&');
                }
                break;
            case self::HTTP_GET:
                if (!empty($parameters)) {
                    $url .= '?' . http_build_query($parameters, null, '&');
                }
                break;
            default:
                break;
        }

        $curl_options[CURLOPT_URL] = $url;

        // Cookies.
        if (is_array($cookies) && !empty($cookies)) {
            // Removes trailing semicolons, if exists.
            foreach ($cookies as $key => $value) {
                $cookies[$key] = rtrim($value, ';');
            }
            $curl_options[CURLOPT_COOKIE] = implode('; ', $cookies);
        }

        // Prepare headers.
        if (is_array($http_headers) && !empty($http_headers)) {
            $header = array();
            foreach ($http_headers as $key => $parsed_urlvalue) {
                $header[] = "$key: $parsed_urlvalue";
            }
            $curl_options[CURLOPT_HTTPHEADER] = $header;
        }

        // Send request.
        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);

        Identity::getLogger()->debug('### BEGIN REQUEST ###');
        Identity::getLogger()->debug(sprintf('URL -> [%s][%s] %s', $http_method, ($is_ssl ? 'ssl' : 'no ssl'), var_export($url, true)));
        Identity::getLogger()->debug('Params -> ' . var_export($parameters, true));
        Identity::getLogger()->debug('Headers -> ' . var_export($http_headers, true));
        Identity::getLogger()->debug(sprintf("Response -> [%s][%s]\n%s", $content_type, $http_code, var_export($result, true)));
        Identity::getLogger()->debug('Total Time -> ' . var_export($total_time, true));
        Identity::getLogger()->debug('### END REQUEST ###');

        return array(
            'result' => ($content_type === 'application/json') ? ((null === json_decode($result)) ? $result : json_decode($result)) : $result,
            'code' => $http_code,
            'content_type' => $content_type
        );
    }
} 