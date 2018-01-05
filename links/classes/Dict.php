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
}

class Dict
{
    const GOAL = 10;

    // Array of entries
    private $rows = [];

    static function load() {
        return new self();
    }

    function __construct() {
        $path = $this->path();
        $this->rows = [];
        if (!file_exists($path)) {
            return;
        }
        $f = fopen($path, 'rb');
        while (1) {
            $row = fgetcsv($f);
            if (!$row) break;
            $this->rows[] = Entry::fromRow($row);
        }
        fclose($f);
    }

    private function path()
    {
        return __DIR__ . '/../data/dict/dict.csv';
    }

    function append($tuples)
    {
        foreach ($tuples as $t) {
            $entry = new Entry;
            $entry->q = $t[0];
            $entry->a = $t[1];
            if ($this->has($entry)) {
                continue;
            }
            $this->rows[] = $entry;
        }
        return $this;
    }

    private function has(Entry $e)
    {
        foreach ($this->rows as $r) {
            if ($r->eq($e)) return true;
        }
        return false;
    }

    function pick($n, $dir)
    {
        return Arr::make($this->rows)
            ->filter(function(Entry $row) use ($dir) {
                return $row->score($dir) < self::GOAL;
            })
            ->shuffle()
            ->take($n)
            ->map(function(Entry $row) {
                return [$row->q, $row->a];
            })
            ->get();
    }

    function save() {
        $path = $this->path();
        if (file_exists($path)) {
            copy($path, $path.date('ymd-his'));
        }
        $f = fopen($path, 'wb');
        foreach ($this->rows as $row) {
            fputcsv($f, $row->toRow());
        }
        fclose($f);
        return $this;
    }

    private function find($q, $dir) {
        foreach ($this->rows as $i => $row) {
            if ($row->val($dir) == $q) {
                return $i;
            }
        }
        return -1;
    }

    function check($q, $a, $dir)
    {
        // Find all rows with this question
        $all = [];
        foreach ($this->rows as $i => $row) {
            if ($row->val($dir) == $q) {
                $all[] = $i;
            }
        }

        // Find one that we got right
        $right = -1;
        foreach ($all as $i) {
            if (mb_strtolower($this->rows[$i]->expected($dir)) == mb_strtolower($a)) {
                $right = $i;
                break;
            }
        }

        if ($right >= 0) {
            $expected = $this->rows[$right]->expected($dir);
            $this->rows[$right]->addScore($dir);
            return [
                'q' => $q,
                'a' => $a,
                'expected' => $expected,
                'ok' => true
            ];
        }

        $expected = [];
        foreach ($all as $i) {
            $expected[] = $this->rows[$i]->expected($dir);
        }

        return [
            'q' => $q,
            'a' => $a,
            'expected' => implode(' || ', $expected),
            'ok' => false
        ];
    }

    function stats()
    {
        $ok = 0;
        foreach ($this->rows as $row) {
            $ok += $row->answers1 + $row->answers2;
        }
        $n = count($this->rows);
        return [
            'pairs' => $n,
            'progress' => $ok / self::GOAL / $n
        ];
    }
}
