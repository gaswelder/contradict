<?php

class Answer
{
    public $entryID;
    public $answer;
    public $reverse;

    static function parse($row): Answer
    {
        $a = new self;
        $a->entryID = $row['entryID'];
        $a->answer = $row['answer'];
        $a->reverse = $row['reverse'];
        return $a;
    }
}
