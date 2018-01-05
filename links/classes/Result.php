<?php

class Result
{
    private $answer;

    // Entries that match the question
    private $entries;

    // Entry that matched both the question and the answer
    private $match;

    function __construct(Answer $answer, $entries, $match)
    {
        $this->answer = $answer;
        $this->entries = $entries;
        $this->match = $match;
    }

    function ok() {
        return $this->match != null;
    }

    function question() {
        return $this->answer->q;
    }

    function answer() {
        return $this->answer->a;
    }

    function entries() {
        return $this->entries;
    }

    function match() {
        return $this->match;
    }

    function dir() {
        return $this->answer->dir;
    }
}
