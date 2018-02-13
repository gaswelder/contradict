<?php
use havana\dbobject;

class TestResult extends dbobject
{
    const TABLE_NAME = 'results';
    const DATABASE = 'sqlite://dict.sqlite';

    public $t;
    public $right;
    public $wrong;

    function __construct($right, $wrong)
    {
        $this->right = $right;
        $this->wrong = $wrong;
        $this->t = time();
    }
}
