<?php namespace Genetsis\core\OAuth\Contracts;

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
     * @param string $name The token name.
     * @param sting $value The token value.
     * @param $expires_in Number the seconds until the token expires.
     * @param $expires_at Date when the token expires. As UNIX timestamp.
     * @param $path Full path to the folder where cookies will be saved.
     * @return bool|AccessToken|ClientToken|RefreshToken An object of type {@link StoredToken} or FALSE if
     *     unable to create it.
     */
    public static function factory ($name, $value, $expires_in, $expires_at, $path);

    /**
     * Returns the token name.
     *
     * We use it for serialization the token content.
     *
     * @return string The token name.
     * @see \Genetsis\core\OAuth\Collections\TokenTypes
     */
    public function getName();

    /**
     * Sets the token name.
     *
     * @return void
     * @see \Genetsis\core\OAuth\Collections\TokenTypes
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
     * @param string Token value.
     * @return void
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
     * @param integer The number of seconds it takes to die.
     * @return void
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
     * @param integer UNIX timestamp.
     * @return void
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
     * @param string Full path to the folder.
     * @return void
     * @todo Checks if path exists and is writable.
     */
    public function setPath($path);

}