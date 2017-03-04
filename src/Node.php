<?php
namespace exussum12\Phruit;

use InvalidArgumentException;

class Node
{
    private $children = [];
    private $match;
    private $payload;

    public function __construct($match)
    {
        $this->match = $match;
    }

    public function countChildren()
    {
        return count($this->children);
    }

    public function countNestedChildren()
    {
        $total = 0;

        foreach ($this->children as $child) {
            $total += $child->countNestedChildren();
        }
        $total += $this->countChildren();

        return $total;
    }

    public function add($route, callable $payload)
    {
        if (empty($route)) {
            $this->payload = $payload;
            return;
        }
        $part = array_shift($route);
        foreach ($this->children as $child) {
            $variables = (object)[];
            if ($child->matches($part, $variables)) {
                return $child->add($route, $payload);
            }
        }

        $node = new Node($part);
        $node->add($route, $payload);
        $this->children[] = $node;
    }

    public function matches($string, $variables)
    {
        if (ctype_alnum($this->match)) {
            return $string === $this->match;
        }

        $matches = [];
        $found = preg_match(sprintf('@^%s$@', trim($this->match, "^$")), $string, $matches);

        foreach ($matches as $key => $val) {
            if (!is_int($key)) {
                $variables->$key = $val;
            }
        }

        return $found;
    }

    public function route($matches, $variables)
    {
        $part = array_shift($matches);
        foreach ($this->children as $child) {
            if ($child->matches($part, $variables)) {
                return $child->route($matches, $variables);
            }
        }

        if (count($this->children) == 0 && count($matches) == 0) {
            return call_user_func($this->payload);
        }

        throw new InvalidArgumentException("Can't find the requested route");
    }
}
