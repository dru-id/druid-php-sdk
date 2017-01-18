<?php
namespace Genetsis\UnitTest;

use Codeception\Specify;
use Codeception\Test\Unit;
use Doctrine\Common\Cache\VoidCache;
use Genetsis\core\Config\Beans\Config;
use Genetsis\core\User\Beans\Brand;
use Genetsis\DruID;
use Genetsis\Identity\Services\Identity;
use Genetsis\Opi\Services\Opi;
use Genetsis\UrlBuilder\Services\UrlBuilder;
use Genetsis\UserApi\Services\UserApi;

/**
 * @package Genetsis
 * @category UnitTest
 */
class DruIDTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var DruID $druid */
    private $druid;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
    }

    protected function _after()
    {
    }

    public function testDruIDVersion()
    {
        $this->specify('Checks current library version.', function () {
            $this->assertEquals('1.4', DruID::CONF_VERSION);
        });
    }

    public function testService()
    {
        $this->specify('Test constructor.', function () {
            $this->druid = new DruID(new Config('www.foo.com'), file_get_contents(OAUTHCONFIG_SAMPLE_XML_1_4), getSyslogLogger('druid-facade-test'), new VoidCache());
            $this->assertInstanceOf(Identity::class, $this->druid->identity());
            $this->assertInstanceOf(UrlBuilder::class, $this->druid->urlBuilder());
            $this->assertInstanceOf(UserApi::class, $this->druid->userApi());
            $this->assertInstanceOf(Opi::class, $this->druid->opi());
            $this->assertInstanceOf(Config::class, getProperty($this->druid, 'config'));
        });
    }

    public function testServiceWithAnInvalidOAuthConfXml()
    {
        $this->specify('Checks if the library throws an exception with an oauthconf file with wrong version.', function () {
            $this->druid = new DruID(new Config('www.foo.com'), file_get_contents(OAUTHCONFIG_SAMPLE_XML_WRONG_VERSION), getSyslogLogger('druid-facade-test'), new VoidCache());
        }, ['throws' => \Exception::class]);
    }
}
