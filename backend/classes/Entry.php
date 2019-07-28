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
        return [
            'q' => $this->q,
            'a' => $this->a,
            'id' => $this->id
        ];
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
}
