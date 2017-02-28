<?php
namespace Genetsis\DruID\Opi\Contracts;

/**
 * @package  Genetsis\DruID
 * @category Contract
 */
interface OpiServiceInterface {

    /**
     * Performs a redirection to an specific Opi.
     *
     * @param string|null $opi Opi identifier. You can find all available Opinator IDs at "oauthconf.xml" file, inside
     *      of "<data/>". If NULL defined then default Opi identifier will be used.
     * @param string|null $redirect_url
     * @return void
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function open($opi = null, $redirect_url = null);

    /**
     * Returns the URL to an specific Opi.
     *
     * @param string|null $opi Opi identifier. You can find all available Opinator IDs at "oauthconf.xml" file, inside
     *      of "<data/>". If NULL defined then default Opi identifier will be used.
     * @param string|null $redirect_url
     * @return string
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function get($opi = null, $redirect_url = null);

}
