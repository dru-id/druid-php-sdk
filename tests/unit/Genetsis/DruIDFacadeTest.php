<?php
namespace Genetsis\UnitTest;

use Codeception\Specify;
use Codeception\Test\Unit;
use Doctrine\Common\Cache\VoidCache;
use Genetsis\Core\Config\Beans\Config;
use Genetsis\Core\User\Beans\Brand;
use Genetsis\DruID;
use Genetsis\DruIDFacade;
use Genetsis\Identity\Identity;
use Genetsis\Opi\Opi;
use Genetsis\UrlBuilder\UrlBuilder;
use Genetsis\UserApi\UserApi;

/**
 * @package Genetsis
 * @category UnitTest
 */
class DruIDFacadeTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var DruID $druid */
    private $druid;

    protected function _before()
    {
        $this->druid = new DruID(new Config('www.foo.com'), file_get_contents(OAUTHCONFIG_SAMPLE_XML_1_4), getSyslogLogger('druid-facade-test'), new VoidCache());
    }

    protected function _after()
    {
    }

    public function testFacadeExceptions()
    {
        $this->specify('Checks if the facade throws an exception when using before setup.', function () {
            DruIDFacade::get();
        }, ['throws' => \Exception::class]);
    }

    public function testFacade()
    {
        $this->specify('Checks facade setup.', function () {
            DruIDFacade::setup($this->druid);
            $this->assertTrue(getStaticProperty(DruIDFacade::class, 'setup_done'));
            $this->assertInstanceOf(DruID::class, DruIDFacade::get());
        });
    }
}
