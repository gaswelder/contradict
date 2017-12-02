<?php

class Dict
{
    const GOAL = 10;

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
            $this->rows[] = $row;
        }
        fclose($f);
    }

    private function path()
    {
        return __DIR__ . '/../data/dict/dict.csv';
    }

    function append($tuples) {
        foreach ($tuples as $t) {
            $t[] = 0;
            $t[] = 0;
            $this->rows[] = $t;
        }
        return $this;
    }

    function pick($n, $dir)
    {
        return Arr::make($this->rows)
            ->filter(function($row) use ($dir) {
                return $row[$dir + 2] < self::GOAL;
            })
            ->shuffle()
            ->take($n)
            ->map(function($row) {
                return array_slice($row, 0, 2);
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
            fputcsv($f, $row);
        }
        fclose($f);
        return $this;
    }

    private function find($q, $dir) {
        foreach ($this->rows as $i => $row) {
            if ($row[$dir] == $q) {
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
            if ($row[$dir] == $q) {
                $all[] = $i;
            }
        }

        // Find one that we got right
        $right = -1;
        foreach ($all as $i) {
            if (mb_strtolower($this->rows[$i][abs($dir-1)]) == mb_strtolower($a)) {
                $right = $i;
                break;
            }
        }

        if ($right >= 0) {
            $expected = $this->rows[$right][abs($dir-1)];
            $this->rows[$right][$dir+2]++;
            return [
                'q' => $q,
                'a' => $a,
                'expected' => $expected,
                'ok' => true
            ];
        }

        $expected = [];
        foreach ($all as $i) {
            $expected[] = $this->rows[$i][abs($dir-1)];
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
            $ok += $row[2] + $row[3];
        }
        $n = count($this->rows);
        return [
            'pairs' => $n,
            'progress' => $ok / self::GOAL / $n
        ];
    }
}
