<?php namespace Genetsis\core\OAuth\Beans\OAuthConfig;

/**
 * Bean to store entry point information.
 *
 * @package   Genetsis
 * @category  Bean
 */
class EntryPoint {

    /** @var string $id */
    protected $id = '';

    /** @var string $promotion_id Promotion ID related to this entry point. */
    protected $promotion_id = '';

    /** @var array $prizes Prizes related to this entry point. */
    protected $prizes = [];

    /**
     * @param array $settings Initial settings. Array structure:
     *      [
     *          'id' => {@see EntryPoint::setId},
     *          'promotion_id' => {@see EntryPoint::setPromotionId},
     *          'prizes' => {@see EntryPoint::setPrizes}
     *      ]
     */
    public function __construct(array $settings = [])
    {
        if (isset($settings['id'])) { $this->setId($settings['id']); }
        if (isset($settings['promotion_id'])) { $this->setPromotionId($settings['promotion_id']); }
        if (isset($settings['prizes'])) { $this->setPrizes($settings['prizes']); }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return EntryPoint
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getPromotionId()
    {
        return $this->promotion_id;
    }

    /**
     * @param string $promotion_id
     * @return EntryPoint
     */
    public function setPromotionId($promotion_id)
    {
        $this->promotion_id = $promotion_id;
        return $this;
    }

    /**
     * @return array
     */
    public function getPrizes()
    {
        return $this->prizes;
    }

    /**
     * @param string $id
     * @return string|false
     */
    public function getPrize($id)
    {
        return ($id && isset($this->prizes[$id])) ? $this->prizes[$id] : false;
    }

    /**
     * @param array $prizes Array structure:
     *      [
     *          [prize_id => prize_value],
     *          [prize_id => prize_value],
     *          ...
     *      ]
     * @return EntryPoint
     */
    public function setPrizes(array $prizes)
    {
        $this->prizes = $prizes;
        return $this;
    }

    /**
     * @param string $id
     * @param string $value
     * @return EntryPoint
     */
    public function addPrize($id, $value)
    {
        $this->prizes[$id] = $value;
        return $this;
    }

}