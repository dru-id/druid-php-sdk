<?php
namespace Genetsis\Core\Http;

use Genetsis\Core\Http\Contracts\HttpServiceInterface;
use Genetsis\Core\Http\Exceptions\RequestException;
use Genetsis\Core\OAuth\Exceptions\InvalidGrantException;
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
            $content = json_decode((string)$response->getBody());
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
} 