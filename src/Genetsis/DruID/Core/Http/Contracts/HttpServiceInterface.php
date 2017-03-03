<?php
namespace Genetsis\DruID\Core\Http\Contracts;

use Genetsis\DruID\Core\Http\Exceptions\RequestException;
use Genetsis\DruID\Core\OAuth\Exceptions\InvalidGrantException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * HTTP service interface.
 *
 * @author Genetsis
 * @link http://developers.dru-id.com
 */
interface HttpServiceInterface
{

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
