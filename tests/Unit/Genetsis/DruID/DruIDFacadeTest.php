<?php
namespace Genetsis\DruID\UnitTest;

use Codeception\Specify;
use Doctrine\Common\Cache\VoidCache;
use Genetsis\DruID\Core\Config\Beans\Config;
use Genetsis\DruID\DruID;
use Genetsis\DruID\DruIDFacade;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class DruIDFacadeTest extends TestCase
{
    use Specify;

    /** @var DruID $druid */
    private $druid;

    protected function setUp()
    {
        $this->druid = new DruID(new Config('www.foo.com'), file_get_contents(OAUTHCONFIG_SAMPLE_XML_1_4), getSyslogLogger('druid-facade-test'), new VoidCache());
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
