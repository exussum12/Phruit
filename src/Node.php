<?php
declare(strict_types=1);
namespace exussum12\Phruit;

use InvalidArgumentException;
use stdClass;

class Node
{
    private $children = [];
    private $match;
    private $payload;

    public function __construct(string $match)
    {
        $this->match = $match;
    }

    public function countChildren() : int
    {
        return count($this->children);
    }

    public function countNestedChildren() : int
    {
        $total = 0;

        foreach ($this->children as $child) {
            $total += $child->countNestedChildren();
        }
        $total += $this->countChildren();

        return $total;
    }

    public function add(array $route, callable $payload)
    {
        if (empty($route)) {
            $this->payload = $payload;
            return;
        }
        $part = array_shift($route);
        foreach ($this->children as $child) {
            $variables = (object)[];
            if ($child->getMatch() === $part) {
                return $child->add($route, $payload);
            }
        }

        $node = new Node($part);
        $node->add($route, $payload);
        $this->children[] = $node;
    }

    public function matches(string $string, stdClass $variables) : bool
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

        return $found === 1;
    }

    public function route(array $matches, stdClass $variables)
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

    public function printDebug($level = 0)
    {
        echo str_repeat("\t", $level) . $this->match . "\n";
        $level++;
        foreach ($this->children as $child) {
            $child->printDebug($level);
        }
    }

    public function getMatch()
    {
        return $this->match;
    }
}
