<?php
namespace Genetsis;

use Exception;
use Genetsis\core\OAuthConfig;

/**
 * This class wraps all methods for interactions with OPI
 *
 * @package   Genetsis
 * @category  Helper
 * @version   1.0
 * @access    private
 */
class Opi
{

    /**
     * redirect to a specified opi
     */
    public static function open($opi=false, $redirect_url=false, $oid=false)
    {
        header("Location: " . self::get($opi, $redirect_url, $oid));
        die();
    }

    /**
     * get url redirect to a specified opi
     */
    public static function get($opi=false, $redirect_url=false, $oid=false)
    {
        if (!$opi) {
            $opi = OAuthConfig::getOpi();
        }

        if (!$opi) {
            throw new Exception ("You must pass OPI as param, or define it in <data> part of oauthconf.xml");
        }

        $info = !$oid ? UserApi::getUserLogged() : UserApi::getUsers(array('id' => $oid))[0];

        $params = array(
            "id" => $info->user->oid,
            "sc" => urlencode(OAuthConfig::getBrand()),
            "carry_url" => urlencode($redirect_url));

        // Gender
        $opi_gender = false;
        try {
            $gender = isset($info->user->user_data->gender) ? $info->user->user_data->gender->vid : null;
            if ($gender == 1) { // Female
                $opi_gender = 2;
            } else if ($gender == 2) { //MAle
                $opi_gender = 1;
            }
        } catch (Exception $e) {}

        // Age
        if ($opi_gender) {
            $params['carry_sexo'] =  $opi_gender;
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
            } catch (Exception $e) {}
        }

        $query = array();

        foreach($params as $param => $value) {
            $query[] = "$param=$value";
        }

        return OAuthConfig::getApiUrl('opi','base_url') . OAuthConfig::getApiUrl('opi','rules') . "/" . $opi . "?" . implode('&', $query);
    }
}
