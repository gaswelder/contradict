<?php
require 'vendor/autoload.php';
require_once 'classes/Dict.php';
require __DIR__ . '/storage.php';

use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    function test()
    {
        $storage = new Storage();
        $dicts = $storage->dicts();
        $this->assertNotEmpty($dicts);
    }
}
