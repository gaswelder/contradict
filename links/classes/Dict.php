<?php

class Dict
{
    const GOAL = 10;

    // Array of entries
    private $rows = [];

    static function load() {
        return new self();
    }

    function entry($id) {
        return $this->rows[$id];
    }

    function __construct() {
        $path = $this->path();
        $this->rows = [];
        if (!file_exists($path)) {
            return;
        }
        $f = fopen($path, 'rb');
        $i = 0;
        while (1) {
            $row = fgetcsv($f);
            if (!$row) break;
            $this->rows[] = Entry::fromRow($row, $i);
            $i++;
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

    // Returns list of entries matching the given question for the given direction.
    private function entries($dir, $q)
    {
        $entries = [];
        foreach ($this->rows as $row) {
            if (mb_strtolower($row->val($dir)) == mb_strtolower($q)) {
                $entries[] = $row;
            }
        }
        return $entries;
    }

    function result(Answer $answer)
    {
        $dir = $answer->dir;
        $q = $answer->q;
        $a = $answer->a;

        // Find all rows with this question
        $entries = $this->entries($dir, $q);

        // Find one that matches.
        $match = array_reduce($entries, function($prev, Entry $entry) use ($dir, $a) {
            if ($prev) return $prev;
            if ($entry->match($dir, $a)) return $entry;
            return null;
        });

        if ($match) {
            $match->addScore($dir);
        }

        return new Result($answer, $entries, $match);
    }
}
