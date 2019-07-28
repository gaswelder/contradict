<?php

class Dict
{
    public $id;
    public $name;

    function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    function format(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
