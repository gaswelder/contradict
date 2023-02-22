<?php

class Dict
{
    public $id;
    public $name;
    public $lookupURLTemplates;
    public $data = ['words' => []];

    static function parse(array $arr): Dict
    {
        $d = new self;
        $d->data = $arr;
        $d->id = $arr['id'];
        $d->name = $arr['name'];
        $d->lookupURLTemplates = $arr['lookupURLTemplates'] ?? [];
        return $d;
    }
}
