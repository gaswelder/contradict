<?php

use havana\dbobject;

class Dict extends dbobject
{
    /**
     * How many correct answers needed for an entry to be "finished".
     */
    const GOAL = 10;

    /**
     * How many entries are in the "learning pool".
     * This limit is applied separately to both directions,
     * so the actual pool limit is twice this value.
     */
    const WINDOW = 200;

    const TABLE_NAME = 'dicts';
    public $name;

    static function load($id)
    {
        $s = new self($id);
        $s->id = $id;
        return $s;
    }

    function pick($size, $dir)
    {
        return Entry::pick($this->id, $size, $dir);
    }

    function append($tuples)
    {
        $n = 0;
        foreach ($tuples as $t) {
            $entry = new Entry;
            $entry->dict_id = $this->id;
            $entry->q = $t[0];
            $entry->a = $t[1];
            if ($this->has($entry)) {
                continue;
            }
            $entry->save();
            $n++;
        }
        return $n;
    }

    private function has(Entry $e)
    {
        return Entry::db()->getValue(
            "select count(*)
            from words
            where dict_id = ? and q = ? and a = ?",
            $this->id,
            $e->q,
            $e->a
        ) > 0;
    }

    function stats()
    {
        $goal = self::GOAL;

        $totalEntries = self::db()->getValue('select count(*) from words where dict_id = ?', $this->id);

        // Number of entries that have enough correct answers in both directions.
        $finished = self::db()->getValue(
            "select sum(a1 + a2)
            from (select answers1 >= $goal as a1, answers2 >= $goal as a2
                from words where dict_id = ?) a",
            $this->id
        );

        // Number of entries that are "in progress".
        $touched = self::db()->getValue(
            "select count(*) from words
                where dict_id = ?
                and touched = 1
                and (answers1 < $goal or answers2 < $goal)",
            $this->id
        );

        return [
            'pairs' => floatval($totalEntries),
            'finished' => $finished / 2,
            'touched' => floatval($touched),
            'successRate' => $this->successRate()
        ];
    }

    private function successRate()
    {
        $scores = self::db()->getValues(
            'select 1.0 * right / (right + wrong)
            from results
            where dict_id = ?
            order by id desc
            limit 10',
            $this->id
        );
        $n = count($scores);
        if ($n == 0) return 1;
        return array_sum($scores) / $n;
    }

    function format()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stats' => $this->stats()
        ];
    }
}
