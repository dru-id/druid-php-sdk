<?php
namespace Genetsis\UserApi\Services;

use Exception;
use Genetsis\core\Http\Contracts\HttpServiceInterface;
use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
use Genetsis\core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\core\User\Beans\Brand;
use Genetsis\core\User;
use Genetsis\core\FileCache;
use Genetsis\core\Http\Collections\HttpMethods as HttpMethodsCollection;
use Genetsis\core\User\Collections\LoginStatusTypes as LoginStatusTypesCollection;
use Genetsis\DruID;
use Genetsis\UserApi\Contracts\UserApiServiceInterface;

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
class UserApi implements UserApiServiceInterface
{
    const USER_TTL = 3600;
    const BRANDS_TTL = 3600;

    /** @var OAuthServiceInterface $oauth */
    protected $oauth;
    /** @var HttpServiceInterface $http */
    protected $http;
    /** @var LoggerServiceInterface $logger */
    protected $logger;

    /**
     * @param OAuthServiceInterface $oauth
     * @param HttpServiceInterface $http
     * @param LoggerServiceInterface $logger
     */
    public function __construct(OAuthServiceInterface $oauth, HttpServiceInterface $http, LoggerServiceInterface $logger)
    {
        $this->oauth = $oauth;
        $this->http = $http;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getUserLogged()
    {
        try {
            $this->logger->debug('Get user Logged info', __METHOD__, __LINE__);

            if ((DruID::identity()->getThings()->getLoginStatus()!=null)&&(DruID::identity()->getThings()->getLoginStatus()->getConnectState() == LoginStatusTypesCollection::CONNECTED)) {
                $user_logged = $this->getUsers(array('id' => DruID::identity()->getThings()->getLoginStatus()->getCkUsid()));
                if (count($user_logged)>0) {
                    return $user_logged[0];
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), __METHOD__, __LINE__);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getUserLoggedCkusid()
    {
        $this->logger->debug('Get user Logged info', __METHOD__, __LINE__);

        if ((DruID::identity()->getThings()->getLoginStatus()!=null)&&(DruID::identity()->getThings()->getLoginStatus()->getConnectState() == LoginStatusTypesCollection::CONNECTED)) {
            return DruID::identity()->getThings()->getLoginStatus()->getCkUsid();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getUserLoggedOid()
    {
        $this->logger->debug('Get user Logged info', __METHOD__, __LINE__);

        if ((DruID::identity()->getThings()->getLoginStatus()!=null)&&(DruID::identity()->getThings()->getLoginStatus()->getConnectState() == LoginStatusTypesCollection::CONNECTED)) {
            return DruID::identity()->getThings()->getLoginStatus()->getOid();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getUserLoggedAvatarUrl($width = 150, $height = 150)
    {
        return $this->getAvatarUrl($this->getUserLogged()->user->oid, $width, $height);
    }

    /**
     * @inheritDoc
     */
    public function getAvatarUrl($userid, $width = 150, $height = 150)
    {
        try {
            return $this->getAvatar($userid, $width, $height, 'false');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), __METHOD__, __LINE__);
            return '';
        }
    }

    public function getAvatarImg($userid, $width = 150, $height = 150)
    {
        try {
            return $this->getAvatar($userid, $width, $height, 'true');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), __METHOD__, __LINE__);
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
    private function getAvatar($userid, $width, $height, $redirect){
        $this->logger->debug('Get user Avatar', __METHOD__, __LINE__);
        $params = array(
            'width' => $width,
            'height' => $height,
            'redirect' => $redirect
        );

        $response = $this->http->execute($this->oauth->getConfig()->getApi('api.activityid')->getEndpoint('public_image', true).'/'.$userid, $params, HttpMethodsCollection::GET);

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
                throw new \Exception('not implemented. better use getAvatarUrl or getUserLoggedAvatarUrl');
            } else {
                $ret = "/assets/img/placeholder.png";
            }
        } else {
            throw new \Exception('Error [' . __FUNCTION__ . '] - ' . $response['code'] . ' - ' . $response['result']);
        }

        return $ret;
    }

    /**
     * @inheritDoc
     */
    public function getBrands()
    {
        try {
            $this->logger->debug('Get list of Brands', __METHOD__, __LINE__);
            if (!$brands = unserialize(FileCache::get('brands'))) {
                $this->logger->debug('Brands not cached', __METHOD__, __LINE__);
                if (!$client_token = DruID::identity()->getThings()->getClientToken()) {
                    throw new \Exception('The clientToken is empty');
                }

                $header_params = array(
                    'Authorization' => 'Bearer ' . $client_token->getValue(),
                    'Content-Type' => 'application/json',
                    'From' => '452200208393481-main'
                );

                $response = $this->http->execute($this->oauth->getConfig()->getApi('api.activityid')->getEndpoint('brands', true), [], HttpMethodsCollection::GET, $header_params);

                if (($response['code'] != 200) || (!isset($response['result']->items))) {
                    throw new \Exception('The data retrieved is empty');
                }

                $brands = array();
                foreach ($response['result']->items as $brand) {
                    if (isset($brand->id, $brand->displayName, $brand->displayName->es_ES)) {
                        $brands[] = new Brand(['key' => $brand->id, 'name' => $brand->displayName->es_ES]);
                    }
                }

                FileCache::set('brands', serialize($brands), self::BRANDS_TTL);
            }

            return $brands;

        } catch ( Exception $e ) {
            $this->logger->error($e->getMessage(), __METHOD__, __LINE__);
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteCacheUser($ckusid = null)
    {
        try {
            $this->logger->debug('Delete cache of user', __METHOD__, __LINE__);

            if ($ckusid == null) {
                if ((DruID::identity()->getThings()->getLoginStatus()!=null)&&(DruID::identity()->getThings()->getLoginStatus()->getConnectState() == LoginStatusTypesCollection::CONNECTED)) {
                    FileCache::delete('user-' . DruID::identity()->getThings()->getLoginStatus()->getCkUsid());
                }
            } else {
                FileCache::delete('user-' . $ckusid);
            }
        } catch ( Exception $e ) {
            $this->logger->error($e->getMessage(), __METHOD__, __LINE__);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getUsers($identifiers)
    {
        $druid_user = array();

        if (is_array($identifiers)) {
            try {
                if (!$druid_user_data = FileCache::get('user-' . reset($identifiers))) {
                    $this->logger->debug('Identifier: ' . reset($identifiers) . ' is Not in Cache System', __METHOD__, __LINE__);

                    $client_token = DruID::identity()->getThings()->getClientToken();

                    if (is_null($client_token)) {
                        throw new \Exception('The clientToken is empty');
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

                    $response = $this->http->execute($this->oauth->getConfig()->getApi('api.user')->getEndpoint('user', true), $params, HttpMethodsCollection::POST);
                    if (($response['code'] != 200) || (!isset($response['result']->data)) || ($response['result']->count == '0')) {
                        throw new \Exception('The data retrieved is empty');
                    }
                    $druid_user = $response['result']->data;
                    FileCache::set('user-' . reset($identifiers), $druid_user, self::USER_TTL);
                } else {
                    $this->logger->debug('Identifier: ' . reset($identifiers) . ' is in Cache System', __METHOD__, __LINE__);
                    $druid_user = json_decode(json_encode($druid_user_data));
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), __METHOD__, __LINE__);
            }
        }
        return $druid_user;
    }

}




