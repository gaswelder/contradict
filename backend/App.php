<?php

class App
{
    private $s;

    function __construct(Storage $s)
    {
        $this->s = $s;
    }

    function generateTest($dict_id): Test
    {
        $size = 20;
        $entries = $this->s->allEntries($dict_id);
        $pick1 = $this->pick($entries, $size, 0);
        $pick2 = $this->pick($entries, $size, 1);

        // Mark the entries as touched.
        foreach (array_merge($pick1, $pick2) as $e) {
            if (!$e->touched) {
                $e->touched = 1;
                $this->s->saveEntry($e);
            }
        }

        $questions1 = [];
        foreach ($pick1 as $entry) {
            $questions1[] = new Question($entry, false);
        }
        $questions2 = [];
        foreach ($pick2 as $entry) {
            $questions2[] = new Question($entry, true);
        }

        $test = new Test($questions1, $questions2);
        return $test;
    }

    private function pick(array $entries, int $size, $dir): array
    {
        $unfinished = [];
        foreach ($entries as $e) {
            if ($dir == 0 && $e->answers1 >= Storage::GOAL) {
                continue;
            }
            if ($dir == 1 && $e->answers2 >= Storage::GOAL) {
                continue;
            }
            $unfinished[] = $e;
        }
        usort($unfinished, function ($a, $b) {
            return $b->touched <=> $a->touched;
        });
        $unfinished = array_slice($unfinished, 0, Storage::WINDOW);
        shuffle($unfinished);
        $entries = array_slice($unfinished, 0, $size);
        return $entries;
    }

    function verifyTest(string $dict_id, array $answers): TestResults
    {
        $storage = $this->s;

        $ids = array_map(function (Answer $a) {
            return $a->entryID;
        }, $answers);
        $entries = $storage->entries($ids);

        $qa = [];
        foreach ($entries as $i => $entry) {
            $qa[] = [new Question($entry, $answers[$i]->reverse), $answers[$i]->answer];
        }

        $results = [];
        foreach ($qa as $i => $tuple) {
            [$question, $answer] = $tuple;
            $result = [
                'question' => $question->format(),
                'answer' => $answer,
                'correct' => checkAnswer($question, $answer)
            ];
            $results[] = $result;
        }

        // Update correct answer counters
        // For all questions that are correct, increment the corresponding counter (dir 0/1) and save.
        foreach ($qa as $i => $tuple) {
            [$question, $answer] = $tuple;
            if (!checkAnswer($question, $answer)) {
                continue;
            }
            if ($question->reverse) {
                $question->entry()->answers2++;
            } else {
                $question->entry()->answers1++;
            }
            $this->s->saveEntry($question->entry());
        }

        return new TestResults($dict_id, $results);
    }

    function appendWords(string $dict_id, array $entries): int
    {
        $n = 0;
        foreach ($entries as $entry) {
            if ($this->s->hasEntry($dict_id, $entry)) {
                continue;
            }
            $this->s->saveEntry($entry);
            $n++;
        }
        return $n;
    }

    function dicts(): array
    {
        return $this->s->dicts();
    }

    function dictStats($dict_id): Stats
    {
        return dictStats($this->s, $dict_id);
    }

    function hint(Question $q)
    {
        return hint($this->s, $q);
    }

    function entry($id): Entry
    {
        return $this->s->entry($id);
    }

    function updateEntry(Entry $entry)
    {
        $this->s->saveEntry($entry);
    }
}
