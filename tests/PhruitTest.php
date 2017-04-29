<?php
namespace exussum12\tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use exussum12\Phruit\Phruit;

class PhruitTest extends TestCase
{
    public function testCallingRoute()
    {
        $payload = function () {
        };
        $route = new Phruit();
        $route->add('/users/{name:[a-z]+}', $payload);

        $foundRoute = $route->route('/users/abc');
        $this->assertSame($payload, $foundRoute);
    }

    public function testRouteDoesntExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $route = new Phruit();

        $payload = $route->route('/users/abc');
        $payload();
    }

    public function testSamePrefix()
    {
        $notCalled = function () {
        };
        $called = function () {
        };
        $route = new Phruit();
        $route->add('/users/{name:[a-z]+}', $notCalled);
        $route->add('/users/{name:[a-z]+}/{id:[0-9]+}', $called);

        $this->assertSame($called, $route->route('/users/test/123'));
    }

    public function testDynamicEndPart()
    {
        $payload = function () {
        };
        $route = new Phruit();
        $route->add('/users/{name:[a-z]+}', $payload);

        $this->assertSame($payload, $route->route('/users/test'));
    }

    public function testCustom404()
    {
        $notFound = function () {
        };

        $route = new Phruit($notFound);

        $this->assertSame($notFound, $route->route('/users/test'));

    }
}
