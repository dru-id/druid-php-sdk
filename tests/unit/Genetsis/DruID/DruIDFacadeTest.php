<?php
namespace Genetsis\DruID\UnitTest;

use Codeception\Specify;
use Codeception\Test\Unit;
use Doctrine\Common\Cache\VoidCache;
use Genetsis\DruID\Core\Config\Beans\Config;
use Genetsis\DruID\Core\User\Beans\Brand;
use Genetsis\DruID\DruID;
use Genetsis\DruID\DruIDFacade;

/**
 * @package Genetsis\DruID
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
