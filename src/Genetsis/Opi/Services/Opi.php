<?php
namespace Genetsis\Opi\Services;

use Genetsis\core\OAuth\Beans\OAuthConfig\Brand;
use Genetsis\core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\DruID;
use Genetsis\Opinator\Contracts\OpiServiceInterface;

/**
 * This class wraps all methods for interactions with OPI
 *
 * @package   Genetsis
 * @category  Helper
 * @version   1.0
 * @access    private
 */
class Opi implements OpiServiceInterface
{

    /** @var DruID $druid */
    private $druid;
    /** @var OAuthServiceInterface $oauth */
    private $oauth;

    /**
     * @param DruID $druid
     * @param OAuthServiceInterface $oauth
     */
    public function __construct(DruID $druid, OAuthServiceInterface $oauth)
    {
        $this->druid = $druid;
        $this->oauth = $oauth;
    }

    /**
     * @inheritDoc
     */
    public function open($opi = null, $redirect_url = null)
    {
        header("Location: " . $this->get($opi, $redirect_url));
        die();
    }

    /**
     * @inheritDoc
     */
    public function get($opi = null, $redirect_url = null)
    {
        if (!$opi) {
            $opi = $this->oauth->getConfig()->getOpi();
        }
        if (!$opi) {
            throw new \InvalidArgumentException('Invalid Opi identifier.');
        }

        $params = array(
            "id" => urlencode($this->druid->userApi()->getUserLoggedOid()),
            "sc" => urlencode(
                ($this->oauth->getConfig()->getBrand() instanceof Brand) ? $this->oauth->getConfig()->getBrand()->getName() : ''
            ),
            "carry_url" => urlencode($redirect_url));

        $info = $this->druid->userApi()->getUserLogged();

        // Age.
        try {
            if (isset($info->user, $info->user->user_data, $info->user->user_data->birthday, $info->user->user_data->birthday->value) && $info->user->user_data->birthday->value) {
                $birthday = explode('/', $info->user->user_data->birthday->value);
                $age = (date("md", date("U", mktime(0, 0, 0, $birthday[2], $birthday[1], $birthday[0]))) > date("md")
                    ? ((date("Y") - $birthday[2]) - 1)
                    : (date("Y") - $birthday[2]));
                if (18 <= $age && $age <= 24) {
                    $age = 1;
                } elseif (25 <= $age && $age <= 34) {
                    $age = 2;
                } elseif (35 <= $age && $age <= 44) {
                    $age = 3;
                } elseif (45 <= $age && $age <= 64) {
                    $age = 4;
                } else {
                    $age = false;
                }
                if ($age) {
                    $params["carry_edad"] =  $age;
                }
            }
        } catch (\Exception $e) {}

        // Gender
        try {
            if (isset($info->user, $info->user->user_data, $info->user->user_data->gender, $info->user->user_data->gender->vid) && $info->user->user_data->gender->vid) {
                if ($info->user->user_data->gender->vid == 1) {
                    $params['carry_sexo'] = 2;
                } elseif ($info->user->user_data->gender->vid == 2) {
                    $params['carry_sexo'] = 1;
                }
            }
        } catch (\Exception $e) {}

        $query = array();
        foreach($params as $param => $value) {
            $query[] = "$param=$value";
        }

        return $this->oauth->getConfig()->getApi('opi')->getEndpoint('rules', true).'/'.$opi.'?'.implode('&', $query);
    }

}
