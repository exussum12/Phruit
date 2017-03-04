<?php
namespace exussum12\Phruit;

class Phruit
{
    private $node;

    public function __construct()
    {
        $this->node = new Node('');
    }

    public function add($route, callable $payload)
    {
         $route = (preg_replace('/{([a-z0-9_]+):(.*?)}/i', '(?P<\1>\2)', $route));

         $route = $this->splitRoute($route);

         $this->node->add($route, $payload);
    }

    public function route($route)
    {
         $variables = (object)[];
         $route = $this->splitRoute($route);
         return $this->node->route($route, $variables);
    }

    protected function splitRoute($route)
    {
         return array_filter(str_getcsv($route, "/"));
    }

    public function getNode()
    {
         return $this->node;
    }
}
