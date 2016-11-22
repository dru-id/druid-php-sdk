<?php  namespace Genetsis\core\OAuth\Beans;

use Genetsis\core\OAuth\Contracts\StoredTokenInterface;
use Genetsis\core\OAuth\Collections\TokenTypes as TokenTypesCollection;

/**
 * Abstract class which aims to be the parent class of the different types
 * of tokens.
 *
 * @package   Genetsis
 * @category  Bean
 * @version   1.0
 * @access    public
 * @since     2011-09-08
 */
class StoredToken implements StoredTokenInterface
{
    /** @var string The token name. */
    protected $name = '';
    /** @var string The token value. */
    protected $value = '';
    /** @var integer integer Number the seconds until the token expires. */
    protected $expires_in = 0;
    /** @var integer Date when the token expires. As UNIX timestamp. */
    protected $expires_at = 0;
    /** @var string Full path to the folder where cookies will be saved. */
    protected $path = '/';

    /**
     * @inheritDoc
     */
    public function __construct($value, $expires_in = 0, $expires_at = 0, $path = '/')
    {
        $this->setValue($value);
        $this->setExpiresIn($expires_in);
        $this->setExpiresAt($expires_at);
        $this->setPath($path);
    }

    /**
     * @inheritDoc
     */
    public static function factory($name, $value, $expires_in = 0, $expires_at = 0, $path = '/')
    {
        switch ($name) {
            case TokenTypesCollection::ACCESS_TOKEN:
                return new AccessToken ($value, $expires_in, $expires_at, $path);
            case TokenTypesCollection::CLIENT_TOKEN:
                return new ClientToken ($value, $expires_in, $expires_at, $path);
            case TokenTypesCollection::REFRESH_TOKEN:
                return new RefreshToken($value, $expires_in, $expires_at, $path);
        }
        return false;
    }


    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        if (!TokenTypesCollection::check($name)) {
            throw new \InvalidArgumentException('Invalid token name');
        }
        $this->name = $name;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        $this->value = (string)$value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExpiresIn()
    {
        return $this->expires_in;
    }

    /**
     * @inheritDoc
     */
    public function setExpiresIn($expires_in)
    {
        $this->expires_in = (int)$expires_in;
        if ($this->expires_in < 0) {
            $this->expires_in = 0;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExpiresAt()
    {
        return $this->expires_at;
    }

    /**
     * @inheritDoc
     */
    public function setExpiresAt($expires_at)
    {
        $this->expires_at = (int)$expires_at;
        if ($this->expires_at < 0) {
            $this->expires_at = 0;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function setPath($path)
    {
        $this->path = (string)$path;
        return $this;
    }
}