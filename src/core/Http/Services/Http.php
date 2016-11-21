<?php namespace Genetsis\core\Http\Services;

use \Exception;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\Http\Collections\HttpMethods as HttpMethodsCollection;
use Genetsis\core\ServiceContainer\Services\ServiceContainer as SC;

/**
 * Class to performs HTTP request calls.
 *
 * @package   Genetsis
 * @category  Service
 */
class Http implements HttpServiceInterface {

    /**
     * @inheritDoc
     */
    public function execute($url, $parameters = array(), $http_method = HttpMethodsCollection::GET, $credentials = false, $http_headers = array(), $cookies = array())
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
            $parameters['client_id'] = SC::getOAuthService()->getConfig()->getClientId();
            $parameters['client_secret'] = SC::getOAuthService()->getConfig()->getClientSecret();
        }

        switch ($http_method) {
            case HttpMethodsCollection::POST:
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

            case HttpMethodsCollection::PUT:
                $curl_options[CURLOPT_POSTFIELDS] = http_build_query($parameters);
                break;

            case HttpMethodsCollection::HEAD:
                $curl_options[CURLOPT_NOBODY] = true;
                /* The 'break' is intentionally omitted. */
            case HttpMethodsCollection::DELETE:
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
            case HttpMethodsCollection::GET:
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

        SC::getLogger()->debug('### BEGIN REQUEST ###', __METHOD__, __LINE__);
        SC::getLogger()->debug(sprintf('URL -> [%s][%s] %s', $http_method, ($is_ssl ? 'ssl' : 'no ssl'), var_export($url, true)), __METHOD__, __LINE__);
        SC::getLogger()->debug('Params -> ' . var_export($parameters, true), __METHOD__, __LINE__);
        SC::getLogger()->debug('Headers -> ' . var_export($http_headers, true), __METHOD__, __LINE__);
        SC::getLogger()->debug(sprintf("Response -> [%s][%s]\n%s", $content_type, $http_code, var_export($result, true)), __METHOD__, __LINE__);
        SC::getLogger()->debug('Total Time -> ' . var_export($total_time, true), __METHOD__, __LINE__);
        SC::getLogger()->debug('### END REQUEST ###', __METHOD__, __LINE__);

        return array(
            'result' => ($content_type === 'application/json') ? ((null === json_decode($result)) ? $result : json_decode($result)) : $result,
            'code' => $http_code,
            'content_type' => $content_type
        );
    }
} 