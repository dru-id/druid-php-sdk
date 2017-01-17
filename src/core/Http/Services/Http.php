<?php
namespace Genetsis\core\Http\Services;

use Genetsis\core\Http\Beans\Error;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\Http\Collections\HttpMethods as HttpMethodsCollection;
use Genetsis\core\Http\Exceptions\RequestException;
use Genetsis\core\OAuth\Exceptions\InvalidGrantException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException as GuzzleHttpRequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class to performs HTTP request calls.
 *
 * @package  Genetsis
 * @category Service
 */
class Http implements HttpServiceInterface {

    /** @var ClientInterface $http */
    protected $http;
    /** @var LoggerInterface $logger */
    protected $logger;

    /**
     * @param ClientInterface $http
     * @param LoggerInterface $logger
     */
    public function __construct(ClientInterface $http, LoggerInterface $logger)
    {
        $this->http = $http;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function request($method, $uri, array $options = [])
    {
        try {
            if (!$uri) {
                throw new \InvalidArgumentException('URI not defined');
            }
            if (!$method) {
                $method = 'GET';
            }
            $response = $this->http->request($method, $uri, $options);
            if (!($response instanceof ResponseInterface)) {
                $this->logger->debug('Response received but it\'s not a valid response object.', ['method' => __METHOD__, 'line' => __LINE__]);
                throw new RequestException('Response object not defined.');
            }
            $this->checkErrorMessage($response);
            return $response;
        } catch (GuzzleHttpRequestException $e) {
            throw new RequestException($e->getMessage(), $e->getCode(), $e);
        } catch (InvalidGrantException $e) {
            throw $e;
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function checkErrorMessage(ResponseInterface $response)
    {
        try {
            $content = @json_decode((string)$response->getBody());
            if (isset($content->error)) {
                if (isset($content->type) && ($content->type == 'InvalidGrantException')) {
                    throw new InvalidGrantException($content->error);
                }
                throw new \Exception('(' . (isset($content->type) ? trim($content->type) : '') . ') ' . $content->error);
            }
        } catch (InvalidGrantException $e) {
            $this->logger->debug('Invalid grant exception detected: ' . $e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->debug('Error detected: '.$e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function execute($url, $parameters = array(), $http_method = HttpMethodsCollection::GET, $http_headers = array(), $cookies = array())
    {
        if (!extension_loaded('curl')) {
            throw new \Exception('The PHP extension curl must be installed to use this library.');
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
            CURLOPT_USERAGENT => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''
        );

        if ($is_ssl) {
            $curl_options[CURLOPT_SSL_VERIFYPEER] = false;
            $curl_options[CURLOPT_SSL_VERIFYHOST] = 0;
        } else {
            $curl_options[CURLOPT_SSL_VERIFYPEER] = true;
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

        $this->logger->debug('### BEGIN REQUEST ###', ['method' => __METHOD__, 'line' => __LINE__]);
        $this->logger->debug(sprintf('URL -> [%s][%s] %s', $http_method, ($is_ssl ? 'ssl' : 'no ssl'), var_export($url, true)), ['method' => __METHOD__, 'line' => __LINE__]);
        $this->logger->debug('Params -> ' . var_export($parameters, true), ['method' => __METHOD__, 'line' => __LINE__]);
        $this->logger->debug('Headers -> ' . var_export($http_headers, true), ['method' => __METHOD__, 'line' => __LINE__]);
        $this->logger->debug(sprintf("Response -> [%s][%s]\n%s", $content_type, $http_code, var_export($result, true)), ['method' => __METHOD__, 'line' => __LINE__]);
        $this->logger->debug('Total Time -> ' . var_export($total_time, true), ['method' => __METHOD__, 'line' => __LINE__]);
        $this->logger->debug('### END REQUEST ###', ['method' => __METHOD__, 'line' => __LINE__]);

        return array(
            'result' => ($content_type === 'application/json') ? ((null === json_decode($result)) ? $result : json_decode($result)) : $result,
            'code' => $http_code,
            'content_type' => $content_type
        );
    }
} 