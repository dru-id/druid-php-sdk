<?php
namespace Genetsis\DruID\UnitTest\Core\Logger;

use Codeception\Specify;
use Genetsis\DruID\Core\Logger\VoidLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class VoidLoggerServiceTest extends TestCase
{
    use Specify;

    /** @var LoggerInterface $logger */
    protected $logger;

    protected function setUp()
    {
        $this->logger = new VoidLogger();
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
