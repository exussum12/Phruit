<?php
namespace exussum12\tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use exussum12\Phruit\Phruit;

class PhruitTest extends TestCase
{
    public function testAddingRoute()
    {
        $route = new Phruit();
        $routedFunction = function () {
        };
        $route->add('/users/{name:[a-z]+}', $routedFunction);
        $this->assertSame(2, $route->getNode()->countNestedChildren());
        $this->assertSame(1, $route->getNode()->countChildren());
    }

    public function testCallingRoute()
    {
        $mock = $this->getMockBuilder('route')->setMethods(['test'])->getMock();
        $mock ->expects($this->once())->method('test');

        $route = new Phruit();
        $route->add('/users/{name:[a-z]+}', [$mock, 'test']);

        $route->route('/users/abc');
    }

    public function testRouteDoesntExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $route = new Phruit();

        $route->route('/users/abc');
    }

    public function testSharedNode()
    {

        $mock = $this->getMockBuilder('route')->setMethods(['notCalled', 'called'])->getMock();
        $mock ->expects($this->never())->method('notCalled');
        $mock ->expects($this->once())->method('called');

        $route = new Phruit();
        $route->add('/users/{name:[a-z]+}', [$mock, 'notCalled']);
        $route->add('/users/{id:[0-9]+}', [$mock, 'called']);

        $route->route('/users/123');

        $this->assertSame(3, $route->getNode()->countNestedChildren());
        $this->assertSame(1, $route->getNode()->countChildren());
    }
}
