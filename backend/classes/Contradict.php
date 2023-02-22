<?php

class DictNotFound extends Exception
{
}

class Contradict
{
    /**
     * How many correct answers needed for an entry to be "finished".
     */
    const GOAL = 10;

    /**
     * How many entries are in the "learning pool".
     * This limit is applied separately for both directions,
     * so the actual pool limit is twice this value.
     */
    const WINDOW = 100;

    private $dataPath;
    private $data = [];

    function __construct(string $dataPath)
    {
        $this->dataPath = $dataPath;
        $this->load();
    }

    private function load()
    {
        if (!file_exists($this->dataPath)) {
            $this->data = ['dicts' => []];
            return;
        }
        $data = file_get_contents($this->dataPath);
        if (substr($data, 0, 1) != '{') {
            $data = gzuncompress($data);
        }
        $data = json_decode($data, true);
        foreach ($data['dicts'] as $id => $dict) {
            if (!array_key_exists('lookupURLTemplates', $dict)) {
                $data['dicts'][$id]['lookupURLTemplates'] = [];
                $t = trim($dict['lookupURLTemplate'] ?? "");
                if ($t) {
                    $data['dicts'][$id]['lookupURLTemplates'][] = $t;
                }
                unset($data['dicts'][$id]['lookupURLTemplate']);
            }
        }
        $this->data = $data;
    }

    function import(array $data)
    {
        $this->begin()->import($data)->commit();
    }

    function export()
    {
        return $this->data;
    }

    function getDicts(): array
    {
        $list = [];
        foreach ($this->_getDicts() as $row) {
            $entries = $this->_getEntries($row['id']);
            $totalEntries = count($entries);
            $finished = 0;
            $touched = 0;
            foreach ($entries as $e) {
                $isfinished = $e->answers1 >= self::GOAL && $e->answers2 >= self::GOAL;
                if ($isfinished) {
                    $finished++;
                    continue;
                }
                if ($e->touched) {
                    $touched++;
                }
            }
            $list[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'lookupURLTemplates' => $row['lookupURLTemplates'] ?? [],
                'stats' => [
                    'pairs' => floatval($totalEntries),
                    'finished' => $finished,
                    'touched' => floatval($touched),
                ]
            ];
        }
        return $list;
    }

    function getDict(string $id)
    {
        return [
            'name' => $this->_getDict($id)->name
        ];
    }

    function updateDict(string $dict_id, array $data)
    {
        $this->begin()->updateDict($dict_id, $data)->commit();
    }

    function addDict(string $name)
    {
        $id = uniqid();
        $this->begin()->insertDict(['id' => $id, 'name' => $name])->commit();
        return $id;
    }

    function getEntries(string $dictID): array
    {
        $list = [];
        foreach ($this->_getEntries($dictID) as $e) {
            $list[] = [
                'id' => $e->id
            ];
        }
        return $list;
    }

    function getEntry(string $id)
    {
        $dictID = $this->_getEntryDict($id);
        return $this->_getentry($dictID, $id);
    }

    function updateEntry(string $id, string $q, string $a)
    {
        $dictID = $this->_getEntryDict($id);
        $this->begin()->updateEntry($dictID, $id, compact('q', 'a'))->commit();
    }

    function generateTest(string $dict_id)
    {
        $writer = $this->begin();
        $size = 20;
        $entries = $this->_getEntries($dict_id);
        $pick1 = $this->pick($entries, $size, 0);
        $pick2 = $this->pick($entries, $size, 1);
        $f = [
            'tuples1' => [],
            'tuples2' => [],
        ];
        foreach ($pick1 as $i => $entry) {
            $f['tuples1'][$i] = [
                'id' => $entry->id,
                'q' => $entry->q,
                'a' => $entry->a,
                'times' => $entry->answers1,
                'urls' => $this->wikiURLs($dict_id, $entry->q),
                'reverse' => false,
            ];
        }
        foreach ($pick2 as $i => $entry) {
            $f['tuples2'][$i] = [
                'id' => $entry->id,
                'q' => $entry->a,
                'a' => $entry->q,
                'times' => $entry->answers2,
                'urls' => $this->wikiURLs($dict_id, $entry->q),
                'reverse' => true,
            ];
        }
        // Mark the entries as touched.
        foreach (array_merge($pick1, $pick2) as $e) {
            if (!$e->touched) {
                $writer->updateEntry($dict_id, $e->id, ['touched' => 1]);
            }
        }
        $writer->commit();
        return $f;
    }

    private function pick(array $entries, int $size, $dir): array
    {
        $unfinished = [];
        foreach ($entries as $e) {
            if ($dir == 0 && $e->answers1 >= self::GOAL) {
                continue;
            }
            if ($dir == 1 && $e->answers2 >= self::GOAL) {
                continue;
            }
            $unfinished[] = $e;
        }
        usort($unfinished, function ($a, $b) {
            return $b->touched <=> $a->touched;
        });
        $unfinished = array_slice($unfinished, 0, self::WINDOW);
        shuffle($unfinished);
        $entries = array_slice($unfinished, 0, $size);
        return $entries;
    }

    function submitTest(string $dict_id, array $directions, array $ids, array $aa): array
    {
        $writer = $this->begin();
        $results = [];
        $right = 0;
        $wrong = 0;
        foreach ($aa as $i => $answer) {
            $entryID = $ids[$i];
            $reverse = $directions[$i] == 1;
            $entryRow = $this->data['dicts'][$dict_id]['words'][$entryID];

            $entry = $this->_getentry($dict_id, $entryID);
            if (!$entry) {
                throw new Exception("couldn't find entry $dict_id/$entryID");
            }
            $realAnswer = $reverse ? $entryRow['q'] : $entryRow['a'];
            $ok = mb_strtolower($realAnswer) == mb_strtolower($answer);

            if ($ok) {
                $right++;
                // Update correct answer counters
                // For all questions that are correct, increment the corresponding counter (dir 0/1) and save.
                if ($reverse) {
                    $writer->updateEntry($dict_id, $entryID, ['answers2' => $entry->answers2 + 1]);
                    $entry->answers2++;
                } else {
                    $writer->updateEntry($dict_id, $entryID, ['answers1' => $entry->answers1 + 1]);
                    $entry->answers1++;
                }
            } else {
                $wrong++;
            }
            $results[] = [
                "answer" => $answer,
                "question" => [
                    'id' => $entryID,
                    'q' => $reverse ? $entryRow['a'] : $entryRow['q'],
                    'a' => $reverse ? $entryRow['q'] : $entryRow['a'],
                    'times' => $reverse ? $entry->answers2 : $entry->answers1,
                    'urls' => $this->wikiURLs($dict_id, $entryRow['q']),
                    'dir' => $reverse ? 1 : 0
                ],
                "correct" => $ok
            ];
        }
        $writer->commit();
        return [
            'dict_id' => $dict_id,
            'results' => $results,
        ];
    }

    private function wikiURLs(string $dictID, string $entryText): array
    {
        $words = explode(' ', $entryText);
        if (empty($words)) {
            return [];
        }
        if (in_array(strtolower($words[0]), ['das', 'die', 'der', 'to'])) {
            array_shift($words);
        }
        if (count($words) != 1) {
            return [];
        }
        $word = $words[0];
        $dict = $this->_getDict($dictID);
        return array_map(function ($template) use ($word) {
            return str_replace('{{word}}', urlencode($word), $template);
        }, $dict->lookupURLTemplates);
    }

    /**
     * Adds words to a dictionary.
     */
    function appendWords(string $dict_id, array $lines)
    {
        $writer = $this->begin();
        $added = 0;
        $skipped = 0;
        $ids = [];
        foreach ($lines as $tuple) {
            if ($this->hasEntry($dict_id, $tuple[0], $tuple[1])) {
                $skipped++;
                continue;
            }
            $id = uniqid();
            $writer->insertEntry($dict_id, ['id' => $id, 'q' => $tuple[0], 'a' => $tuple[1]]);
            $added++;
            $ids[] = $id;
        }
        $writer->commit();
        return compact('added', 'skipped', 'ids');
    }

    function markTouch(string $dictID, string $entryID, $dir, $success)
    {
        $reverse = $dir == 1;
        $e = $this->_getentry($dictID, $entryID);
        $new = function ($v) use ($success) {
            if ($success) {
                return $v + 1;
            } else {
                return max($v - 1, 0);
            }
        };
        $upd = [
            'touched' => true,
        ];
        if ($reverse) {
            $upd['answers2'] = $new($e->answers2);
        } else {
            $upd['answers1'] = $new($e->answers1);
        }
        $this->begin()->updateEntry($dictID, $entryID, $upd)->commit();
    }

    private function hasEntry($dictId, $q, $a): bool
    {
        foreach ($this->_getEntries($dictId) as $entry) {
            if ($entry->q == $q && $entry->a == $a) {
                return true;
            }
        }
        return false;
    }

    private function begin()
    {
        return new writer($this->dataPath, $this->data);
    }

    private function _getDicts()
    {
        return $this->data['dicts'];
    }

    /**
     * Returns a saved dict with the given id.
     */
    private function _getDict(string $id): Dict
    {
        return Dict::parse($this->data['dicts'][$id]);
    }

    private function _getEntryDict($entryID)
    {
        foreach ($this->data['dicts'] as $dictID => $drow) {
            if (array_key_exists($entryID, $drow['words'])) {
                return $dictID;
            }
        }
        return null;
    }

    private function _getEntries($dictId)
    {
        $entries = [];
        foreach ($this->data['dicts'][$dictId]['words'] as $row) {
            $e = new Entry;
            $e->id = $row['id'];
            $e->q = $row['q'];
            $e->a = $row['a'];
            $e->answers1 = intval($row['answers1']);
            $e->answers2 = intval($row['answers2']);
            $e->touched = !!$row['touched'];
            $entries[] = $e;
        }
        return $entries;
    }

    private function _getentry(string $dict_id, string $id): ?Entry
    {
        $row = $this->data['dicts'][$dict_id]['words'][$id] ?? null;
        if (!$row) {
            return null;
        }
        $e = new Entry;
        $e->id = $row['id'];
        $e->q = $row['q'];
        $e->a = $row['a'];
        $e->answers1 = intval($row['answers1']);
        $e->answers2 = intval($row['answers2']);
        $e->touched = !!$row['touched'];
        return $e;
    }
}

class writer
{
    private $touched = false;
    private $data;
    private $dataPath;

    function __construct($path, &$data)
    {
        $this->data = &$data;
        $this->dataPath = $path;
    }

    function insertDict($data)
    {
        $id = $data['id'] ?? uniqid();
        $this->data['dicts'][$id] = [
            'id' => $id,
            'name' => $data['name'],
            'lookupURLTemplates' => $data['lookupURLTemplates'] ?? [],
            'words' => []
        ];
        $this->touched = true;
        return $this;
    }

    function updateDict($id, $data)
    {
        $ok = ['name', 'lookupURLTemplates'];
        foreach ($data as $k => $v) {
            if (!in_array($k, $ok)) {
                throw new Error("Unknown dict field: $k");
            }
            $this->data['dicts'][$id][$k] = $v;
        }
        $this->touched = true;
        return $this;
    }

    function insertEntry($dictID, $data)
    {
        $id = $data['id'] ?? uniqid();
        $this->data['dicts'][$dictID]['words'][$id] = [
            'id' => $id,
            'q' => $data['q'],
            'a' => $data['a'],
            'answers1' => 0,
            'answers2' => 0,
            'touched' => 0,
        ];
        $this->touched = true;
        return $this;
    }

    function updateEntry($dictID, $id, $data)
    {
        foreach ($data as $k => $v) {
            $this->data['dicts'][$dictID]['words'][$id][$k] = $v;
        }
        $this->touched = true;
        return $this;
    }

    function import($data)
    {
        $this->data = $data;
        $this->touched = true;
        return $this;
    }

    function commit()
    {
        if (!$this->touched) {
            return;
        }
        file_put_contents($this->dataPath, gzcompress(json_encode($this->data)));
        $this->touched = false;
    }
}
