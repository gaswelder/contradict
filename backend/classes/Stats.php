<?php

class Stats
{
    private $totalEntries;
    private $finished;
    private $touched;
    private $successRate;

    function __construct($totalEntries, $finished, $touched, $successRate)
    {
        $this->totalEntries = $totalEntries;
        $this->finished = $finished;
        $this->touched = $touched;
        $this->successRate = $successRate;
    }

    function format()
    {
        return [
            'pairs' => floatval($this->totalEntries),
            'finished' => $this->finished / 2,
            'touched' => floatval($this->touched),
            'successRate' => $this->successRate,
        ];
    }
}
