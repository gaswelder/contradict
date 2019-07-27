<?php

class Entry
{
    const TABLE_NAME = 'words';

    public $q;
    public $a;
    public $answers1 = 0;
    public $answers2 = 0;
    public $id;
    public $dict_id;

    function format()
    {
        return [
            'q' => $this->q,
            'a' => $this->a,
            'id' => $this->id
        ];
    }
}
