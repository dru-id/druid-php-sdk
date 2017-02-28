<?php
namespace Genetsis\DruID\Core\Encryption;

/**
 * This class is used to wrap encryption functions.
 *
 * @package   Genetsis\DruID
 * @category  Service
 */
class Encryption {

    /** @var string $_skey The secret key to encrypt data. */
    private $_skey = '';

    /**
     * @param string $client_secret The secret key to encrypt data.
     */
    public function __construct($client_secret)
    {
        $this->_skey = str_pad(trim((string)$client_secret), 32, '\0');
    }

    /**
     * Encodes a string using a secret key.
     *
     * @param string $value The string to be encoded.
     * @return string|false The string encoded. FALSE if there is a problem encoding the value.
     */
    public function encode($value)
    {
        if (!$value) {
            return false;
        }
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->_skey, $value, MCRYPT_MODE_ECB, $iv);
        return trim($this->safe_b64encode($crypttext));
    }

    /**
     * Encodes a string using base64.
     *
     * @param $string $string The string to be encoded.
     * @return mixed The encoded string.
     */
    public function safe_b64encode($string)
    {
        return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($string));
    }

    /**
     * Decodes a string using a secret key.
     *
     * @param string $value The string to be decoded. This string must be encoded with the {@link Encryption::encode} method.
     * @return string|false The string decoded. FALSE if there is a problem decoding the value.
     */
    public function decode($value)
    {
        if (!$value) {
            return false;
        }
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->_skey, $this->safe_b64decode($value), MCRYPT_MODE_ECB, $iv);
        return trim($decrypttext);
    }

    /**
     * Decodes base64 encoded string.
     *
     * @param $string $string The string to be decoded.
     * @return string|false The string decoded o FALSE on failure.
     */
    public function safe_b64decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = (strlen($data) % 4);
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
}