<?php

class TestResults
{
    private $results;
    private $dict_id;

    function __construct($dict_id, $results)
    {
        $this->results = $results;
        $this->dict_id = $dict_id;
    }

    function format()
    {
        $ok = [];
        $fail = [];
        foreach ($this->results as $result) {
            if ($result['correct']) {
                $ok[] = $result;
            } else {
                $fail[] = $result;
            }
        }

        return [
            'dict_id' => $this->dict_id,
            'results' => $this->results,
            'stats' => [
                'right' => count($ok),
                'wrong' => count($fail)
            ]
        ];
    }
}
