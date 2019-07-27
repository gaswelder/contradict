<?php

class Test
{
    private $tuples1;
    private $tuples2;

    function __construct($tuples1, $tuples2)
    {
        $this->tuples1 = $tuples1;
        $this->tuples2 = $tuples2;
    }

    private function ft($tuples)
    {
        return array_map(function (Question $tuple) {
            return $tuple->format();
        }, $tuples);
    }

    function format()
    {
        return [
            'tuples1' => $this->ft($this->tuples1),
            'tuples2' => $this->ft($this->tuples2),
        ];
    }
}
