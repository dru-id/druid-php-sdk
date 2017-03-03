<?php namespace Genetsis\DruID\Core\OAuth\Contracts;

/**
 * Stored token interface.
 *
 * @author Genetsis
 * @link http://developers.dru-id.com
 */
interface StoredTokenInterface {

    /**
     * @param string $value The token value.
     * @param integer $expires_in Number the seconds until the token expires.
     * @param integer $expires_at Date when the token expires. As UNIX timestamp.
     * @param string $path Full path to the folder where cookies will be saved.
     *     Only if necessary.
     */
    public function __construct ($value, $expires_in = 0, $expires_at = 0, $path = '/');

    /**
     * Create an instance of an access token based on the name.
     *
     * @param string $name One of the values defined in {@link \Genetsis\DruID\Core\OAuth\Collections\TokenTypes}
     * @param string $value The token value.
     * @param integer $expires_in Number the seconds until the token expires.
     * @param integer $expires_at Date when the token expires. As UNIX timestamp.
     * @param string $path Full path to the folder where cookies will be saved.
     * @return bool|StoredTokenInterface An object with the token data or FALSE if we are not able to create it.
     */
    public static function factory ($name, $value, $expires_in = 0, $expires_at = 0, $path = '/');

    /**
     * Returns the token name.
     *
     * We use it for serialization the token content.
     *
     * @return string The token name.
     * @see \Genetsis\DruID\Core\OAuth\Collections\TokenTypes
     */
    public function getName();

    /**
     * Sets the token name.
     *
     * @param string $name One of the values defined in {@link \Genetsis\DruID\Core\OAuth\Collections\TokenTypes}
     * @return StoredTokenInterface
     * @throws \InvalidArgumentException If the name is invalid.
     */
    public function setName($name);

    /**
     * Returns the token value.
     *
     * @return string The token value. It could be empty.
     */
    public function getValue();

    /**
     * Sets token value.
     *
     * @param string $value Token value.
     * @return StoredTokenInterface
     */
    public function setValue($value);

    /**
     * Returns the number of seconds when token expires.
     *
     * @return integer The number of seconds.
     */
    public function getExpiresIn();

    /**
     * Sets the number of seconds when token expires.
     *
     * @param integer $expires_in The number of seconds it takes to die.
     * @return StoredTokenInterface
     */
    public function setExpiresIn($expires_in);

    /**
     * Returns the date when the "token" should be dead.
     *
     * @return integer UNIX timestamp with the date. Zero if not defined.
     */
    public function getExpiresAt();

    /**
     * Sets the date when the "token" should be dead.
     *
     * @param integer $expires_at UNIX timestamp.
     * @return StoredTokenInterface
     */
    public function setExpiresAt($expires_at);

    /**
     * Returns the path to cookie folder.
     *
     * @return string The full path to the folder where the cookies will be
     *     saved.
     */
    public function getPath();

    /**
     * Sets the path where the cookies will be saved.
     *
     * @param string $path Full path to the folder.
     * @return StoredTokenInterface
     */
    public function setPath($path);

}