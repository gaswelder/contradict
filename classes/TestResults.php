<?php

class TestResults
{
    private $results;
    private $dict_id;
    private $stats;

    function __construct($dict_id, $stats, $results)
    {
        $this->results = $results;
        $this->dict_id = $dict_id;
        $this->stats = $stats;
    }

    function format()
    {
        return [
            'dict_id' => $this->dict_id,
            'results' => $this->results,
            'stats' => $this->stats,
        ];
    }
}
