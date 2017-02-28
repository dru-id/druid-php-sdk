<?php
namespace Genetsis\DruID\Identity\Contracts;

use Genetsis\DruID\Core\User\Beans\Things;

/**
 * @package  Genetsis\DruID
 * @category Contract
 */
interface IdentityServiceInterface {

    /**
     * This method verifies the authorization tokens (client_token,
     * access_token and refresh_token). Also updates the web client status,
     * storing the client_token, access_token and refresh tokend and
     * login_status in Things {@link Things}.
     *
     * Is INVOKE ON EACH REQUEST in order to check and update
     * the status of the user (not logged, logged or connected), and
     * verify that every token that you are gonna use before is going to be
     * valid.
     *
     * @return void
     */
    public function synchronizeSessionWithServer();

    /**
     * Helper to check if the user is connected (logged on Genetsis ID)
     *
     * @return boolean TRUE if is logged, FALSE otherwise.
     */
    public function isConnected();

    /**
     * Helper to access library data
     *
     * @return Things
     */
    public function getThings();

    /**
     * In that case, the url of "post-login" will retrieve an authorization
     * code as a GET parameter.
     *
     * Once the authorization code is provided to the web client, the SDK
     * will send it again to Genetsis ID at "token_endpoint" to obtain the
     * "access_token" of the user and create the cookie.
     *
     * This method is needed to authorize user when the web client takes
     * back the control of the browser.
     *
     * @param string $code Authorization code returned by Genetsis ID.
     * @return void
     * @throws /Exception
     */
    public function authorizeUser($code);

    /**
     * Deletes the local data of the user's session.
     *
     * @return void
     */
    public function clearLocalSessionData();

}