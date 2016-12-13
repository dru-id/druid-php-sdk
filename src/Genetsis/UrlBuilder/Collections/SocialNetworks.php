<?php
namespace Genetsis\UrlBuilder\Collections;

/**
 * Class to group all allowed social networks.
 *
 * @package  Genetsis
 * @category Collection
 */
class SocialNetworks
{
    const FACEBOOK = 'facebook';
    const TWITTER = 'twitter';

    /**
     * @param string $value
     * @return boolean
     */
    public static function check($value)
    {
        return in_array($value, [self::FACEBOOK, self::TWITTER]);
    }
}