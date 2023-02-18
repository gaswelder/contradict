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

    function format(): array
    {
        return array_merge($this->data, [
            'id' => $this->id,
            'name' => $this->name,
            'lookupURLTemplates' => $this->lookupURLTemplates
        ]);
    }

    function wikiURLs(string $entryText): array
    {
        $words = explode(' ', $entryText);
        if (empty($words)) {
            return [];
        }
        if (in_array(strtolower($words[0]), ['das', 'die', 'der', 'to'])) {
            array_shift($words);
        }
        if (count($words) != 1) {
            return [];
        }
        $word = $words[0];
        return array_map(function ($template) use ($word) {
            return str_replace('{{word}}', urlencode($word), $template);
        }, $this->lookupURLTemplates);
    }

    function allEntries(): array
    {
        $entries = [];
        foreach ($this->data['words'] as $row) {
            $entries[] = Entry::parse($row);
        }
        return $entries;
    }

    function hasEntry(Entry $e): bool
    {
        foreach ($this->allEntries() as $entry) {
            if ($entry->q == $e->q && $entry->a == $e->a) {
                return true;
            }
        }
        return false;
    }

    function saveEntry(Entry $e): Entry
    {
        if (!$e->id) {
            $e->id = uniqid();
        }
        $this->data['words'][$e->id] = $e->format();
        return $e;
    }

    function entry(string $id): ?Entry
    {
        $e = $this->data['words'][$id] ?? null;
        if (!$e) {
            return $e;
        }
        return Entry::parse($this->data['words'][$id]);
    }

    function entries(array $ids): array
    {
        $entries = [];
        foreach ($ids as $id) {
            $entries[] = $this->entry($id);
        }
        return $entries;
    }

    function similars(Entry $e, bool $reverse): array
    {
        $ee = $this->allEntries();
        $sim = [];
        foreach ($ee as $entry) {
            if ($entry->id == $e->id) continue;
            if ($reverse && $entry->a != $e->a) continue;
            if (!$reverse && $entry->q != $e->q) continue;
            $sim[] = $entry;
        }
        return $sim;
    }
}
