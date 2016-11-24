<?php
namespace Genetsis;

use Exception;
use Genetsis\core\User\Beans\Brand;
use Genetsis\core\User;
use Genetsis\core\FileCache;
use Genetsis\core\Http\Collections\HttpMethods as HttpMethodsCollection;
use Genetsis\core\ServiceContainer\Services\ServiceContainer as SC;
use Genetsis\core\User\Collections\LoginStatusTypes as LoginStatusTypesCollection;

/**
 * This class allow you to use the User Api
 *
 * {@link UserApi} makes calls internally to the API to
 * request user Data.
 *
 * @package   Genetsis
 * @category  Bean
 * @version   2.0
 * @access    public
 * @author    Israel Dominguez
 * @revision  Alejandro Sánchez
 * @see       http://developers.dru-id.com
 */
class UserApi
{
    const USER_TTL = 3600;
    const BRANDS_TTL = 3600;

    /**
     * Returns the personal data of the user logged.
     * To check if user is logged, is not necessary call to this method, you must use Identity::isConnected().
     * If you only need the User ID, you must use  {@link getUserLoggedCkusid}
     *
     * @return User An object with the user logged personal data or null if is not logged
     */
    public static function getUserLogged()
    {
        try {
            SC::getLogger()->debug('Get user Logged info', __METHOD__, __LINE__);

            if ((Identity::getThings()->getLoginStatus()!=null)&&(Identity::getThings()->getLoginStatus()->getConnectState() == LoginStatusTypesCollection::CONNECTED)) {
                $user_logged = self::getUsers(array('id' => Identity::getThings()->getLoginStatus()->getCkUsid()));
                if (count($user_logged)>0) {
                    return $user_logged[0];
                }
            }
        } catch (Exception $e) {
            SC::getLogger()->error($e->getMessage(), __METHOD__, __LINE__);
        }
        return null;
    }

    /**
     * Returns User ID of user Logged, stored in Things {@link Things}
     * You must use this method to get the ckusid of user logged
     *
     * @return integer User ID or null if user is not logged
     */
    public static function getUserLoggedCkusid()
    {
        SC::getLogger()->debug('Get user Logged info', __METHOD__, __LINE__);

        if ((Identity::getThings()->getLoginStatus()!=null)&&(Identity::getThings()->getLoginStatus()->getConnectState() == LoginStatusTypesCollection::CONNECTED)) {
            return Identity::getThings()->getLoginStatus()->getCkUsid();
        }

        return null;
    }

    /**
     * Returns ObjectID of user Logged, stored in Things {@link Things}
     * You must use this method to get the oid of user logged
     *
     * @return integer ObjectID or null if user is not logged
     */
    public static function getUserLoggedOid()
    {
        SC::getLogger()->debug('Get user Logged info', __METHOD__, __LINE__);

        if ((Identity::getThings()->getLoginStatus()!=null)&&(Identity::getThings()->getLoginStatus()->getConnectState() == LoginStatusTypesCollection::CONNECTED)) {
            return Identity::getThings()->getLoginStatus()->getOid();
        }

        return null;
    }

    /**
     * Method to get User Logged Profile Image available
     *
     * @param int $width (optional) 150px by default
     * @param int $height (optional) 150px by default
     * @return String url of profile image
     */
    public static function getUserLoggedAvatarUrl($width = 150, $height = 150) {
        return self::getAvatarUrl(UserApi::getUserLogged()->user->oid, $width, $height);
    }

    /**
     * Method to get User Profile Image available
     *
     * @param $userid
     * @param int $width (optional) 150px by default
     * @param int $height (optional) 150px by default
     * @return String url of profile image
     */
    public static function getAvatarUrl($userid, $width = 150, $height = 150)
    {
        try {
            return self::getAvatar($userid, $width, $height, 'false');
        } catch (Exception $e) {
            SC::getLogger()->error($e->getMessage(), __METHOD__, __LINE__);
            return '';
        }
    }

    public static function getAvatarImg($userid, $width = 150, $height = 150)
    {
        try {
            return self::getAvatar($userid, $width, $height, 'true');
        } catch (Exception $e) {
            SC::getLogger()->error($e->getMessage(), __METHOD__, __LINE__);
            return '';
        }
    }

    /**
     * @param $userid
     * @param $width
     * @param $height
     * @param $redirect
     * @return mixed
     * @throws \Exception If an error occurred
     */
    private static function getAvatar($userid, $width, $height, $redirect){
        SC::getLogger()->debug('Get user Avatar', __METHOD__, __LINE__);
        $params = array(
            'width' => $width,
            'height' => $height,
            'redirect' => $redirect
        );

        $response = SC::getHttpService()->execute(SC::getOAuthService()->getConfig()->getApi('api.activityid')->getEndpoint('public_image', true).'/'.$userid, $params, HttpMethodsCollection::GET);

        $ret = null;

        if (isset($response['code']) && ($response['code'] == 200)) {
            if ($redirect === 'true') {
                $ret = $response['result'];
            } else {
                $ret = $response['result']->url;
            }
        } else if (isset($response['code']) && ($response['code'] == 204)) { //user does not have avatar
            if ($redirect === 'true') {
                //$ret = "";
                throw new Exception('not implemented. better use getAvatarUrl or getUserLoggedAvatarUrl');
            } else {
                $ret = "/assets/img/placeholder.png";
            }
        } else {
            throw new Exception('Error [' . __FUNCTION__ . '] - ' . $response['code'] . ' - ' . $response['result']);
        }

        return $ret;
    }

    /**
     * @return array
     */
    public static function getBrands() {
        try {
            $brands = array();

            SC::getLogger()->debug('Get list of Brands', __METHOD__, __LINE__);
            if (!$brands = unserialize(FileCache::get('brands'))) {
                SC::getLogger()->debug('Brands not cached', __METHOD__, __LINE__);
                if (!$client_token = Identity::getThings()->getClientToken()) {
                    throw new Exception('The clientToken is empty');
                }

                $header_params = array(
                    'Authorization' => 'Bearer ' . $client_token->getValue(),
                    'Content-Type' => 'application/json',
                    'From' => '452200208393481-main'
                );

                $response = SC::getHttpService()->execute(SC::getOAuthService()->getConfig()->getApi('api.activityid')->getEndpoint('brands', true), [], HttpMethodsCollection::GET, $header_params);

                if (($response['code'] != 200) || (!isset($response['result']->items))) {
                    throw new Exception('The data retrieved is empty');
                }

                $brands = array();
                foreach ($response['result']->items as $brand) {
                    if (isset($brand->id, $brand->displayName, $brand->displayName->es_ES)) {
                        $brands[] = new Brand(['key' => $brand->id, 'name' => $brand->displayName->es_ES]);
                    }
                }

                FileCache::set('brands', serialize($brands), self::BRANDS_TTL);
            }

        } catch ( Exception $e ) {
            SC::getLogger()->error($e->getMessage(), __METHOD__, __LINE__);
        }
        return $brands;
    }

    /**
     * Delete cache data of a user, must call this method in a post-edit or a post-complete actions
     *
     * @param $ckusid integer Identifier of user to delete cache data, if is not passed, the method get user logged
     *
     * @return void
     * @throws /Exception
     */
    public static function deleteCacheUser($ckusid = null) {
        try {
            SC::getLogger()->debug('Delete cache of user', __METHOD__, __LINE__);

            if ($ckusid == null) {
                if ((Identity::getThings()->getLoginStatus()!=null)&&(Identity::getThings()->getLoginStatus()->getConnectState() == LoginStatusTypesCollection::CONNECTED)) {
                    FileCache::delete('user-' . Identity::getThings()->getLoginStatus()->getCkUsid());
                }
            } else {
                FileCache::delete('user-' . $ckusid);
            }
        } catch ( Exception $e ) {
            SC::getLogger()->error($e->getMessage(), __METHOD__, __LINE__);
        }
        return null;
    }

    /**
     * Returns the user data stored trough the Genetsis ID personal identifier.
     * The identifiers could be: id (ckusid), screenName, email, dni
     * Sample: array('id'=>'XXXX','screenName'=>'xxxx');
     *
     * @param array The Genetsis IDs identifier to search, 'identifier' => 'value'
     * @return array A vector of {@link User} objects with user's
     *     personal data. The array could be empty.
     * @throws /Exception
     */
    public static function getUsers($identifiers)
    {
        $druid_user = array();

        if (is_array($identifiers)) {
            try {
                if (!$druid_user_data = FileCache::get('user-' . reset($identifiers))) {
                    SC::getLogger()->debug('Identifier: ' . reset($identifiers) . ' is Not in Cache System', __METHOD__, __LINE__);

                    $client_token = Identity::getThings()->getClientToken();

                    if (is_null($client_token)) {
                        throw new Exception('The clientToken is empty');
                    }

                    /**
                     * Parameters:
                     * oauth_token: client token
                     * s (select): dynamic user data to be returned
                     * f (from): User
                     * w (where): param with OR w.param1&w.param2...
                     */
                    $params = array();
                    $params['oauth_token'] = $client_token->getValue();
                    $params['s'] = "*";
                    $params['f'] = "User";
                    foreach ($identifiers as $key => $val) {
                        $params['w.' . $key] = $val;
                    }

                    $response = SC::getHttpService()->execute(SC::getOAuthService()->getConfig()->getApi('api.user')->getEndpoint('user', true), $params, HttpMethodsCollection::POST);
                    if (($response['code'] != 200) || (!isset($response['result']->data)) || ($response['result']->count == '0')) {
                        throw new Exception('The data retrieved is empty');
                    }
                    $druid_user = $response['result']->data;
                    FileCache::set('user-' . reset($identifiers), $druid_user, self::USER_TTL);
                } else {
                    SC::getLogger()->debug('Identifier: ' . reset($identifiers) . ' is in Cache System', __METHOD__, __LINE__);
                    $druid_user = json_decode(json_encode($druid_user_data));
                }
            } catch (Exception $e) {
                SC::getLogger()->error($e->getMessage(), __METHOD__, __LINE__);
            }
        }
        return $druid_user;
    }
}




