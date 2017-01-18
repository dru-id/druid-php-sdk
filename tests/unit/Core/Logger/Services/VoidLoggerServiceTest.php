<?php
namespace Genetsis\UnitTest\Core\Logger\Services;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Logger\Services\VoidLogger;
use Psr\Log\LoggerInterface;

/**
 * @package Genetsis
 * @category UnitTest
 */
class VoidLoggerServiceTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;

    /** @var LoggerInterface $logger */
    protected $logger;

    protected function _before()
    {
        $this->logger = new VoidLogger();
    }

    protected function _after()
    {
    }

    public function testVoidLoggerMethods()
    {
        $this->specify('Checks all methods.', function(){
            $this->assertNull($this->logger->emergency('Foo'));
            $this->assertNull($this->logger->alert('Foo'));
            $this->assertNull($this->logger->critical('Foo'));
            $this->assertNull($this->logger->error('Foo'));
            $this->assertNull($this->logger->warning('Foo'));
            $this->assertNull($this->logger->notice('Foo'));
            $this->assertNull($this->logger->info('Foo'));
            $this->assertNull($this->logger->debug('Foo'));
            $this->assertNull($this->logger->log('Foo', 'Bar'));
        });
    }
}
