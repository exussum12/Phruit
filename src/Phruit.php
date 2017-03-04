<?php
declare(strict_types=1);
namespace exussum12\Phruit;

class Phruit
{
    private $node;

    public function __construct()
    {
        $this->node = new Node('');
    }

    public function add(string $route, callable $payload)
    {
         $route = (preg_replace('/{([a-z0-9_]+):(.*?)}/i', '(?P<\1>\2)', $route));

         $route = $this->splitRoute($route);

         $this->node->add($route, $payload);
    }

    public function route(string $route)
    {
         $variables = (object)[];
         $route = $this->splitRoute($route);
         return $this->node->route($route, $variables);
    }

    protected function splitRoute(string $route) : array
    {
         return array_filter(str_getcsv($route, "/"));
    }

    public function getNode()
    {
         return $this->node;
    }
}
