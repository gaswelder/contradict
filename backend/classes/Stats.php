<?php

class Stats
{
    public $totalEntries = 0;
    public $finished = 0;
    public $touched = 0;
    public $successRate = 0;

    function format()
    {
        return [
            'pairs' => floatval($this->totalEntries),
            'finished' => $this->finished,
            'touched' => floatval($this->touched),
            'successRate' => $this->successRate
        ];
    }
}
