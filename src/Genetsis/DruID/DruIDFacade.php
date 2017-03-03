<?php
namespace Genetsis\DruID;

/**
 * Facade to allow to have the DruID library configured wherever the code requires it. The DruID library must be
 * configured first. See {@link DruID}
 *
 * @author Genetsis
 * @link http://developers.dru-id.com
 */
class DruIDFacade
{

    /** @var DruID $druid */
    private static $druid;
    /** @var boolean $setup_done */
    private static $setup_done = false;

    /**
     * Setup this facade.
     *
     * @param DruID $druid
     * @return void
     */
    public static function setup(DruID $druid)
    {
        self::$setup_done = true;
        self::$druid = $druid;
    }

    /**
     * Returns the current DruID library.
     *
     * @return DruID
     * @throws \Exception If facade has not been initialized.
     */
    public static function get()
    {
        if (!self::$setup_done) {
            throw new \Exception('DruID facade has not been setup.');
        }
        return self::$druid;
    }
}
