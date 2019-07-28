<?php

class Stats
{
    public $totalEntries;
    public $finished;
    public $touched;

    function __construct($totalEntries, $finished, $touched)
    {
        $this->totalEntries = $totalEntries;
        $this->finished = $finished;
        $this->touched = $touched;
    }

    function format()
    {
        return [
            'pairs' => floatval($this->totalEntries),
            'finished' => $this->finished / 2,
            'touched' => floatval($this->touched),
        ];
    }
}
