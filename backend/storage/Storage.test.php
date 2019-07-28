<?php
require 'vendor/autoload.php';

function registerClasses($dir)
{
    spl_autoload_register(function ($className) use ($dir) {
        $path = "$dir/$className.php";
        if (file_exists($path)) {
            require_once($path);
        }
    });
}

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
        $sql = new SQLStorage('sqlite://dict.sqlite');
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

        $stats = $s->dictStats($dict->id);
        $this->assertInstanceOf(Stats::class, $stats);
        $this->assertGreaterThan(0, $stats->totalEntries, 'total entries');

        $scores = $s->lastScores($dict->id);

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
        $sql = new SQLStorage('sqlite://dict.sqlite');
        $blob = new BlobStorage(function () {
            return json_encode([
                'dicts' => [],
                'words' => [],
            ]);
        }, function ($data) {
            var_dump($data);
            // $this->assertArrayHasKey('dicts', $data);
            // $this->assertArrayHasKey('words', $data);
        });
        $data = export($sql);
        import($blob, $data);
    }
}
