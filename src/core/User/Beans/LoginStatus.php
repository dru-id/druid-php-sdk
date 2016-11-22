<?php namespace Genetsis\core\User\Beans;

use Genetsis\core\User\Collections\LoginStatusTypes as LoginStatusTypesCollection;

/**
 * This class stores login status.
 *
 * @package   Genetsis
 * @category  Bean
 * @access    private
 */
class LoginStatus
{
    /** @var string $ckusid Ckusid of user logged. */
    private $ckusid = null;
    /** @var string $oid ObjectID of user logged. */
    private $oid = null;
    /** @var string $connect_state One of the values defined in {@link \Genetsis\core\User\Collections\LoginStatusTypes}. */
    private $connect_state = null;

    /**
     * @param array $settings Initial settings. Array structure:
     *      [
     *          'ckusid' => {@see LoginStatus::setCkusid},
     *          'oid' => {@see LoginStatus::setOid},
     *          'connect-state' => {@see LoginStatus::setConnectState},
     *      ]
     */
    public function __construct(array $settings = [])
    {
        if (isset($settings['ckusid'])) { $this->setCkusid($settings['ckusid']); }
        if (isset($settings['oid'])) { $this->setOid($settings['oid']); }
        if (isset($settings['connect-state'])) { $this->setConnectState($settings['connect-state']); }
    }

    /**
     * @return string
     */
    public function getCkusid()
    {
        return $this->ckusid;
    }

    /**
     * @param string $ckusid
     * @return LoginStatus
     */
    public function setCkusid($ckusid)
    {
        $this->ckusid = $ckusid;
        return $this;
    }

    /**
     * @return string
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * @param string $oid
     * @return LoginStatus
     */
    public function setOid($oid)
    {
        $this->oid = $oid;
        return $this;
    }

    /**
     * @return string
     */
    public function getConnectState()
    {
        return $this->connect_state;
    }

    /**
     * @param string $connect_state
     * @return LoginStatus
     */
    public function setConnectState($connect_state)
    {
        $this->connect_state = LoginStatusTypesCollection::check($connect_state) ? $connect_state : LoginStatusTypesCollection::UNKNOWN;
        return $this;
    }

}