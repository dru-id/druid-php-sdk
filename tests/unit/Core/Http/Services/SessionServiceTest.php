<?php
namespace Genetsis\UnitTest\Core\Http\Services;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\core\Http\Contracts\SessionServiceInterface;
use Genetsis\core\Http\Services\Session;

/**
 * @package Genetsis
 * @category UnitTest
 */
class SessionServiceTest extends Unit
{

    use Specify;

    /** @var \UnitTester */
    protected $tester;
    /** @var SessionServiceInterface $session */
    protected $session;

    protected function _before()
    {
        $this->specifyConfig()->shallowClone(); // Speeds up testing avoiding deep clone.
        $this->session = new Session();
        $_SESSION = []; // Calling this test from CLI won't create a $_SESSION variable.
    }

    protected function _after()
    {
    }

    public function testSessionHandler()
    {
        $this->specify('Checks if we can set a session data.', function(){
            $this->assertTrue($this->session->set('foo', 'bar'));
            $this->assertFalse($this->session->set('', 'biz'));
            $this->assertTrue($this->session->set('foo2', 'bar2'));
            $this->assertTrue($this->session->set('foo2', 'barbar'));
        });

        $this->specify('Checks if a session data exists.', function(){
            $this->assertTrue($this->session->has('foo'));
            $this->assertFalse($this->session->has('foo3'));
            $this->assertFalse($this->session->has(''));
        });

        $this->specify('Checks if we can get a session value.', function(){
            $this->assertEquals('bar', $this->session->get('foo'));
            $this->assertEquals('biz', $this->session->get('nope', 'biz'));
            $this->assertEquals('bar', $this->session->get('', 'bar'));
        });

        $this->specify('Checks all stored values.', function(){
            $this->assertCount(2, $this->session->all());
            $this->session->set('biz', 'cucu');
            $this->assertCount(3, $this->session->all());
        });

        $this->specify('Checks if a stored value is deleted', function(){
            $this->assertTrue($this->session->has('foo'));
            $this->session->delete('foo');
            $this->assertFalse($this->session->has('foo'));
            $this->assertCount(2, $this->session->all());
            $this->session->delete('nope');
            $this->assertCount(2, $this->session->all());
        });
    }
}
