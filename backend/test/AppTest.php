<?php

require 'vendor/autoload.php';
require __DIR__ . '/../classes/__load.php';

function testData()
{
    return [
        'dicts' => [
            '1' => [
                'id' => '1',
                'name' => 'Sample dict',
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
            ]
        ],
    ];
}

class AppTest extends TestCase
{
    function testImport()
    {
        // pre: empty app
        $app = new Contradict("test");

        // action: import data
        $app->storage->import(testData());

        // post: app has 1 dict with 2 entries
        $dicts = $app->dicts();
        $this->assertEquals(count($dicts), 1);
        $this->assertEquals(count($dicts[0]->allEntries()), 2);
    }

    function testExport()
    {
        // pre: app with data
        $app = new Contradict("test");
        $app->storage->import(testData());

        // action: export
        $exported = $app->storage->export();

        // post: exported data matches the source data
        $this->assertEquals($exported, testData());
    }

    // function test()
    // {
    //     // pre: app with some words
    //     $app = new Contradict("test");
    //     $app->storage->import(testData());

    //     // action: submit answers
    //     $result = $app->submitTest('1', [
    //         Answer::parse(['entryID' => '1', 'answer' => 'a', 'reverse' => false]), // correct
    //         Answer::parse(['entryID' => '2', 'answer' => 'qq', 'reverse' => false]) // incorrect
    //     ]);

    //     // post: result contains 2 results, 1 correct
    //     $this->assertEquals($result->dict_id, '1');
    //     $this->assertEquals($result->correct, 1);
    // }


}
