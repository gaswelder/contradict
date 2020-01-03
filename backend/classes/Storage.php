<?php

class Storage
{
    private $data = [];

    // Whether we have modified the data.
    private $touched = false;

    private $fs;

    function __construct(FileSystem $fs)
    {
        $this->fs = $fs;
        if ($this->fs->exists('')) {
            $data = $this->fs->read('');
            if (substr($data, 0, 2) != '{"') {
                $data = gzuncompress($data);
            }
            $this->data = json_decode($data, true);
            $keys = ['dicts', 'words', 'scores'];
            foreach ($keys as $key) {
                if (!isset($this->data[$key]) || !is_array(($this->data[$key]))) {
                    throw new Exception("missing key '$key' from storage data");
                }
            }
        } else {
            $this->data = ['dicts' => [], 'words' => [], 'scores' => []];
        }
    }

    function __destruct()
    {
        $this->flush();
    }

    /**
     * Saves recent data changes to the storage.
     */
    function flush()
    {
        if (!$this->touched) {
            return;
        }
        $this->fs->write('', gzcompress(json_encode($this->data)));
        $this->touched = false;
    }

    function saveDict(Dict $d)
    {
        $this->data['dicts'][$d->id] = $d->format();
        $this->touched = true;
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
        return array_reverse($rows);
    }

    function saveScore(Score $score)
    {
        if (!$score->id) {
            $score->id = uniqid();
        }
        $this->data['scores'][$score->id] = $score->format();
        $this->touched = true;
    }

    function scores(): array
    {
        return array_map([Score::class, 'parse'], $this->data['scores']);
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

    function saveEntry(Entry $e): Entry
    {
        if (!$e->id) {
            $e->id = uniqid();
        }
        $this->data['words'][$e->id] = $e->format();
        $this->touched = true;
        return $e;
    }

    function hasEntry(string $dict_id, Entry $e): bool
    {
        foreach ($this->allEntries($dict_id) as $entry) {
            if ($entry->q == $e->q && $entry->a == $e->a) {
                return true;
            }
        }
        return false;
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

    function getSuccessRate($dict_id): float
    {
        $scores = $this->lastScores($dict_id);
        $total = 0;
        $n = 0;
        foreach ($scores as $score) {
            $n++;
            $total += $score->right / ($score->right + $score->wrong);
        }
        return $n > 0 ? $total / $n : 1;
    }
}
