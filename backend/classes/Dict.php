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

    function wikiURL(string $entryText)
    {
        if (!$this->lookupURLTemplate) {
            return null;
        }
        $words = explode(' ', $entryText);
        if (empty($words)) {
            return null;
        }
        if (in_array(strtolower($words[0]), ['das', 'die', 'der'])) {
            array_shift($words);
        }
        if (count($words) != 1) {
            return null;
        }

        $wiki = $words[0];
        return str_replace('{{word}}', urlencode($wiki), $this->lookupURLTemplate);
    }
}
