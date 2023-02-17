<?php

class App
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

    private $storage;

    function setStorage(Dictionaries $s)
    {
        $this->storage = $s;
    }

    function generateTest(string $dict_id): Test
    {
        $storage = $this->storage;
        $dict = $storage->dict($dict_id);
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
        $storage->saveDict($dict);

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
        $storage = $this->storage;
        $dict = $storage->dict($dict_id);

        $questions = [];
        $correct = [];
        foreach ($answers as $a) {
            $entry = $dict->entry($a->entryID);
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
        $storage->saveDict($dict);
        $storage->flush();

        return new TestResults($dict_id, $questions, $answers, $correct);
    }

    function appendWords(string $dict_id, array $entries): array
    {
        $storage = $this->storage;
        $dict = $storage->dict($dict_id);
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
        $storage->saveDict($dict);
        return compact('added', 'skipped');
    }

    function dicts(): array
    {
        return $this->storage->dicts();
    }

    function dictStats(string $dict_id): Stats
    {
        $storage = $this->storage;
        $dict = $storage->dict($dict_id);
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
}
