<?php
namespace UnitTest\Genetsis\UrlBuilder\Collections;
use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\UrlBuilder\Collections\SocialNetworks;

/**
 * @package UnitTest\Genetsis\UrlBuilder\Collections
 * @category UnitTest
 * @author Ismael Salgado <ismael.salgado@genetsis.com>
 */
class SocialNetworksCollectionTest extends Unit
{
    use Specify;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testCollection ()
    {
        $this->specify('Checks collection verification.', function() {
            $this->assertTrue(SocialNetworks::check(SocialNetworks::FACEBOOK));
            $this->assertTrue(SocialNetworks::check(SocialNetworks::TWITTER));
            $this->assertFalse(SocialNetworks::check('invalid-value'));
        });
    }

}

