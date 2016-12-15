<?php
namespace Genetsis\core\Http\Contracts;

/**
 * Session service interface.
 *
 * This interface explains how to implement a session handler service which will centralize the session management.
 *
 * @package  Genetsis
 * @category Contract
 */
interface SessionServiceInterface {

    /**
     * Stores data to session.
     *
     * @param string $key An identifier for the data.
     * @param mixed $data The data to be saved.
     * @returns boolean TRUE on success, FALSE otherwise.
     */
    public function set($key, $data);

    /**
     * Search for a key and returns stored data if exists.
     *
     * @param string $key The data identifier.
     * @param mixed $default Default value returned if the key is not found.
     * @returns mixed Data that was stored or $default if not exists.
     */
    public function get($key, $default = null);

    /**
     * Returns all session data.
     *
     * @return array Returns a key=value array, which the key is the session key and the value the stored value. It
     *      could be empty.
     */
    public function all();

    /**
     * Checks if there is data stored based on a key.
     *
     * @param string $key The identifier.
     * @return boolean TRUE if there is a key stored in cache system or FALSE if not.
     */
    public function has($key);

    /**
     * Removes a stored data.
     *
     * @param string $key The identifier to be deleted.
     * @return boolean TRUE on success, FALSE otherwise.
     */
    public function delete($key);

}