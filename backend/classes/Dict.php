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

    static function parse(array $arr): Dict
    {
        return new self($arr['id'], $arr['name']);
    }

    function format(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
