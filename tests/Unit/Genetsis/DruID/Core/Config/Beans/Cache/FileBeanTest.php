<?php
namespace Genetsis\DruID\UnitTest\Core\Config\Beans\Cache;

use Codeception\Specify;
use Genetsis\DruID\Core\Config\Beans\Cache\File;
use PHPUnit\Framework\TestCase;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class FileBeanTest extends TestCase
{
    use Specify;

    /** @var \UnitTester */
    protected $tester;
    /** @var File $cache */
    protected $cache;

    protected function setUp()
    {
        $this->cache = new File('default', 'foo/bar');
    }

    public function testSetterAndGetterGroup()
    {
        $this->specify('Checks setter and getter for parameter "group".', function(){
            $this->assertInstanceOf(File::class, $this->cache->setGroup('foo'));
            $this->assertEquals('foo', $this->cache->getGroup());
        });
    }

    public function testSetterAndGetterFolder()
    {
        $this->specify('Checks setter and getter for parameter "folder".', function(){
            $this->assertInstanceOf(File::class, $this->cache->setFolder('foo/bar/biz'));
            $this->assertEquals('foo/bar/biz', $this->cache->getFolder());
            $this->cache->setFolder('foo/bar/biz/');
            $this->assertEquals('foo/bar/biz', $this->cache->getFolder());
        });
    }

    public function testConstructor()
    {
        $this->specify('Checks constructor.', function () {
            $log = new File('foo', 'foo/bar');
            $this->assertEquals('foo', $log->getGroup());
            $this->assertEquals('foo/bar', $log->getFolder());
        });
    }
}
