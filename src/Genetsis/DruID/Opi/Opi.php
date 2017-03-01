<?php
namespace Genetsis\DruID\Opi;

use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Brand;
use Genetsis\DruID\Core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\DruID\Opi\Contracts\OpiServiceInterface;
use Genetsis\DruID\UserApi\Contracts\UserApiServiceInterface;

/**
 * This class wraps all methods for interactions with OPI
 *
 * @package   Genetsis\DruID
 * @category  Helper
 * @version   1.0
 * @access    private
 */
class Opi implements OpiServiceInterface
{

    /** @var UserApiServiceInterface $user_api */
    private $user_api;
    /** @var OAuthServiceInterface $oauth */
    private $oauth;

    /**
     * @param UserApiServiceInterface $user_api
     * @param OAuthServiceInterface $oauth
     */
    public function __construct(UserApiServiceInterface $user_api, OAuthServiceInterface $oauth)
    {
        $this->user_api = $user_api;
        $this->oauth = $oauth;
    }

    /**
     * @inheritDoc
     */
    public function open($opi = null, $redirect_url = null, $oid = null)
    {
        header('Location: ' . $this->get($opi, $redirect_url, $oid));
        exit();
    }

    /**
     * @inheritDoc
     */
    public function get($opi = null, $redirect_url = null, $oid = null)
    {
        if (!$opi) {
            $opi = $this->oauth->getConfig()->getOpi();
        }
        if (!$opi) {
            throw new \InvalidArgumentException('You must pass OPI as param, or define it in < data > part of oauthconf.xml');
        }

        $info = $oid
            ? $this->user_api->getUsers(['id' => $oid])[0]
            : $this->user_api->getUserLogged();
        $params = [
            'id' => $info->user->oid,
            'sc' => urlencode(
                ($this->oauth->getConfig()->getBrand() instanceof Brand)
                    ? $this->oauth->getConfig()->getBrand()->getName()
                    : ''
            ),
            'carry_url' => urlencode($redirect_url)
        ];

        // Gender
        $opi_gender = false;
        try {
            if (isset($info->user, $info->user->user_data, $info->user->user_data->gender, $info->user->user_data->gender->vid) && $info->user->user_data->gender->vid) {
                if ($info->user->user_data->gender->vid == 1) { // Female
                    $opi_gender = 2;
                } elseif ($info->user->user_data->gender->vid == 2) { // Male
                    $opi_gender = 1;
                }
            }
        } catch (\Exception $e) {}

        // Age
        if ($opi_gender) {
            $params['carry_sexo'] = $opi_gender;
            try {
                if (isset($info->user, $info->user->user_data, $info->user->user_data->birthday, $info->user->user_data->birthday->value) && $info->user->user_data->birthday->value) {
                    if (($user_date = @date_create_from_format('d/m/Y', $info->user->user_data->birthday->value)) instanceof \DateTime) {
                        $opi_age = (new \DateTime())->diff($user_date);
                        $opi_age = ($opi_age instanceof \DateInterval)
                            ? $opi_age->y
                            : false;
                        switch ($opi_gender) {
                            case 1: //Male
                                if ((18 <= $opi_age) && ($opi_age <= 24)) {
                                    $opi_age = 1;
                                } elseif ((25 <= $opi_age) && ($opi_age <= 34)) {
                                    $opi_age = 2;
                                } elseif ((35 <= $opi_age) && ($opi_age <= 44)) {
                                    $opi_age = 3;
                                } elseif ((45 <= $opi_age) && ($opi_age <= 64)) {
                                    $opi_age = 4;
                                } else {
                                    $opi_age = false;
                                }
                                break;
                            case 2: //Female
                                if ((18 <= $opi_age) && ($opi_age <= 24)) {
                                    $opi_age = 1;
                                } elseif ((25 <= $opi_age) && ($opi_age <= 34)) {
                                    $opi_age = 2;
                                } elseif ((35 <= $opi_age) && ($opi_age <= 64)) {
                                    $opi_age = 3;
                                } else {
                                    $opi_age = false;
                                }
                                break;
                        }
                        if ($opi_age !== false) {
                            $params['carry_edad'] = $opi_age;
                        }
                    }
                }
            } catch (\Exception $e) {}
        }

        $query = [];
        foreach($params as $param => $value) {
            $query[] = "$param=$value";
        }
        return $this->oauth->getConfig()->getApi('opi')->getEndpoint('rules', true).'/'.$opi.'?'.implode('&', $query);
    }
}
