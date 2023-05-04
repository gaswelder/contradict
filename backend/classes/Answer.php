<?php

class Answer
{
    public $entryID;
    public $answer;

    static function parse($row): Answer
    {
        $a = new self;
        $a->entryID = $row['entryID'];
        $a->answer = $row['answer'];
        return $a;
    }
}
