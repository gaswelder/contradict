<?php

class TestResults
{
    private $dict_id;
    private $questions;
    private $answers;
    private $correct;

    function __construct(string $dict_id, array $questions, array $answers, array $correct)
    {
        $this->dict_id = $dict_id;
        $this->questions = $questions;
        $this->answers = $answers;
        $this->correct = $correct;
    }

    function format()
    {
        $results = [];
        foreach ($this->questions as $i => $question) {
            $answer = $this->answers[$i];
            $results[] = [
                "answer" => $answer->answer,
                "question" => $question->format(),
                "correct" => $this->correct[$i]
            ];
        }

        return [
            'dict_id' => $this->dict_id,
            'results' => $results,
        ];
    }
}
