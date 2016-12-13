<?php
namespace Genetsiss;
die('Deprecated!');
use Exception;
use Genetsis\core\OAuth;
use Genetsis\core\OAuthConfig;
use Genetsis\core\Request;

use Genetsis\core\OAuth\Beans\OAuthConfig\Brand;
use Genetsis\core\ServiceContainer\Services\ServiceContainer as SC;

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
    public static function open($opi=false, $redirect_url=false)
    {
        header("Location: " . self::get($opi, $redirect_url));
        die();
    }

    /**
     * get url redirect to a specified opi
     */

    public static function get($opi=false, $redirect_url=false)
    {
        if (!$opi) {
            $opi = SC::getOAuthService()->getConfig()->getOpi();
        }

        if (!$opi) {
            throw new Exception ("You must pass OPI as param, or define it in <data> part of oauthconf.xml");
        }

        $params = array(
            "id" => urlencode(UserApi::getUserLoggedOid()),
            "sc" => urlencode(
                (SC::getOAuthService()->getConfig()->getBrand() instanceof Brand) ? SC::getOAuthService()->getConfig()->getBrand()->getName() : ''
            ),
            "carry_url" => urlencode($redirect_url));

        $info = UserApi::getUserLogged();

        $opi_age = false;
        $opi_gender = false;

        try {
            $birthday = isset($info->user->user_data->birthday) ? $info->user->user_data->birthday->value : null;

            if($birthday != null){
            	$birthday = explode("/", $birthday);

            	$age = (date("md", date("U", mktime(0, 0, 0, $birthday[2], $birthday[1], $birthday[0]))) > date("md")
                	? ((date("Y") - $birthday[2]) - 1)
                	: (date("Y") - $birthday[2]));
            	if (18 <= $age && $age <= 24) {
                	$opi_age = 1;
            	} else if (25 <= $age && $age <= 34) {
                	$opi_age = 2;
            	} if (35 <= $age && $age <= 44) {
                	$opi_age = 3;
            	} if (45 <= $age && $age <= 64) {
                	$opi_age = 4;
            	}
            }
        } catch (Exception $e) {}

        try {
            $gender = isset($info->user->user_data->gender) ? $info->user->user_data->gender->vid : null;
            if ($gender == 1) {
                $opi_gender = 2;
            } else if ($gender == 2) {
                $opi_gender = 1;
            }
        } catch (Exception $e) {}

        if ($opi_age) {
            $params["carry_edad"] =  $opi_age;
        }

        if ($opi_gender) {
            $params["carry_sexo"] =  $opi_gender;
        }

        $query = array();

        foreach($params as $param => $value) {
            $query[] = "$param=$value";
        }

        return SC::getOAuthService()->getConfig()->getApi('opi')->getEndpoint('rules', true).'/'.$opi.'?'.implode('&', $query);
    }
}