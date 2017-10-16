<?php

use havana\dbobject;

class Link extends dbobject
{
    const TABLE_NAME = 'links';

    public $created_at;
    public $updated_at;
    public $category;
    public $url;

    function __construct()
    {
        $this->created_at = time();
        $this->updated_at = time();
    }
}
