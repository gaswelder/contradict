<?php

function migrate($data)
{
    $v = $data['version'] ?? 0;
    if ($v == 0) {
        $v1 = [
            'version' => 1,
            'dicts' => [
                "" => [
                    "id" => "",
                    "name" => "(recovered)",
                ]
            ]
        ];
        foreach ($data['dicts'] as $dict) {
            $v1['dicts'][$dict['id']] = array_merge($dict, [
                'words' => [],
                'scores' => []
            ]);
        }
        foreach ($data['words'] as $id => $word) {
            $v1['dicts'][$word['dict_id']]['words'][$id] = $word;
        }
        foreach ($data['scores'] as $id => $score) {
            $v1['dicts'][$score['dict_id']]['scores'][$id] = $score;
        }
        return migrate($v1);
    }
    if ($v == 1) {
        return $data;
    }
    throw new Error("unexpected data version: $v");
}

function parseData($data)
{
    if (substr($data, 0, 1) != '{') {
        $data = gzuncompress($data);
    }
    $data = json_decode($data, true);
    return migrate($data);
}

class Dictionaries
{
    private $data = [];

    // Whether we have modified the data.
    private $touched = false;

    private $fs;

    function __construct(FileSystem $fs)
    {
        $this->fs = $fs;
        if ($this->fs->exists('')) {
            $this->data = parseData($this->fs->read(''));
            file_put_contents('dump.json', json_encode($this->data, JSON_PRETTY_PRINT));
        } else {
            $this->data = ['dicts' => []];
        }
    }

    function export()
    {
        return $this->data;
    }

    function import($data)
    {
        $this->data = $data;
        $this->touched = true;
    }

    function __destruct()
    {
        $this->flush();
    }

    /**
     * Saves recent data changes to the storage.
     */
    function flush()
    {
        if (!$this->touched) {
            return;
        }
        $this->fs->write('', gzcompress(json_encode($this->data)));
        $this->touched = false;
    }

    /**
     * Creates or updates a dict.
     */
    function saveDict(Dict $d)
    {
        $this->data['dicts'][$d->id] = array_merge($this->data['dicts'][$d->id], $d->format());
        $this->touched = true;
    }

    /**
     * Returns all saved dicts.
     */
    function dicts(): array
    {
        $dicts = [];
        foreach ($this->data['dicts'] as $row) {
            $dicts[] = Dict::parse($row);
        }
        return $dicts;
    }

    /**
     * Returns a saved dict with the given id.
     */
    function dict(string $id): Dict
    {
        return Dict::parse($this->data['dicts'][$id]);
    }
}
