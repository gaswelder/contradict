<?php
class Dict
{
    const GOAL = 10;

    static function load()
    {
        return new self();
    }

    function entry($id)
    {
        return Entry::get($id);
    }

    function append($tuples)
    {
        foreach ($tuples as $t) {
            $entry = new Entry;
            $entry->q = $t[0];
            $entry->a = $t[1];
            if ($this->has($entry)) {
                continue;
            }
            $entry->save();
        }
        return $this;
    }

    private function has(Entry $e)
    {
        return Entry::db()->getValue("select count(*) from words where q = ? and a = ?", $e->q, $e->a) > 0;
    }

    /**
     * Checks a given answer, returns a result.
     *
     * @param Answer $answer
     * @return Result
     */
    function result(Answer $answer)
    {
        $dir = $answer->dir;
        $q = $answer->q;
        $a = $answer->a;

        // Find all rows with this question
        $questionField = $dir == 0 ? 'q' : 'a';
        $timesAnsweredField = $dir == 0 ? 'answers1' : 'answers2';
        $entries = Entry::fromRows(Entry::db()->getRows("
            select * from words
            where lower($questionField) = ?
            and $timesAnsweredField < ?", mb_strtolower($q), self::GOAL));

        // Find one that matches.
        $match = array_reduce($entries, function ($prev, Entry $entry) use ($dir, $a) {
            if ($prev) return $prev;
            if ($entry->match($dir, $a)) return $entry;
            return null;
        });

        if ($match) {
            $match->addScore($dir);
            $match->save();
        }

        return new Result($answer, $entries, $match);
    }
}
