<?php
require 'vendor/autoload.php';

registerClasses(__DIR__);
registerClasses(__DIR__ . '/../classes');

use PHPUnit\Framework\TestCase;

function export(Storage $s)
{
    $data = [
        'dicts' => [],
        'entries' => [],
        'scores' => []
    ];
    foreach ($s->dicts() as $dict) {
        $data['dicts'][] = $dict->format();
        foreach ($s->allEntries($dict->id) as $e) {
            $data['entries'][] = $e->format();
        }
    }
    return $data;
}

function import(Storage $s, $data)
{
    foreach ($data['dicts'] as $row) {
        $d = Dict::parse($row);
        $s->saveDict($d);
    }
    foreach ($data['entries'] as $row) {
        $e = Entry::parse($row);
        $s->saveEntry($e);
    }
}

class StorageTest extends TestCase
{
    function storages()
    {
        $sql = new SQLStorage(__DIR__ . '/../dict.sqlite');
        $blob = new BlobStorage(function () {
            return json_encode([
                'dicts' => [
                    '1' => [
                        'id' => '1',
                        'name' => 'Sample dict'
                    ]
                ],
                'words' => [
                    '1' => [
                        'id' => '1',
                        'dict_id' => '1',
                        'q' => 'q',
                        'a' => 'a',
                        'touched' => 0,
                        'answers1' => 0,
                        'answers2' => 0,
                    ]
                ],
                'scores' => []
            ]);
        }, function ($data) {
            //
        });
        return [[$sql], [$blob]];
    }

    /**
     * @dataProvider storages
     */
    function testStorage(Storage $s)
    {
        $dicts = $s->dicts();
        $this->assertNotEmpty($dicts);
        foreach ($dicts as $dict) {
            $this->checkDict($s, $dict);
        }
    }

    private function checkDict(Storage $s, Dict $dict)
    {
        $this->assertNotEmpty($dict->id);
        $this->assertNotEmpty($dict->name);

        $d = $s->dict($dict->id);
        $this->assertEquals($d->id, $dict->id);
        $this->assertEquals($d->name, $dict->name);

        $scores1 = $s->lastScores($dict->id);
        $score = new Score;
        $score->right = rand();
        $score->wrong = rand();
        $score->dict_id = $dict->id;
        $s->saveScore($score);
        $scores2 = $s->lastScores($dict->id);
        $sc = $scores2[0];
        $this->assertEquals($score->right, $sc->right);
        $this->assertEquals($score->wrong, $sc->wrong);

        $ee = $s->allEntries($dict->id);
        $this->assertNotEmpty($ee);

        $s->similars($ee[0], false);

        $entry = $ee[0];
        $e = $s->entry($entry->id);
        $this->assertInstanceOf(Entry::class, $entry);

        $ee = $s->entries([$entry->id]);
        $this->assertCount(1, $ee);

        $s->saveEntry($e);
    }

    function testImport()
    {
        $sql = new SQLStorage(__DIR__ . '/../dict.sqlite');
        $blob = new BlobStorage(function () {
            return json_encode([
                'dicts' => [],
                'words' => [],
                'scores' => [],
            ]);
        }, function ($data) {
            // var_dump($data);
            // $this->assertArrayHasKey('dicts', $data);
            // $this->assertArrayHasKey('words', $data);
        });
        $data = export($sql);
        import($blob, $data);
    }
}
