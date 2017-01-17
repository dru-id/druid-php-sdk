<?php
namespace Genetsis\core\Http\Beans;

/**
 * @package  Genetsis
 * @category Bean
 */
class Error
{

    /** @var string $code */
    protected $code;
    /** @var string $description */
    protected $description;

    /**
     * @param string $code
     * @param string $description
     */
    public function __construct($code, $description)
    {
        $this->setCode($code);
        $this->setDescription($description);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Error
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Error
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
}
