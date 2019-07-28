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

        return new Stats($totalEntries, $finished, $touched, $this->successRate($dict_id));
    }

    private function successRate($dict_id)
    {
        $scores = $this->db->getValues(
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

    /**
     * Generates a test for a given dictionary.
     */
    function test(string $dict_id): Test
    {
        $size = 20;
        return new Test($this->pick($dict_id, $size, 0), $this->pick($dict_id, $size, 1));
    }

    /**
     * Returns a given number of random questions.
     *
     * @param int $dict_id Identifier of the dictionary to get questions from
     * @param int $size Number of questions
     * @param int $dir Translation direction: 0 for direct, 1 for reverse
     * @return array
     */
    private function pick($dict_id, $size, $dir)
    {
        $correctAnswers = $dir == 0 ? 'answers1' : 'answers2';
        $size = intval($size);
        $goal = Storage::GOAL;
        $windowSize = Storage::WINDOW;

        $rows = $this->db->getRows("
            select * from 
                (select * from words
                    where dict_id = ?
                    and $correctAnswers < $goal
                    order by touched desc, id
                    limit $windowSize) a
            order by random()
            limit $size", $dict_id);

        $entries = [];
        $ids = [];
        foreach ($rows as $row) {
            $ids[] = $row['id'];
            $e = new Entry($row['id']);
            $e->q = $row['q'];
            $e->a = $row['a'];
            $e->answers1 = $row['answers1'];
            $e->answers2 = $row['answers2'];
            $e->id = $row['id'];
            $e->dict_id = $row['dict_id'];
            $entries[] = $e;
        }

        $set = '(' . implode(', ', $ids) . ')';
        $this->db->exec("update words set touched = 1 where id in $set");

        $questions = [];
        foreach ($entries as $entry) {
            $questions[] = new Question($entry, $dir == 1);
        }
        return $questions;
    }

    function similars(Question $q)
    {
        $filter = $q->reverse ? ['a' => $q->entry()->a] : ['q' => $q->entry()->q];
        $rows = $this->db->select('words', ['q', 'a', 'answers1', 'answers2', 'dict_id'], $filter, 'id');

        $entries = [];
        foreach ($rows as $row) {
            $e = Entry::parse($row);
            if ($e->id == $q->entry()->id) {
                continue;
            }
            $entries[] = $e;
        }
        return $entries;
    }

    function entry(string $id): Entry
    {
        $row = $this->db->getRow("select id, q, a, answers1, answers2, dict_id from 'words' where id = ?", $id);
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
