<?php

class Entry
{
    public $q;
    public $a;
    public $answers1 = 0;
    public $answers2 = 0;
    public $id;
    public $dict_id;
    public $touched = false;

    function format(): array
    {
        $row = json_decode(json_encode($this), true);
        $row['touched'] = $this->touched ? 1 : 0;
        return $row;
    }

    static function parse(array $row): self
    {
        $keys = ['id', 'q', 'a', 'answers1', 'answers2', 'dict_id', 'touched'];
        $e = new Entry;
        foreach ($keys as $k) {
            $e->$k = $row[$k];
        }
        $e->touched = !!$e->touched;
        $e->answers1 = intval($e->answers1);
        $e->answers2 = intval($e->answers2);
        return $e;
    }

    function wikiURL()
    {
        $words = explode(' ', $this->q);
        if (empty($words)) return null;
        if (in_array(strtolower($words[0]), ['das', 'die', 'der'])) {
            array_shift($words);
        }
        if (count($words) != 1) return null;

        $wiki = $words[0];
        return 'https://de.wiktionary.org/w/index.php?search=' . urlencode($wiki);
    }
}
