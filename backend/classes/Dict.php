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

    function saveEntry(Entry $e): Entry
    {
        if (!$e->id) {
            $e->id = uniqid();
        }
        $this->data['words'][$e->id] = [
            'q' => $e->q,
            'a' => $e->a,
            'answers1' => $e->answers1,
            'answers2' => $e->answers2,
            'id' => $e->id,
            'dict_id' => $e->dict_id,
            'touched' => $e->touched ? 1 : 0,
        ];
        return $e;
    }
}
