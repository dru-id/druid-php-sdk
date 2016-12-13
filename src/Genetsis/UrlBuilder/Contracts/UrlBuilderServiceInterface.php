<?php
namespace Genetsis\UrlBuilder\Contracts;

/**
 * @package  Genetsis
 * @category Contract
 */
interface UrlBuilderServiceInterface {

    /**
     * Returns the login URL.
     *
     * @param string|null $entry_point Entry point for this application. You can find all available entry points at
     *      "oauthconf.xml" file, inside of "<sections/>". If NULL defined then default entry point will be used.
     * @param string $social You can use this parameter to force user to login with a social network. Accepts any of
     *      those defined at {@link \Genetsis\UrlBuilder\Collections\SocialNetworks}
     * @param string $urlCallback Url for callback. A list of valid url is defined in "oauthconf.xml"
     *     If it's NULL default url will be used.
     * @param string $urlCallback Url for callback. A list of valid url is defined in "oauthconf.xml"
     *     If it's NULL default url will be used.
     * @return string The URL for login process.
     */
    public function getUrlLogin($entry_point = null, $social = null, $urlCallback = null, array $prefill = array());

    /**
     * Returns the link for register form page.
     *
     * @param string|null $entry_point Entry point for this application. You can find all available entry points at
     *      "oauthconf.xml" file, inside of "<sections/>". If NULL defined then default entry point will be used.
     * @param string $urlCallback Url for callback. A list of url is defined in "oauthconf.xml"
     *     If it's NULL the default url will be used.
     * @return string The URL for register process.
     */
    public function getUrlRegister($entry_point = null, $urlCallback = null, array $prefill = array());

    /**
     * Returns the link for edit account form page.
     *
     * @param string|null $entry_point Entry point for this application. You can find all available entry points at
     *      "oauthconf.xml" file, inside of "<sections/>". If NULL defined then default entry point will be used.
     * @param string $urlCallback Url for callback. A list of url is defined in "oauthconf.xml"
     *     If it's NULL the default url will be used.
     * @return string The URL for edit account process.
     */
    public function getUrlEditAccount($entry_point = null, $urlCallback = null);

    /**
     * Returns the URL to complete the account for a section (scope) given.
     *
     * @param string|null $entry_point Entry point for this application. You can find all available entry points at
     *      "oauthconf.xml" file, inside of "<sections/>". If NULL defined then default entry point will be used.
     * @return string The URL for complete process.
     */
    public function getUrlCompleteAccount($entry_point = null);

    /**
     * This method is commonly used for promotions or sweepstakes: if a
     * user wants to participate in a promotion, the web client must
     * ensure that the user is logged and have all the fields filled
     * in order to let him participate.
     *
     * - If it is not logged, will return the login URL.
     * - If it is logged the method will check
     *     - If the user have not enough PII to access to a section,
     *       returns the URL needed to force a consumer to fill all the
     *       PII needed to enter into a section
     *     - Else will return false (user logged and completed)
     *
     * The "scope" (section) is a group of fields configured in Genetsis ID for
     * a web client.
     *
     * A section can be also defined as a "part" (section) of the website
     * (web client) that only can be accesed by a user who have filled a
     * set of personal information configured in Genetsis ID (all of the fields
     * required for that section).
     *
     * @param string|null $entry_point Entry point for this application. You can find all available entry points at
     *      "oauthconf.xml" file, inside of "<sections/>".
     * @return string With generated URL. If the user is not connected,
     *     will return login URL.
     * @throws Exception if scope is empty.
     */
    public function buildSignupPromotionUrl($entry_point);

}