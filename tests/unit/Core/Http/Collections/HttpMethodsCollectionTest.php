<?php
namespace Genetsis\UnitTest\Core\Http\Services;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Http\Collections\HttpMethods;

/**
 * @package Genetsis
 * @category UnitTest
 */
class HttpMethodsCollectionTest extends Unit {
    use Specify;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testHttpMethodsCollectionVerification()
    {
        $this->specify('Checks all collections values.', function(){
            $this->assertTrue(HttpMethods::check(HttpMethods::POST));
            $this->assertTrue(HttpMethods::check(HttpMethods::GET));
            $this->assertTrue(HttpMethods::check(HttpMethods::PUT));
            $this->assertTrue(HttpMethods::check(HttpMethods::HEAD));
            $this->assertTrue(HttpMethods::check(HttpMethods::DELETE));
            $this->assertFalse(HttpMethods::check('nope'));
        });
    }

}
