<?php

class Dict
{
    public $id;
    public $name;

    static function parse(array $arr): Dict
    {
        $d = new self;
        $d->id = $arr['id'];
        $d->name = $arr['name'];
        return $d;
    }

    function format(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
