<?php
namespace Genetsis\core\Http\Contracts;

/**
 * Cookie service interface.
 *
 * This interface explains how to implement a cookie handler service which will centralize the cookie management.
 *
 * @package  Genetsis
 * @category Contract
 */
interface CookiesServiceInterface {

    /**
     * Send a cookie to browser.
     *
     * This helper use the "setcookie" function from PHP standard and accepts the same parameters that this one.
     *
     * @param string $name The name of the cookie.
     * @param string $value [optional] The value of the cookie. This value is stored on the clients computer; do not
     *      store sensitive information.
     * @param int $expire [optional] The time the cookie expires. This is a Unix timestamp so is in number of seconds
     *      since the epoch. In other words, you'll most likely set this with the time function plus the number of
     *      seconds before you want it to expire. Or you might use mktime. time()+60*60*24*30 will set the cookie to
     *      expire in 30 days. If set to 0, or omitted, the cookie will expire at the end of the session (when the
     *      browser closes).
     *      You may notice the expire parameter takes on a Unix timestamp, as opposed to the date format Wdy,
     *      DD-Mon-YYYY HH:MM:SS GMT, this is because PHP does this conversion internally.
     *      Expire is compared to the client's time which can differ from server's time.
     * @param string $path [optional] The path on the server in which the cookie will be available on. If set to '/',
     *      the cookie will be available within the entire domain. If set to '/foo/', the cookie will only be available
     *      within the /foo/ directory and all sub-directories such as /foo/bar/ of domain. The default value is the
     *      current directory that the cookie is being set in.
     * @param string $domain [optional] The domain that the cookie is available.
     *      To make the cookie available on all subdomains of example.com then you'd set it to '.example.com'. The . is
     *      not required but makes it compatible with more browsers. Setting it to www.example.com will make the cookie
     *      only available in the www subdomain. Refer to tail matching in the spec for details.
     * @param bool $secure [optional] Indicates that the cookie should only be transmitted over a secure HTTPS
     *      connection from the client. When set to true, the cookie will only be set if a secure connection exists.
     *      On the server-side, it's on the programmer to send this kind of cookie only on secure connection (e.g. with
     *      respect to $_SERVER["HTTPS"]).
     * @param bool $httponly [optional] When true the cookie will be made accessible only through the HTTP protocol.
     *      This means that the cookie won't be accessible by scripting languages, such as JavaScript. This setting can
     *      effectively help to reduce identity theft through XSS attacks (although it is not supported by all browsers).
     *      Added in PHP 5.2.0. true or false
     * @return boolean If output exists prior to calling this function, this method will fail and return FALSE. If it
     *      successfully runs, it will return TRUE.
     *      This does not indicate whether the user accepted the cookie.
     *
     * @see http://php.net/manual/en/function.setcookie.php
     */
    public function set($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null);

    /**
     * Retrieves a cookie value.
     *
     * @param string $name The name of the cookie.
     * @param mixed $default Default value returned if the key is not found.
     * @return string|null If cookie does not exist then NULL will be returned.
     */
    public function get($name, $default = null);

    /**
     * Returns all available cookies.
     *
     * @return array Returns a key=value array, which the key is the cookie name and the value the cookie value. It
     *      could be empty.
     */
    public function all();

    /**
     * Checks if a cookie exists.
     *
     * @param string $name The name of the cookie.
     * @return boolean
     */
    public function has($name);

    /**
     * Removes a cookie.
     *
     * Please keep in mind that calling this method will not delete the cookie from client browser immediately. Instead
     * the response will contain the same cookie but with a past expiration which will make this cookie invalid and not
     * be send again in further requests.
     *
     * @param string $name The name of the cookie.
     * @param string $path [optional] {@see CookiesServiceInterface::get}
     * @param string $domain [optional] {@see CookiesServiceInterface::get}
     * @return boolean
     */
    public function delete($name, $path = null, $domain = null);

}