<?php

class Dict
{
    public $id;
    public $name;
    public $lookupURLTemplate;

    static function parse(array $arr): Dict
    {
        $d = new self;
        $d->id = $arr['id'];
        $d->name = $arr['name'];
        $d->lookupURLTemplate = $arr['lookupURLTemplate'] ?? '';
        return $d;
    }

    function format(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lookupURLTemplate' => $this->lookupURLTemplate
        ];
    }
}
