<?php

class Entry
{
    public $q;
    public $a;
    public $answers1 = 0;
    public $answers2 = 0;

    function toRow() {
        return [$this->q, $this->a, $this->answers1, $this->answers2];
    }

    static function fromRow($row) {
        $e = new self();
        $e->q = array_shift($row);
        $e->a = array_shift($row);
        $e->answers1 = array_shift($row);
        $e->answers2 = array_shift($row);
        return $e;
    }

    function eq(Entry $e) {
        return $e->q == $this->q && $e->a == $this->a;
    }

    function score($dir) {
        if ($dir == 0) return $this->answers1;
        if ($dir == 1) return $this->answers2;
        throw new Exception("score(dir): got dir=$dir");
    }

    function val($dir) {
        if ($dir == 0) return $this->q;
        if ($dir == 1) return $this->a;
        throw new Exception("val(dir): got dir=$dir");
    }

    function expected($dir) {
        if ($dir == 0) {
            $expected = $this->a;
        } else {
            $expected = $this->q;
        }
        return $expected;
    }

    function addScore($dir) {
        if ($dir == 0) {
            $this->answers1++;
        } else {
            $this->answers2++;
        }
    }

    function match($dir, $a) {
        return mb_strtolower($this->expected($dir)) == mb_strtolower($a);
    }
}
