<?php
namespace Genetsis\Core\Http\Services;
use Genetsis\Core\Http\Contracts\CookiesServiceInterface;

/**
 * Cookies handler implementation.
 *
 * @package  Genetsis
 * @category Service
 */
class Cookies implements CookiesServiceInterface {

    /**
     * @inheritDoc
     */
    public function set($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        if (!$name) {
            return false;
        }
        return @setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * @inheritDoc
     */
    public function get($name, $default = null)
    {
        return $this->has($name) ? $_COOKIE[$name] : $default;
    }

    /**
     * @inheritDoc
     */
    public function all()
    {
        return (isset($_COOKIE) && is_array($_COOKIE)) ? $_COOKIE : [];
    }

    /**
     * @inheritDoc
     */
    public function has($name)
    {
        return ($name && isset($_COOKIE) && is_array($_COOKIE) && array_key_exists($name, $_COOKIE));
    }

    /**
     * @inheritDoc
     */
    public function delete($name, $path = null, $domain = null)
    {
        if ($this->has($name)) {
            unset($_COOKIE[$name]);
            @setcookie($name, '', time() - 3600, $path, $domain);
        }
        return $this->has($name);
    }

}