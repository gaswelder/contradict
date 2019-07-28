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

class StorageTest extends TestCase
{
    function test()
    {
        $s = new SQLStorage('sqlite://dict.sqlite');
        $this->checkStorage($s);
    }

    function checkStorage(Storage $s)
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

        $t = $s->test($dict->id);
        $this->assertInstanceOf(Test::class, $t);

        $q = $t->tuples1[0];
        $s->similars($q);

        $entry = $q->entry();
        $e = $s->entry($entry->id);
        $this->assertInstanceOf(Entry::class, $entry);

        $ee = $s->entries([$entry->id]);
        $this->assertCount(1, $ee);

        $s->saveEntry($e);
    }
}
