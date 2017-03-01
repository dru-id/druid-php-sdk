<?php
namespace Genetsis\DruID\UserApi;

use Doctrine\Common\Cache\Cache;
use Exception;
use Genetsis\DruID\Core\Http\Contracts\HttpServiceInterface;
use Genetsis\DruID\Core\Http\Exceptions\RequestException;
use Genetsis\DruID\Core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\DruID\Core\User\Beans\Brand;
use Genetsis\DruID\Core\User;
use Genetsis\DruID\Core\User\Collections\LoginStatusTypes as LoginStatusTypesCollection;
use Genetsis\DruID\DruID;
use Genetsis\DruID\DruIDFacade;
use Genetsis\DruID\Identity\Contracts\IdentityServiceInterface;
use Genetsis\DruID\UserApi\Contracts\UserApiServiceInterface;
use GuzzleHttp\Psr7\Uri;
use Psr\Log\LoggerInterface;

/**
 * This class allow you to use the User Api
 *
 * {@link UserApi} makes calls internally to the API to
 * request user Data.
 *
 * @package   Genetsis\DruID
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

    /** @var IdentityServiceInterface $identity */
    private $identity;
    /** @var DruID $druid */
    private $druid;
    /** @var OAuthServiceInterface $oauth */
    private $oauth;
    /** @var HttpServiceInterface $http */
    private $http;
    /** @var LoggerInterface $logger */
    private $logger;
    /** @var \Doctrine\Common\Cache\Cache $cache */
    private $cache;

    /**
     * @param IdentityServiceInterface $identity
     * @param OAuthServiceInterface $oauth
     * @param HttpServiceInterface $http
     * @param LoggerInterface $logger
     * @param \Doctrine\Common\Cache\Cache $cache
     */
    public function __construct(IdentityServiceInterface $identity, OAuthServiceInterface $oauth, HttpServiceInterface $http, LoggerInterface $logger, Cache $cache)
    {
        $this->identity = $identity;
        $this->oauth = $oauth;
        $this->http = $http;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function getUserLogged()
    {
        try {
            $this->logger->debug('Get user Logged info', ['method' => __METHOD__, 'line' => __LINE__]);

            if (($this->identity->getThings()->getLoginStatus()!=null)&&($this->identity->getThings()->getLoginStatus()->getConnectState() == LoginStatusTypesCollection::CONNECTED)) {
                $user_logged = $this->getUsers(array('id' => $this->identity->getThings()->getLoginStatus()->getCkUsid()));
                if (count($user_logged)>0) {
                    return $user_logged[0];
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getUserLoggedCkusid()
    {
        $this->logger->debug('Get user Logged info', ['method' => __METHOD__, 'line' => __LINE__]);

        if (($this->identity->getThings()->getLoginStatus()!=null)&&($this->identity->getThings()->getLoginStatus()->getConnectState() == LoginStatusTypesCollection::CONNECTED)) {
            return $this->identity->getThings()->getLoginStatus()->getCkUsid();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getUserLoggedOid()
    {
        $this->logger->debug('Get user Logged info', ['method' => __METHOD__, 'line' => __LINE__]);

        if (($this->identity->getThings()->getLoginStatus()!=null)&&($this->identity->getThings()->getLoginStatus()->getConnectState() == LoginStatusTypesCollection::CONNECTED)) {
            return $this->identity->getThings()->getLoginStatus()->getOid();
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
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
            return '';
        }
    }

    public function getAvatarImg($userid, $width = 150, $height = 150)
    {
        try {
            return $this->getAvatar($userid, $width, $height, true);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
            return '';
        }
    }

    /**
     * @param $userid
     * @param $width
     * @param $height
     * @param boolean $redirect
     * @return mixed
     * @throws \Exception If an error occurred
     */
    private function getAvatar($userid, $width, $height, $redirect){
        $this->logger->debug('Get user Avatar', ['method' => __METHOD__, 'line' => __LINE__]);
        $response = $this->http->request('GET', $this->oauth->getConfig()->getApi('api.activityid')->getEndpoint('public_image', true).'/'.$userid, [
            'query' => [
                'width' => $width,
                'height' => $height,
                'redirect' => ($redirect ? 'true' : 'false')
            ]
        ]);

        $ret = null;

        if ($response->getStatusCode() == 200) {
            if ($redirect) {
                $ret = (string)$response->getBody();
            } else {
                $response = @json_decode((string)$response->getBody(), true);
                if (is_null($response) || !is_array($response)) {
                    throw new RequestException('Server has responded with an invalid JSON data.');
                }
                if (isset($response['url'])) {
                    $ret = $response['url'];
                }
            }
        } elseif ($response->getStatusCode() == 204) {
            if ($redirect) {
                //$ret = "";
                throw new \Exception('not implemented. better use getAvatarUrl or getUserLoggedAvatarUrl');
            } else {
                $ret = "/assets/img/placeholder.png";
            }
        } else {
            throw new \Exception('Error [' . __FUNCTION__ . '] - ' . $response->getStatusCode() . ' - ' . (string)$response->getBody());
        }

        return $ret;
    }

    /**
     * @inheritDoc
     */
    public function getBrands()
    {
        try {
            $this->logger->debug('Get list of Brands', ['method' => __METHOD__, 'line' => __LINE__]);
            if (!$this->cache->contains('brands') || !($brands = @unserialize($this->cache->fetch('brands')))) {
                $this->logger->debug('Brands not cached', ['method' => __METHOD__, 'line' => __LINE__]);
                if (!$client_token = $this->identity->getThings()->getClientToken()) {
                    throw new \Exception('The clientToken is empty');
                }
                $response = $this->http->request('GET', $this->oauth->getConfig()->getApi('api.activityid')->getEndpoint('brands', true), [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $client_token->getValue(),
                        'Content-Type' => 'application/json',
                        'From' => '452200208393481-main'
                    ]
                ]);
                if ($response->getStatusCode() != 200) {
                    throw new \Exception('The data retrieved is empty');
                }
                $response = @json_decode((string)$response->getBody(), true);
                if (is_null($response) || !is_array($response)) {
                    throw new RequestException('Server has responded with an invalid JSON data.');
                }

                $brands = array();
                if (isset($response['items'])) {
                    foreach ($response['items'] as $brand) {
                        if (isset($brand['id'], $brand['displayName'], $brand['displayName']['es_ES'])) {
                            $brands[] = new Brand(['key' => $brand['id'], 'name' => $brand['displayName']['es_ES']]);
                        }
                    }
                }

                $this->cache->save('brands', serialize($brands), self::BRANDS_TTL);
            }

            return $brands;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteCacheUser($ckusid = null)
    {
        try {
            $this->logger->debug('Delete cache of user', ['method' => __METHOD__, 'line' => __LINE__]);

            if ($ckusid == null) {
                if (($this->identity->getThings()->getLoginStatus()!=null)&&($this->identity->getThings()->getLoginStatus()->getConnectState() == LoginStatusTypesCollection::CONNECTED)) {
                    $this->cache->delete('user-' . $this->identity->getThings()->getLoginStatus()->getCkUsid());
                }
            } else {
                $this->cache->delete('user-' . $ckusid);
            }
        } catch ( Exception $e ) {
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
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
                $cache_key = 'user-' . reset($identifiers);
                if (!$this->cache->contains($cache_key) || !($druid_user_data = $this->cache->fetch($cache_key))) {
                    $this->logger->debug('Identifier: ' . reset($identifiers) . ' is Not in Cache System', ['method' => __METHOD__, 'line' => __LINE__]);

                    $client_token = $this->identity->getThings()->getClientToken();

                    if (is_null($client_token)) {
                        throw new \Exception('The clientToken is empty');
                    }
                    $params = [
                        'oauth_token' => $client_token->getValue(),
                        's' => "*", // s (select): dynamic user data to be returned
                        'f' => "User" // f (from): User
                    ];
                    foreach ($identifiers as $key => $val) {
                        // w (where): param with OR w.param1=val1&w.param2=val2...
                        $params['w.' . $key] = $val;
                    }
                    $response = $this->http->request('POST', $this->oauth->getConfig()->getApi('api.user')->getEndpoint('user', true), [
                        'form_params' => $params,
                        'headers' => [
                            'Content-Type' => 'application/x-www-form-urlencoded'
                        ]
                    ]);
                    if ($response->getStatusCode() != 200) {
                        throw new \Exception('The data retrieved is empty');
                    }
                    $response = @json_decode((string)$response->getBody());
                    if (is_null($response)) {
                        throw new RequestException('Server has responded with an invalid JSON data.');
                    }
                    if (!isset($response->data) || (isset($response->count) && ($response->count == 0))) {
                        throw new \Exception('The data retrieved is empty');
                    }

                    $druid_user = $response->data;
                    $this->cache->save($cache_key, $druid_user, self::USER_TTL);
                } else {
                    $this->logger->debug('Identifier: ' . reset($identifiers) . ' is in Cache System', ['method' => __METHOD__, 'line' => __LINE__]);
                    $druid_user = json_decode(json_encode($druid_user_data));
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
            }
        }
        return $druid_user;
    }

    /**
     * Performs the logout process.
     *
     * It makes:
     * - The logout call to Genetsis ID
     * - Clear cookies
     * - Purge Tokens and local data for the logged user
     *
     * @return void
     * @throws \Exception
     */
    public function logoutUser()
    {
        try {
            if (($this->identity->getThings()->getAccessToken() != null) && ($this->identity->getThings()->getRefreshToken() != null)) {
                $this->logger->info('User Single Sign Logout', ['method' => __METHOD__, 'line' => __LINE__]);
                $this->deleteCacheUser($this->identity->getThings()->getLoginStatus()->getCkUsid());

                $this->oauth->doLogout((string)$this->oauth->getConfig()->getEndPoint('logout_endpoint'));
                $this->identity->clearLocalSessionData();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
        }
    }

    /**
     * Checks if the user have been completed all required fields for that
     * section.
     *
     * The "scope" (section) is a group of fields configured in Genetsis ID for
     * a web client.
     *
     * A section can be also defined as a "part" (section) of the website
     * (web client) that only can be accesed by a user who have filled a
     * set of personal information configured in Genetsis ID (all of the fields
     * required for that section).
     *
     * This method is commonly used for promotions or sweepstakes: if a
     * user wants to participate in a promotion, the web client must
     * ensure that the user have all the fields filled in order to let him
     * participate.
     *
     * @param $scope string Section-key identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @throws \Exception
     * @return boolean TRUE if the user have already completed all the
     *     fields needed for that section, false in otherwise
     */
    public function checkUserComplete($scope)
    {
        $userCompleted = false;
        try {
            $this->logger->info('Checking if the user has filled its data out for this section:' . $scope, ['method' => __METHOD__, 'line' => __LINE__]);

            if ($this->identity->isConnected()) {
                $userCompleted = $this->oauth->doCheckUserCompleted($this->oauth->getConfig()->getApi('api.user')->getEndpoint('user', true), $scope);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
        }
        return $userCompleted;
    }

    /**
     * Checks if the user needs to accept terms and conditions for that section.
     *
     * The "scope" (section) is a group of fields configured in DruID for
     * a web client.
     *
     * A section can be also defined as a "part" (section) of the website
     * (web client) that only can be accessed by a user who have filled a
     * set of personal information configured in DruID.
     *
     * @param $scope string Section-key identifier of the web client. The
     *     section-key is located in "oauthconf.xml" file.
     * @throws \Exception
     * @return boolean TRUE if the user need to accept terms and conditions, FALSE if it has
     *      already accepted them.
     */
    public function checkUserNeedAcceptTerms($scope)
    {
        $status = false;
        try {
            $this->logger->info('Checking if the user has accepted terms and conditions for this section:' . $scope, ['method' => __METHOD__, 'line' => __LINE__]);

            if ($this->identity->isConnected()) {
                $status = $this->oauth->doCheckUserNeedAcceptTerms($this->oauth->getConfig()->getApi('api.user')->getEndpoint('user', true), $scope);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
        }
        return $status;
    }

}




