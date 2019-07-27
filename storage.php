<?php

// interface Storage
// {
//     function dicts(): array;
//     function dict(string $id): Dict;
//     function appendWords(string $dict_id, array $pairs): int;

//     /**
//      * Generates a test for a given dictionary.
//      */
//     function test(string $dict_id): Test;
//     function entry(string $id): Entry;
//     function saveEntry(Entry $e);
//     function entries(array $ids): array;
// }


class Storage
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


    function dicts(): array
    {
        $rows = SQL::db()->getRows("select id, name from 'dicts'");
        return array_map(function ($row) {
            return new Dict($row['id'], $row['name']);
        }, $rows);
    }

    function dict(string $id): Dict
    {
        $row = SQL::db()->getRow("select id, name from 'dicts' where id = ?", $id);
        $d = new Dict($row['id'], $row['name']);
        return $d;
    }

    function dictStats(string $dict_id): Stats
    {
        $goal = self::GOAL;

        $totalEntries = SQL::db()->getValue('select count(*) from words where dict_id = ?', $dict_id);

        // Number of entries that have enough correct answers in both directions.
        $finished = SQL::db()->getValue(
            "select sum(a1 + a2)
            from (select answers1 >= $goal as a1, answers2 >= $goal as a2
                from words where dict_id = ?) a",
            $dict_id
        );

        // Number of entries that are "in progress".
        $touched = SQL::db()->getValue(
            "select count(*) from words
                where dict_id = ?
                and touched = 1
                and (answers1 < $goal or answers2 < $goal)",
            $dict_id
        );

        return new Stats($totalEntries, $finished, $touched, $this->successRate($dict_id));
    }

    private function successRate($dict_id)
    {
        $scores = SQL::db()->getValues(
            'select 1.0 * right / (right + wrong)
            from results
            where dict_id = ?
            order by id desc
            limit 10',
            $dict_id
        );
        $n = count($scores);
        if ($n == 0) return 1;
        return array_sum($scores) / $n;
    }

    function appendWords(string $dict_id, array $pairs): int
    {
        $n = 0;
        foreach ($pairs as $t) {
            $entry = new Entry;
            $entry->dict_id = $dict_id;
            $entry->q = $t[0];
            $entry->a = $t[1];
            if ($this->hasEntry($dict_id, $entry)) {
                continue;
            }
            $entry->save();
            $n++;
        }
        return $n;
    }

    private function hasEntry($dict_id, $entry)
    {
        return Entry::db()->getValue(
            "select count(*)
            from words
            where dict_id = ? and q = ? and a = ?",
            $dict_id,
            $entry->q,
            $entry->a
        ) > 0;
    }

    /**
     * Generates a test for a given dictionary.
     */
    function test(string $dict_id): Test
    {
        $size = 20;
        $dict = $this->dict($dict_id);
        return new Test($this->pick($dict_id, $size, 0), $this->pick($dict_id, $size, 1));
    }

    private function pick($dict_id, $size, $dir)
    {
        return Entry::pick($dict_id, $size, $dir);
    }

    function entry(string $id): Entry
    {
        return Entry::get($id);
    }

    function saveEntry(Entry $e)
    {
        $e->save();
    }

    function entries(array $ids): array
    {
        return Entry::getMultiple($ids);
    }
}
