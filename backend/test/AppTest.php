<?php

require 'vendor/autoload.php';
require __DIR__ . '/../classes/__load.php';

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
        $dict_id = '1';
        $answers = [
            Answer::parse(['entryID' => '1', 'answer' => 'a', 'reverse' => false]), // correct
            Answer::parse(['entryID' => '2', 'answer' => 'qq', 'reverse' => false]) // incorrect
        ];
        $app = new Contradict("test");
        $app->storage->import(testData());
        $app->submitTest($dict_id, $answers);
        $first = reset(testData()['scores']);
        $this->assertEquals(1, $first['right']);
        $this->assertEquals(2, $first['wrong']);
        $this->assertEquals(1, $first['dict_id']);
    }

    function testImportExport()
    {
        $app = new Contradict("test");
        $app->storage->import(testData());
        $this->assertEquals($app->storage->export(), testData());
    }
}
