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
        $app->import(testData());

        // post: app has 1 dict with 2 entries
        $dicts = $app->dicts();
        $this->assertEquals(count($dicts), 1);
        $this->assertEquals(count($dicts[0]->allEntries()), 2);
    }

    function testExport()
    {
        // pre: app with data
        $app = $this->app();

        // action: export
        $exported = $app->export();

        // post: exported data matches the source data
        $this->assertEquals($exported, testData());
    }

    private function app(): Contradict
    {
        $app = new Contradict("test");
        $app->import(testData());
        return $app;
    }

    function testUpdateDict()
    {
        // pre: app with data
        $app = $this->app();

        // action: rename the dictionary
        $app->updateDict('1', ['name' => 'foo']);

        // post: dictionary renamed
        $this->assertEquals($app->getDict('1')->name, 'foo');
    }

    function testUpdateEntry()
    {
        // pre: app with data
        $app = $this->app();

        // action: update entry 2
        $app->updateEntry('2', 'qqq', 'aaa');

        // post: entry updated
        $e = $app->getEntry('2');
        $this->assertEquals('qqq', $e->q);
        $this->assertEquals('aaa', $e->a);
    }

    function testTouch()
    {
        // pre: app with entries
        $app = $this->app();

        // action: touch two entries
        $app->markTouch('1', 0, true);
        $app->markTouch('2', 1, false);

        // post: counters updated correctly
        $e1 = $app->getEntry('1');
        $e2 = $app->getEntry('2');
        $this->assertEquals($e1->touched, true);
        $this->assertEquals($e1->answers1, 2);
        $this->assertEquals($e2->touched, true);
        $this->assertEquals($e2->answers2, 3);
    }

    function testGetEntry()
    {
        // pre: app with entries
        $app = $this->app();

        // action: get entry 1
        $e = $app->getEntry('2');

        // post: success
        $this->assertEquals('2', $e->id);
    }

    function test()
    {
        // pre: app with some words
        $app = $this->app();

        // action: submit answers, one correct, one incorrect.
        $result = $app->submitTest('1', [0, 0], ['1', '2'], ['a', 'qq']);

        // post: result contains 2 results, 1 correct
        $this->assertEquals($result, [
            'dict_id' => '1',
            'results' => [
                [
                    'answer' => 'a',
                    'question' => [
                        'id' => '1',
                        'q' => 'q',
                        'a' => 'a',
                        'times' => 2,
                        'wikiURL' => NULL,
                        'dir' => 0,
                    ],
                    'correct' => true
                ],
                [
                    'answer' => 'qq',
                    'question' => [
                        'id' => '2',
                        'q' => 'x',
                        'a' => 'y',
                        'times' => 3,
                        'wikiURL' => NULL,
                        'dir' => 0,
                    ],
                    'correct' => false
                ]
            ]
        ]);
    }
}
