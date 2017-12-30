<?php

class Arr
{
    private $a;

    static function make($a = []) {
        return new self($a);
    }

    function __construct($a = [])
    {
        $this->a = $a;
    }

    function map($func) {
        return new self(array_map($func, $this->a));
    }

    function filter($func = null) {
        $a = $func ? array_filter($this->a, $func) : array_filter($this->a);
        return new self($a);
    }

    function each($func) {
        foreach($this->a as $k => $v) {
            call_user_func($func, $v, $k);
        }
    }

    function shuffle() {
        $copy = $this->a;
        shuffle($copy);
        return new self($copy);
    }

    function take($n) {
        return new self(array_slice($this->a, 0, $n));
    }

    function get() {
        return $this->a;
    }
}