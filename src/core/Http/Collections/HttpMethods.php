<?php namespace Genetsis\core\Http\Collections;
use Genetsis\core\Utils\Contracts\CollectionInterface;

/**
 * Class to group all HTTP Method supported by HttpService.
 *
 * @package   Genetsis
 * @category  Collection
 */
class HttpMethods implements CollectionInterface {

    const POST = 'POST';
    const PUT = 'PUT';
    const GET = 'GET';
    const DELETE = 'DELETE';
    const HEAD = 'HEAD';

    /**
     * @inheritDoc
     */
    public static function check($value)
    {
        return ($value && in_array($value, [self::POST, self::GET, self::PUT, self::DELETE, self::HEAD]));
    }

}