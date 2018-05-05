<?php

class Arr
{
    private $a;

    static function make($a = [])
    {
        return new self($a);
    }

    function __construct($a = [])
    {
        $this->a = $a;
    }

    function map($func)
    {
        return new self(array_map($func, $this->a, array_keys($this->a)));
    }

    function reduce($func, $init)
    {
        return new self(array_reduce($this->a, $func, $init));
    }

    function filter($func = null)
    {
        $a = $func ? array_filter($this->a, $func) : array_filter($this->a);
        return new self(array_values($a));
    }

    function each($func)
    {
        foreach ($this->a as $k => $v) {
            call_user_func($func, $v, $k);
        }
    }

    function shuffle()
    {
        $copy = $this->a;
        shuffle($copy);
        return new self($copy);
    }

    function take($n)
    {
        return new self(array_slice($this->a, 0, $n));
    }

    function len()
    {
        return count($this->a);
    }

    function pluck($field)
    {
        return $this->map(function ($item) use ($field) {
            return $item->$field;
        });
    }

    function zip($list)
    {
        $n = count($this->a);
        $result = [];
        foreach ($list as $i => $second) {
            if ($i == $n) break;
            $result[] = [$this->a[$i], $second];
        }
        return new self($result);
    }

    function get()
    {
        return $this->a;
    }
}
