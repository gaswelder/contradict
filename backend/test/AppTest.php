<?php

require 'vendor/autoload.php';

use PHPUnit\Framework\TestCase;

registerClasses('backend/classes');
registerClasses('backend');

class TestFS implements FileSystem
{
    function __construct(string $data)
    {
        $this->files = ['' => $data];
    }
    function exists(string $path): bool
    {
        return isset($this->files[$path]);
    }
    function write(string $path, string $data)
    {
        $this->files[$path] = $data;
    }
    function read(string $path): string
    {
        return $this->files[$path];
    }
}

class TestStorage extends Dictionaries
{
    function __construct($data)
    {
        parent::__construct(new TestFS(json_encode($data)));
    }
}

function testData()
{
    return [
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
                'answers1' => 1,
                'answers2' => 2,
            ],
            '2' => [
                'id' => '2',
                'dict_id' => '1',
                'q' => 'x',
                'a' => 'y',
                'touched' => 0,
                'answers1' => 3,
                'answers2' => 4,
            ]
        ],
        'scores' => [
            '1' => [
                'id' => '1',
                'dict_id' => '1',
                'right' => 1,
                'wrong' => 2
            ]
        ]
    ];
}

class AppTest extends TestCase
{
    function test()
    {
        $storage = new TestStorage(testData());

        $dict_id = '1';
        $answers = [
            Answer::parse(['entryID' => '1', 'answer' => 'a', 'reverse' => false]), // correct
            Answer::parse(['entryID' => '2', 'answer' => 'qq', 'reverse' => false]) // incorrect
        ];
        $app = new App;
        $app->setStorage($storage);
        $app->submitTest($dict_id, $answers);
        $first = reset(testData()['scores']);
        $this->assertEquals(1, $first['right']);
        $this->assertEquals(2, $first['wrong']);
        $this->assertEquals(1, $first['dict_id']);
    }

    function testImportExport()
    {
        $storage1 = new TestStorage(testData());
        $storage2 = new TestStorage(['dicts' => [], 'words' => [], 'scores' => []]);
        $this->assertNotEquals($storage1->export(), $storage2->export());

        $storage2->import($storage1->export());
        $this->assertEquals($storage1->export(), $storage2->export());
    }
}
