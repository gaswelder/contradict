<?php

// __APPDIR is a hack to let dbclient know where to look for the sqlite file.
$GLOBALS['__APPDIR'] = __DIR__ . '/..';

class SQLStorage implements Storage
{
    function __construct($url)
    {
        $this->db = db($url);
    }

    function dicts(): array
    {
        $rows = $this->db->getRows("select id, name from 'dicts'");
        return array_map(function ($row) {
            return new Dict($row['id'], $row['name']);
        }, $rows);
    }

    function dict(string $id): Dict
    {
        $row = $this->db->getRow("select id, name from 'dicts' where id = ?", $id);
        $d = new Dict($row['id'], $row['name']);
        return $d;
    }

    function dictStats(string $dict_id): Stats
    {
        $goal = self::GOAL;

        $totalEntries = $this->db->getValue('select count(*) from words where dict_id = ?', $dict_id);

        // Number of entries that have enough correct answers in both directions.
        $finished = $this->db->getValue(
            "select sum(a1 + a2)
            from (select answers1 >= $goal as a1, answers2 >= $goal as a2
                from words where dict_id = ?) a",
            $dict_id
        );

        // Number of entries that are "in progress".
        $touched = $this->db->getValue(
            "select count(*) from words
                where dict_id = ?
                and touched = 1
                and (answers1 < $goal or answers2 < $goal)",
            $dict_id
        );

        return new Stats($totalEntries, $finished, $touched);
    }

    function lastScores(string $dict_id): array
    {
        $rows = $this->db->getRows(
            'select id, right, wrong, dict_id
            from results
            where dict_id = ?
            order by id desc
            limit 10',
            $dict_id
        );
        return array_map([Score::class, 'parse'], $rows);
    }

    function hasEntry($dict_id, $entry)
    {
        return $this->db->getValue(
            "select count(*)
            from words
            where dict_id = ? and q = ? and a = ?",
            $dict_id,
            $entry->q,
            $entry->a
        ) > 0;
    }

    function allEntries(string $dict_id): array
    {
        $rows = $this->db->getRows("
            select * from words where dict_id = ?", $dict_id);
        return array_map([Entry::class, 'parse'], $rows);
    }

    function similars(Entry $e, bool $reverse): array
    {
        $filter = $reverse ? ['a' => $e->a] : ['q' => $e->q];
        $rows = $this->db->select('words', ['id', 'q', 'a', 'answers1', 'answers2', 'dict_id', 'touched'], $filter, 'id');

        $entries = [];
        foreach ($rows as $row) {
            $e = Entry::parse($row);
            if ($e->id == $e->id) {
                continue;
            }
            $entries[] = $e;
        }
        return $entries;
    }

    function entry(string $id): Entry
    {
        $row = $this->db->getRow("select id, q, a, answers1, answers2, dict_id, touched from 'words' where id = ?", $id);
        return Entry::parse($row);
    }

    function saveEntry(Entry $e)
    {
        if ($e->id) {
            $this->db->update('words', [
                'q' => $e->q,
                'a' => $e->a,
                'answers1' => $e->answers1,
                'answers2' => $e->answers2,
                'dict_id' => $e->dict_id,
            ], ['id' => $e->id]);
        } else {
            $e->id = $this->db->insert('words', [
                'q' => $e->q,
                'a' => $e->a,
                'answers1' => $e->answers1,
                'answers2' => $e->answers2,
                'dict_id' => $e->dict_id,
            ]);
        }
    }

    function entries(array $ids): array
    {
        $entries = [];
        foreach ($ids as $id) {
            $entries[] = $this->entry($id);
        }
        return $entries;
    }
}
