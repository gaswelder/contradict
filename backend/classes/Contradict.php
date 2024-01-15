<?php

class DictNotFound extends Exception
{
}

class Contradict
{
    /**
     * How many correct answers are needed for an entry to be "finished".
     */
    const GOAL = 6;

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
            $data = gzdecode($data);
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
        $r = $this->reader();
        $list = [];
        foreach ($r->getDicts() as $row) {
            $entries = $r->getEntries($row['id']);
            $finished = 0;
            $inProgress = 0;
            foreach ($entries as $e) {
                if ($e['answers1'] >= self::GOAL) {
                    $finished++;
                    continue;
                }
                if ($e['touched']) {
                    $inProgress++;
                }
            }
            $list[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'lookupURLTemplates' => $row['lookupURLTemplates'] ?? [],
                'windowSize' => $row['windowSize'],
                'stats' => [
                    'transitions' => $row['stats'],
                    'total' => count($entries),
                    'finished' => $finished,
                    'inProgress' => $inProgress
                ]
            ];
        }
        return $list;
    }

    function getDict(string $id)
    {
        $dict = $this->reader()->getDict($id);
        return [
            'name' => $dict['name'],
            'windowSize' => $dict['windowSize'],
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
        foreach ($this->reader()->getEntries($dictID) as $e) {
            $list[] = [
                'id' => $e['id']
            ];
        }
        return $list;
    }

    function getEntry(string $dictID, string $id)
    {
        return $this->reader()->getEntry($dictID, $id);
    }

    function updateEntry(string $dictID, string $id, string $q, string $a)
    {
        $this->begin()->updateEntry($dictID, $id, compact('q', 'a'))->commit();
    }

    function generateTest(string $dict_id, int $size)
    {
        $dict = $this->reader()->getDict($dict_id);

        // Exclude finished entries.
        $entries = array_filter($this->reader()->getEntries($dict_id), function ($e) {
            return $e['answers1'] < self::GOAL;
        });

        $mode = "shuffle";
        if ($mode == "shuffle") {
            shuffle($entries);
        } else {
            // Use a sliding window, include touched words first, then untouched.
            usort($entries, function ($a, $b) {
                return $b['touched'] <=> $a['touched'];
            });
            $entries = array_slice($entries, 0, $dict['windowSize']);
            // Delay those with higher answer counts.
            $ordering = function ($a, $b) {
                return [$a['touched'], $a['answers1'], rand()] <=> [$b['touched'], $b['answers1'], rand()];
            };
            usort($entries, $ordering);
        }

        // Take $size from the ordered window.
        $entries = array_slice($entries, 0, $size);
        $tuples = [];
        foreach ($entries as $entry) {
            $tuples[] = [
                'id' => $entry['id'],
                'q' => $entry['q'],
                'a' => $entry['a'],
                'times' => $entry['touched'],
                'score' => $entry['answers1'],
                'urls' => $this->wikiURLs($dict_id, $entry['q']),
            ];
        }
        return ['tuples1' => $tuples];
    }

    function getSheet(string $dict_id)
    {
        $entries = $this->reader()->getEntries($dict_id);
        usort($entries, function ($a, $b) {
            return [$a['answers1'], $b['touched']] <=> [$b['answers1'], $a['touched']];
        });

        $size = 100;
        $r = [];
        foreach (array_slice($entries, 0, $size) as $e) {
            $r[] = [
                'id' => $e['id'],
                'q' => $e['q'],
                'a' => $e['a'],
                'score' => $e['answers1'],
                'urls' => $this->wikiURLs($dict_id, $e['q'])
            ];
        }
        return $r;
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
        if (count($words) == 0) {
            return [];
        }
        $word = implode(' ', $words);
        $dict = $this->reader()->getDict($dictID);
        return array_map(function ($template) use ($word) {
            return str_replace('{{word}}', urlencode($word), $template);
        }, $dict['lookupURLTemplates']);
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

    function markTouch(string $dictID, string $entryID, bool $success)
    {
        $e = $this->reader()->getEntry($dictID, $entryID);
        $upd = [
            'answers1' => $e['answers1'],
            'touched' => $e['touched'] + 1
        ];
        if ($success) {
            $upd['answers1']++;
        } else {
            $upd['answers1'] = max($upd['answers1'] - 1, 0);
        }
        $this->begin()->updateEntry($dictID, $entryID, $upd)->commit();
    }

    function deleteEntry(string $dictID, string $entryID)
    {
        $this->begin()->deleteEntry($dictID, $entryID)->commit();
    }

    private function hasEntry(string $dictId, $q, $a): bool
    {
        foreach ($this->reader()->getEntries($dictId) as $entry) {
            if ($entry['q'] == $q && $entry['a'] == $a) {
                return true;
            }
        }
        return false;
    }

    private function begin()
    {
        return new writer($this->dataPath, $this->data);
    }

    private function reader()
    {
        return new reader($this->data);
    }
}

class reader
{
    private $data;

    function __construct($data)
    {
        $this->data = &$data;
    }

    function getDicts()
    {
        return array_map([self::class, '_parseDict'], $this->data['dicts']);
    }

    /**
     * Returns a saved dict with the given id.
     */
    function getDict(string $id)
    {
        return self::_parseDict($this->data['dicts'][$id]);
    }

    private static function _parseDict($row)
    {
        $d['id'] = $row['id'];
        $d['name'] = $row['name'];
        $d['lookupURLTemplates'] = $row['lookupURLTemplates'] ?? [];
        $d['stats'] = $row['stats'] ?? [];
        $d['windowSize'] = $row['windowSize'] ?? 1000;
        return $d;
    }

    function getEntries(string $dictId)
    {
        $entries = [];
        foreach ($this->data['dicts'][$dictId]['words'] as $row) {
            $entries[] = self::_parseEntry($row);
        }
        return $entries;
    }

    function getEntry(string $dict_id, string $id)
    {
        $row = $this->data['dicts'][$dict_id]['words'][$id] ?? null;
        if (!$row) {
            return null;
        }
        return self::_parseEntry($row);
    }

    private static function _parseEntry($row)
    {
        $row['answers1'] = intval($row['answers1']);
        $row['touched'] = intval($row['touched']);
        return $row;
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
        $ok = ['name', 'lookupURLTemplates', 'windowSize'];
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
            'touched' => 0,
        ];
        $this->touched = true;
        return $this;
    }

    function updateEntry($dictID, $id, $data)
    {
        foreach ($data as $k => $v) {
            if ($k == 'answers1') {
                $v1 = $this->data['dicts'][$dictID]['words'][$id][$k];
                $this->data['dicts'][$dictID]['stats']["$v1-$v"] = ($this->data['dicts'][$dictID]['stats']["$v1-$v"] ?? 0) + 1;
            }
            $this->data['dicts'][$dictID]['words'][$id][$k] = $v;
        }
        $this->touched = true;
        return $this;
    }

    function deleteEntry($dictID, $id)
    {
        unset($this->data['dicts'][$dictID]['words'][$id]);
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
        file_put_contents($this->dataPath, gzencode(json_encode($this->data)));
        $this->touched = false;
    }
}
