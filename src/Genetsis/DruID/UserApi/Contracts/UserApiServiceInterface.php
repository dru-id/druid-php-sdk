<?php
namespace Genetsis\DruID\UserApi\Contracts;

/**
 * @package  Genetsis\DruID
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
    public function logoutUser();


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
    public function checkUserComplete($scope);

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
    public function checkUserNeedAcceptTerms($scope);
}