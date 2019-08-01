<?php

class Score
{
    public $id;
    public $dict_id;
    public $right;
    public $wrong;

    static function parse($row)
    {
        $s = new self;
        $s->id = $row['id'];
        $s->dict_id = $row['dict_id'];
        $s->right = $row['right'];
        $s->wrong = $row['wrong'];
        return $s;
    }

    function format()
    {
        return [
            'id' => $this->id,
            'dict_id' => $this->dict_id,
            'right' => $this->right,
            'wrong' => $this->wrong
        ];
    }
}
