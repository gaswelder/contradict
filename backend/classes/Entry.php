<?php

class Entry
{
    public $q;
    public $a;
    public $answers1 = 0;
    public $answers2 = 0;
    public $id;
    public $dict_id;
    public $touched;

    function format()
    {
        return json_decode(json_encode($this), true);
    }

    static function parse($row)
    {
        $keys = ['id', 'q', 'a', 'answers1', 'answers2', 'dict_id', 'touched'];
        $e = new Entry;
        foreach ($keys as $k) {
            $e->$k = $row[$k];
        }
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
