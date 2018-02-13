<?php
use havana\dbobject;

class TestResult extends dbobject
{
    const TABLE_NAME = 'results';
    const DATABASE = 'sqlite://dict.sqlite';

    public $t;
    public $right;
    public $wrong;

    function __construct()
    {
        $this->t = time();
    }
}
