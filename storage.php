<?php

class Storage
{
    function dicts(): array
    {
        return Dict::find([]);
    }

    function dict(string $id): Dict
    {
        return Dict::load($id);
    }

    function appendWords(string $dict_id, array $pairs): int
    {
        $dict = $this->dict($dict_id);
        return $dict->append($pairs);
    }

    /**
     * Generates a test for a given dictionary.
     */
    function test(string $dict_id): Test
    {
        $size = 20;
        $dict = $this->dict($dict_id);
        return new Test($dict->pick($size, 0), $dict->pick($size, 1));
    }

    function entry(string $id): Entry
    {
        return Entry::get($id);
    }

    function saveEntry(Entry $e)
    {
        $e->save();
    }

    function entries(array $ids): array
    {
        return Entry::getMultiple($ids);
    }
}
