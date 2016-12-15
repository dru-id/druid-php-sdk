<?php
namespace Genetsis\UnitTest\Core\Http\Services;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Logger\Collections\LogLevels;

/**
 * @package Genetsis
 * @category UnitTest
 */
class LogLevelsCollectionTest extends Unit {
    use Specify;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testLogLevelsCollectionVerification()
    {
        $this->specify('Checks all collections values.', function(){
            $this->assertTrue(LogLevels::check(LogLevels::DEBUG));
            $this->assertTrue(LogLevels::check(LogLevels::INFO));
            $this->assertTrue(LogLevels::check(LogLevels::NOTICE));
            $this->assertTrue(LogLevels::check(LogLevels::WARNING));
            $this->assertTrue(LogLevels::check(LogLevels::ERROR));
            $this->assertTrue(LogLevels::check(LogLevels::CRITICAL));
            $this->assertTrue(LogLevels::check(LogLevels::ALERT));
            $this->assertTrue(LogLevels::check(LogLevels::EMERGENCY));
            $this->assertFalse(LogLevels::check('nope'));
        });
    }

}
