<?php
namespace Genetsis;

use Exception;
use Genetsis\core\OAuth;
use Genetsis\core\OAuthConfig;
use Genetsis\core\Request;

/**
 * This class wraps all methods for interactions with OAuth service,
 * for user authentication and validation. Also generates the URLs to
 * perform this operations as register or login.
 *
 * @package   Genetsis
 * @category  Helper
 * @version   1.0
 * @access    private
 */
class Opi
{

    private static $OPINATOR_SERVER = "https://www.opinator.com/opi/r";

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
            $opi = OAuthConfig::getOpi();
        }

        if (!$opi) {
            throw new Exception ("You must pass OPI as param, or define it in <data> part of oauthconf.xml");
        }

        $params = array(
            "id" => urlencode(UserApi::getUserLoggedOid()),
            "sc" => urlencode(OAuthConfig::getBrand()),
            "carry_url" => urlencode($redirect_url));

        $query = array();

        foreach($params as $param => $value) {
            $query[] = "$param=$value";
        }

        return self::$OPINATOR_SERVER . "/" . $opi . "?" . implode('&', $query);
    }
}
