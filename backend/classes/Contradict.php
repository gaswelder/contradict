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
    const WINDOW = 200;

    private $touched = false;
    private $dataPath;
    private $data = [];

    function __construct(string $userID)
    {
        $this->dataPath = __DIR__ . "/../database-$userID.json";
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
        $this->data = json_decode($data, true);
    }

    function __destruct()
    {
        $this->flush();
    }

    function import(array $data)
    {
        $this->data = $data;
        $this->touched = true;
    }

    function export()
    {
        return $this->data;
    }

    /**
     * Returns all saved dicts.
     */
    function dicts(): array
    {
        $dicts = [];
        foreach ($this->data['dicts'] as $row) {
            $dicts[] = Dict::parse($row);
        }
        return $dicts;
    }

    /**
     * Returns a saved dict with the given id.
     */
    function getDict(string $id): Dict
    {
        return Dict::parse($this->data['dicts'][$id]);
    }

    function updateDict(string $dict_id, array $data)
    {
        $dict = $this->getDict($dict_id);
        if (!$dict) {
            throw new DictNotFound();
        }
        $dict->name = $data['name'] ?? $dict->name;
        $dict->lookupURLTemplate = $data['lookupURLTemplate'] ?? $dict->lookupURLTemplate;
        $this->saveDict($dict);
    }

    /**
     * Creates or updates a dict.
     */
    private function saveDict(Dict $d)
    {
        $this->data['dicts'][$d->id] = array_merge($this->data['dicts'][$d->id], $d->format());
        $this->touched = true;
    }

    function getEntry(string $id)
    {
        foreach ($this->dicts() as $d) {
            $e = $d->entry($id);
            if ($e) {
                return $e;
            }
        }
        return null;
    }

    function updateEntry($id, $q, $a)
    {
        $dict_id = '';
        foreach ($this->dicts() as $d) {
            $e = $d->entry($id);
            if ($e) {
                $dict_id = $d->id;
                break;
            }
        }
        $dict = $this->getDict($dict_id);

        // Save the entry.
        $entry = new Entry;
        $entry->id = $id;
        $entry->q = $q;
        $entry->a = $a;
        $dict->saveEntry($entry);
        $this->saveDict($dict);
    }

    function generateTest(string $dict_id): Test
    {
        $dict = $this->getDict($dict_id);
        $size = 20;
        $entries = $dict->allEntries();
        $pick1 = $this->pick($entries, $size, 0);
        $pick2 = $this->pick($entries, $size, 1);

        // Mark the entries as touched.
        foreach (array_merge($pick1, $pick2) as $e) {
            if (!$e->touched) {
                $e->touched = 1;
                $dict->saveEntry($e);
            }
        }
        $this->saveDict($dict);

        $questions1 = [];
        foreach ($pick1 as $entry) {
            $questions1[] = new Question($dict, $entry, false);
        }
        $questions2 = [];
        foreach ($pick2 as $entry) {
            $questions2[] = new Question($dict, $entry, true);
        }

        return new Test($questions1, $questions2);
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

    function submitTest(string $dict_id, array $answers): TestResults
    {
        $dict = $this->getDict($dict_id);

        $questions = [];
        $correct = [];
        foreach ($answers as $a) {
            $entry = $dict->entry($a->entryID);
            if (!$entry) {
                throw new Exception("couldn't find entry $dict_id/$a->entryID");
            }
            $question = new Question($dict, $entry, $a->reverse);
            $questions[] = $question;
            $ok = $question->checkAnswer($a->answer);
            $correct[] = $ok;
            if (!$ok) {
                continue;
            }

            // Update correct answer counters
            // For all questions that are correct, increment the corresponding counter (dir 0/1) and save.
            if ($question->reverse) {
                $entry->answers2++;
            } else {
                $entry->answers1++;
            }
            $dict->saveEntry($entry);
        }

        // Save a score record.
        $right = 0;
        $wrong = 0;
        foreach ($correct as $ok) {
            if ($ok) $right++;
            else $wrong++;
        }
        $score = new Score;
        $score->dict_id = $dict_id;
        $score->right = $right;
        $score->wrong = $wrong;

        $dict->saveScore($score);
        $this->saveDict($dict);
        $this->flush();

        return new TestResults($dict_id, $questions, $answers, $correct);
    }

    function appendWords(string $dict_id, array $entries): array
    {
        $dict = $this->getDict($dict_id);
        $added = 0;
        $skipped = 0;
        foreach ($entries as $entry) {
            if (!$dict->hasEntry($entry)) {
                $dict->saveEntry($entry);
                $added++;
            } else {
                $skipped++;
            }
        }
        $this->saveDict($dict);
        return compact('added', 'skipped');
    }

    function dictStats(string $dict_id): Stats
    {
        $dict = $this->getDict($dict_id);
        $entries = $dict->allEntries();

        $stats = new Stats;
        $stats->totalEntries = count($entries);

        foreach ($entries as $e) {
            $isfinished = $e->answers1 >= self::GOAL && $e->answers2 >= self::GOAL;
            if ($isfinished) {
                $stats->finished++;
                continue;
            }
            if ($e->touched) {
                $stats->touched++;
            }
        }

        $stats->successRate = $dict->getSuccessRate();
        return $stats;
    }

    function markTouch($id, $dir, $success)
    {
        $dict_id = '';
        $e = null;
        foreach ($this->dicts() as $d) {
            $e = $d->entry($id);
            if ($e) {
                $dict_id = $d->id;
                break;
            }
        }
        $dict = $this->getDict($dict_id);
        $new = function ($v) use ($success) {
            if ($success) {
                return $v + 1;
            } else {
                return max($v - 1, 0);
            }
        };
        if ($dir == 0) {
            $e->answers1 = $new($e->answers1);
        } else {
            $e->answers2 = $new($e->answers2);
        }
        $e->touched = true;
        $dict->saveEntry($e);
        $this->saveDict($dict);
    }

    /**
     * Saves recent data changes to the storage.
     */
    private function flush()
    {
        if (!$this->touched) {
            return;
        }
        file_put_contents($this->dataPath, gzcompress(json_encode($this->data)));
        $this->touched = false;
    }
}
