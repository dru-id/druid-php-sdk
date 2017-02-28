<?php
namespace Genetsis\DruID\UnitTest\Core\Http;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\DruID\Core\Http\Contracts\CookiesServiceInterface;
use Genetsis\DruID\Core\Http\Cookies;
use phpmock\functions\FixedValueFunction;
use phpmock\MockBuilder;

/**
 * @package Genetsis\DruID
 * @category UnitTest
 */
class CookiesServiceTest extends Unit
{

    use Specify;

    /** @var MockBuilder $mock_builder */
    protected $mock_builder;
    /** @var \UnitTester */
    protected $tester;
    /** @var CookiesServiceInterface $cookies */
    protected $cookies;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->cookies = new Cookies();
    }

    protected function _after()
    {
    }

    public function testCookieHandler()
    {
        // Mocking PHP function "setcookie".
        $setcookie_mock = (new MockBuilder())->setNamespace('Genetsis\DruID\Core\Http')->setName('setcookie')->setFunctionProvider(new FixedValueFunction(true))->build();
        $setcookie_mock->enable();

        $_COOKIE['foo'] = 'foo-value';
        $_COOKIE['bar'] = 'bar-value';

        $this->specify('Checks if we can set a cookie.', function(){
            $this->assertTrue($this->cookies->set('foo', 'bar', 3600));
            $this->assertFalse($this->cookies->set(''));
        });

        $this->specify('Checks if a cookie exists.', function(){
            $this->assertTrue($this->cookies->has('foo'));
        });

        $this->specify('Checks if we can get a cookie value.', function(){
            $this->assertEquals('foo-value', $this->cookies->get('foo'));
            $this->assertEquals('biz', $this->cookies->get('nope', 'biz'));
            $this->assertEquals('bar', $this->cookies->get('', 'bar'));
        });

        $this->specify('Checks when requiring all stored cookies.', function(){
            $this->assertCount(2, $this->cookies->all());
            $_COOKIE['biz'] = 'biz-value';
            $this->assertCount(3, $this->cookies->all());
            $this->assertArrayHasKey('biz', $this->cookies->all());
        });

        $this->specify('Checks if a cookie is deleted', function(){
            $this->cookies->delete('foo');
            $this->assertCount(2, $this->cookies->all());
            $this->cookies->delete('nope');
            $this->assertCount(2, $this->cookies->all());
        });

        $setcookie_mock->disable();
    }
}
