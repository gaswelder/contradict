<?php

require 'vendor/autoload.php';

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
            ]
        ],
    ];
}

class AppTest extends TestCase
{
    function testImport()
    {
        // pre: empty app
        $app = new Contradict("testdata");

        // action: import data
        $app->import(testData());

        // post: app has 1 dict with 2 entries
        $dicts = $app->getDicts();
        $this->assertEquals(count($dicts), 1);
        $entries = $app->getEntries($dicts[0]['id']);
        $this->assertEquals(count($entries), 2);
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
        $app = new Contradict("testdata");
        $app->import(testData());
        return $app;
    }

    function testCreateDict()
    {
        // pre: empty app
        $app = new Contradict(uniqid('testdata'));

        // action: add a dict
        $id = $app->addDict('foo');

        // post: dict added
        $this->assertEquals($app->getDicts(), [
            [
                'id' => $id,
                'name' => 'foo',
                'lookupURLTemplates' => [],
                'stats' => [
                    'transitions' => [],
                    'pairs' => 0,
                    'finished' => 0,
                    'touched' => 0
                ]
            ]
        ]);
    }

    function testUpdateDict()
    {
        // pre: app with data
        $app = $this->app();

        // action: rename the dictionary
        $app->updateDict('1', ['name' => 'foo']);

        // post: dictionary renamed
        $this->assertEquals($app->getDict('1')['name'], 'foo');
    }

    function testUpdateEntry()
    {
        // pre: app with data
        $app = $this->app();

        // action: update entry 2
        $app->updateEntry('1', '2', 'qqq', 'aaa');

        // post: entry updated
        $e = $app->getEntry('1', '2');
        $this->assertEquals('qqq', $e['q']);
        $this->assertEquals('aaa', $e['a']);
    }

    function testTouch()
    {
        // pre: app with entries
        $app = $this->app();

        // action: touch two entries
        $app->markTouch('1', '1', 0, true);
        $app->markTouch('1', '2', 1, false);

        // post: counters updated correctly
        $e1 = $app->getEntry('1', '1');
        $e2 = $app->getEntry('1', '2');
        $this->assertEquals($e1['answers1'], 2);
        $this->assertEquals($e2['answers2'], 3);
    }

    function testGetEntry()
    {
        // pre: app with entries
        $app = $this->app();

        // action: get entry 1
        $e = $app->getEntry('1', '2');

        // post: success
        $this->assertEquals('2', $e['id']);
    }

    function testGenerate()
    {
        // pre: app with one entry
        $app = new Contradict(uniqid("testdata"));
        $dictID = $app->addDict('dict');
        $r = $app->appendWords($dictID, [['q', 'a']]);
        $id = $r['ids'][0];

        // action: generate a test
        $test = $app->generateTest($dictID);

        // post: valid test, entry marked as touched.
        $this->assertEquals($test, array(
            'tuples1' =>
            array(
                0 =>
                array(
                    'id' => $id,
                    'q' => 'q',
                    'a' => 'a',
                    'times' => 0,
                    'score' => 0,
                    'urls' => [],
                    'reverse' => false,
                ),
            ),
            'tuples2' =>
            array(
                0 =>
                array(
                    'id' => $id,
                    'q' => 'a',
                    'a' => 'q',
                    'times' => 0,
                    'score' => 0,
                    'urls' => [],
                    'reverse' => true,
                ),
            ),
        ));
        $this->assertEquals(1, $app->getEntry($dictID, $id)['touched']);
        $app->generateTest($dictID);
        $this->assertEquals(2, $app->getEntry($dictID, $id)['touched']);
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
                        'urls' => [],
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
                        'urls' => [],
                        'dir' => 0,
                    ],
                    'correct' => false
                ]
            ]
        ]);
    }

    function testDicts()
    {
        // pre: app with some dicts
        $app = $this->app();

        // action: get the list
        $r = $app->getDicts();

        // post: correct list
        $this->assertEquals($r, [
            0 =>
            [
                'id' => '1',
                'name' => 'Sample dict',
                'lookupURLTemplates' => [],
                'stats' => [
                    'transitions' => [],
                    'pairs' => 2.0,
                    'finished' => 0,
                    'touched' => 0.0,
                ],
            ],
        ]);
    }
}
