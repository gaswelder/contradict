<?php

class BlobStorage implements Storage
{
    private $write;
    private $data = [];

    function __construct($read, $write)
    {
        $this->write = $write;
        $this->data = json_decode($read(), true);
    }

    private function save()
    {
        call_user_func($this->write, json_encode($this->data));
    }

    function dicts(): array
    {
        $dicts = [];
        foreach ($this->data['dicts'] as $id => $row) {
            $dicts[] = Dict::parse($row);
        }
        return $dicts;
    }

    function dict(string $id): Dict
    {
        return Dict::parse($this->data['dicts'][$id]);
    }

    function dictStats(string $dict_id): Stats
    {
        $entries = $this->allEntries($dict_id);
        $totalEntries = count($entries);

        // Number of entries that have enough correct answers in both directions.
        $finished = 0;
        // Number of entries that are "in progress".
        $touched = 0;
        foreach ($entries as $e) {
            $isfinished = $e->answers1 >= Storage::GOAL && $e->answers2 >= Storage::GOAL;
            if ($isfinished) {
                $finished++;
                continue;
            }
            if ($e->touched) {
                $touched++;
            }
        }
        return new Stats($totalEntries, $finished, $touched);
    }

    function lastScores(string $dict_id): array
    {
        return [];
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
