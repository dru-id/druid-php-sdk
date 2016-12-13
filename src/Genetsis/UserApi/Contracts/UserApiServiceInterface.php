<?php
namespace Genetsis\UserApi\Contracts;

/**
 * @package  Genetsis
 * @category Contract
 */
interface UserApiServiceInterface {

    /**
     * Returns the personal data of the user logged.
     * To check if user is logged, is not necessary call to this method, you must use Identity::isConnected().
     * If you only need the User ID, you must use  {@link getUserLoggedCkusid}
     *
     * @return \stdClass An object with the user logged personal data or null if is not logged
     */
    public function getUserLogged();

    /**
     * Returns User ID of user Logged, stored in Things {@link Things}
     * You must use this method to get the ckusid of user logged
     *
     * @return integer User ID or null if user is not logged
     */
    public function getUserLoggedCkusid();

    /**
     * Returns ObjectID of user Logged, stored in Things {@link Things}
     * You must use this method to get the oid of user logged
     *
     * @return integer ObjectID or null if user is not logged
     */
    public function getUserLoggedOid();

    /**
     * Method to get User Logged Profile Image available
     *
     * @param int $width (optional) 150px by default
     * @param int $height (optional) 150px by default
     * @return String url of profile image
     */
    public function getUserLoggedAvatarUrl($width = 150, $height = 150);

    /**
     * Method to get User Profile Image available
     *
     * @param $userid
     * @param int $width (optional) 150px by default
     * @param int $height (optional) 150px by default
     * @return String url of profile image
     */
    public function getAvatarUrl($userid, $width = 150, $height = 150);

    public function getAvatarImg($userid, $width = 150, $height = 150);

    /**
     * @return array
     */
    public function getBrands();

    /**
     * Delete cache data of a user, must call this method in a post-edit or a post-complete actions
     *
     * @param $ckusid integer Identifier of user to delete cache data, if is not passed, the method get user logged
     *
     * @return void
     * @throws /Exception
     */
    public function deleteCacheUser($ckusid = null);

    /**
     * Returns the user data stored trough the Genetsis ID personal identifier.
     * The identifiers could be: id (ckusid), screenName, email, dni
     * Sample: array('id'=>'XXXX','screenName'=>'xxxx');
     *
     * @param array $identifiers The Genetsis IDs identifier to search, 'identifier' => 'value'
     * @return array A vector of {@link User} objects with user's
     *     personal data. The array could be empty.
     * @throws /Exception
     */
    public function getUsers($identifiers);

}