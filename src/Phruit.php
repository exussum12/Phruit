<?php
declare(strict_types=1);
namespace exussum12\Phruit;

use InvalidArgumentException;

class Phruit
{
    private $routes = [];
    private $notFound = null;

    public function __construct(callable $notFound = null)
    {
        if (is_null($notFound)) {
            $notFound = function () {
                throw new InvalidArgumentException();
            };
        }
        $this->notFound = $notFound;
    }

    public function add(string $route, callable $payload)
    {
        $route = $this->splitRoute($route);
        $currentRoute = &$this->routes;
        $count = count($route);

        for ($i = 0; $i < $count; $i++) {
            $r = $route[$i];
            $r = preg_replace('/{([a-z0-9_]+):(.*?)}/i', '/(?P<\1>\2)/', $r);
            if ($r == '/') {
                $currentRoute[$r] = $payload;
                break;
            }

            if (!isset($currentRoute[$r])) {
                $currentRoute[$r] = [];
            }

            if (!ctype_alnum($r)) {
                if (!isset($currentRoute['/dynamic'])) {
                    $currentRoute['/dynamic'] = [];
                }
                $currentRoute = &$currentRoute['/dynamic'];
            }
            $currentRoute = &$currentRoute[$r];
        }
    }

    public function route(string $route) : callable
    {
        $route = $this->splitRoute($route);

        $tmp = $this->routes;
        foreach ($route as $r) {
            if (isset($tmp[$r])) {
                if (is_array($tmp[$r])) {
                    $tmp = $tmp[$r];
                    continue;
                }
                return $tmp[$r];
            }
            if (isset($tmp['/dynamic'])) {
                foreach ($tmp['/dynamic'] as $part => $payload) {
                    if (preg_match($part, $r)) {
                        $tmp = $payload;
                        continue 2;
                    }
                }
            }
            break;
        }
        return $this->notFound();

    }

    protected function splitRoute(string $route) : array
    {
        return array_merge(
            array_filter(
                explode('/', $route)
            ),
            ['/']
        );
    }

    protected function notFound(): callable
    {
        return $this->notFound;
    }
}

//$a = new Phruit();
//$a->add("a/b/c", function(){echo "Static";});
//$a->add("a/{name:[a-z]+}/c", function(){echo "Dynamic";});
//
//for ($i = 0;$i<100;$i++){
//    var_dump($a->route("a/b/c/"));
//    var_dump($a->route("b/d/c/"));
//    var_dump($a->route("a/d/c/"));
//}
