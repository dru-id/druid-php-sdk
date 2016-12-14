<?php
namespace Genetsis\core\Cache\Contracts;

/**
 * Cache service interface.
 *
 * @package  Genetsis
 * @category Contract
 */
interface CacheServiceInterface {

    /**
     * Stores data to cache.
     *
     * Note: anything that evaluates to FALSE, NULL, '', 0 will not be saved.
     *
     * @param string $key An identifier for the data.
     * @param mixed $data The data to be saved.
     * @param integer $ttl Lifetime of the stored data. In seconds.
     * @returns boolean TRUE on success, FALSE otherwise.
     */
    public function set($key, $data = false, $ttl = 3600);

    /**
     * Search for a key and returns stored data if exists.
     *
     * @param string $key The data identifier.
     * @param mixed $default Default value returned if the key is not found.
     * @returns mixed Data that was stored or FALSE on error.
     */
    public function get($key, $default = null);

    /**
     * Checks if there is data stored based on a key.
     *
     * @param string $key The identifier.
     * @return boolean TRUE if there is a key stored in cache system or FALSE if not.
     */
    public function has($key);

    /**
     * Removes a key, regardless of its expiry time.
     *
     * @param string $key The identifier to be deleted.
     * @return boolean TRUE on success, FALSE otherwise.
     */
    public function delete($key);

    /**
     * Cleans file cache.
     *
     * @return boolean TRUE on success, FALSE otherwise.
     */
    public function clean();

}