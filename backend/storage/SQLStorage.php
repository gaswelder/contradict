<?php

class SQLStorage implements Storage
{
    /**
     * @var \DB\Client
     */
    private $db;

    function __construct($path)
    {
        $this->db = \DB\Client::sqlite($path);
    }

    function saveDict(Dict $d)
    {
        if ($d->id) {
            $this->db->insert('dicts', $d->format());
        } else {
            $this->db->update('dicts', $d->format(), ['id' => $d->id]);
        }
    }

    function dicts(): array
    {
        $rows = $this->db->getRows("select id, name from 'dicts'");
        return array_map([Dict::class, 'parse'], $rows);
    }

    function dict(string $id): Dict
    {
        $row = $this->db->getRow("select id, name from 'dicts' where id = ?", $id);
        return Dict::parse($row);
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

    function scores(): array
    {
        $rows = $this->db->getRows('select id, right, wrong, dict_id from results order by id');
        return array_map([Score::class, 'parse'], $rows);
    }

    function saveScore(Score $s)
    {
        if ($s->id) {
            throw new Exception("trying to save score with ID already defined");
        }
        $this->db->insert('results', $s->format());
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
