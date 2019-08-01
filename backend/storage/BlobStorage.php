<?php

class BlobStorage implements Storage
{
    private $write;
    private $data = [];

    function __construct($read, $write)
    {
        $this->write = $write;
        $this->data = json_decode($read(), true);
        $keys = ['dicts', 'words', 'scores'];
        foreach ($keys as $key) {
            if (!isset($this->data[$key]) || !is_array(($this->data[$key]))) {
                throw new Exception("missing key '$key' from storage data");
            }
        }
    }

    private function save()
    {
        call_user_func($this->write, json_encode($this->data));
    }

    function saveDict(Dict $d)
    {
        $this->data['dicts'][$d->id] = $d->format();
    }

    function dicts(): array
    {
        $dicts = [];
        foreach ($this->data['dicts'] as $row) {
            $dicts[] = Dict::parse($row);
        }
        return $dicts;
    }

    function dict(string $id): Dict
    {
        return Dict::parse($this->data['dicts'][$id]);
    }

    function lastScores(string $dict_id): array
    {
        $rows = [];
        foreach ($this->data['scores'] as $score) {
            if ($score['dict_id'] != $dict_id) {
                continue;
            }
            $rows[] = Score::parse($score);
        }
        return $rows;
    }

    function saveScore(Score $score)
    {
        if (!$score->id) {
            $score->id = uniqid();
        }
        $this->data['scores'][$score->id] = $score->format();
        $this->save();
    }

    function entry(string $id): Entry
    {
        return Entry::parse($this->data['words'][$id]);
    }

    function entries(array $ids): array
    {
        $entries = [];
        foreach ($ids as $id) {
            $entries[] = $this->entry($id);
        }
        return $entries;
    }

    function allEntries(string $dict_id): array
    {
        $entries = [];
        foreach ($this->data['words'] as $row) {
            if ($row['dict_id'] != $dict_id) {
                continue;
            }
            $entries[] = Entry::parse($row);
        }
        return $entries;
    }

    function saveEntry(Entry $e)
    {
        if (!$e->id) {
            $e->id = uniqid();
        }
        $this->data['words'][$e->id] = $e;
        $this->save();
    }

    function similars(Entry $e, bool $reverse): array
    {
        $ee = $this->allEntries($e->dict_id);
        $sim = [];
        foreach ($ee as $entry) {
            if ($entry->id == $e->id) continue;
            if ($reverse && $entry->a != $e->a) continue;
            if (!$reverse && $entry->q != $e->q) continue;
            $sim[] = $entry;
        }
        return $sim;
    }
}
