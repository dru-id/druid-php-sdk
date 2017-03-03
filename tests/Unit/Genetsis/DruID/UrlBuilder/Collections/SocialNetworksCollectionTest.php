<?php
namespace UnitTest\Genetsis\DruID\UrlBuilder\Collections;

use Codeception\Specify;
use Genetsis\DruID\UrlBuilder\Collections\SocialNetworks;
use PHPUnit\Framework\TestCase;

/**
 * @package UnitTest\Genetsis\DruID\UrlBuilder\Collections
 * @category UnitTest
 * @author Ismael Salgado <ismael.salgado@genetsis.com>
 */
class SocialNetworksCollectionTest extends TestCase
{
    use Specify;

    public function testCollection ()
    {
        $this->specify('Checks collection verification.', function() {
            $this->assertTrue(SocialNetworks::check(SocialNetworks::FACEBOOK));
            $this->assertTrue(SocialNetworks::check(SocialNetworks::TWITTER));
            $this->assertFalse(SocialNetworks::check('invalid-value'));
        });
    }

}

