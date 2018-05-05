<?php
class Dict
{
    const GOAL = 10;

    static function load()
    {
        return new self();
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
}
