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
                    ],
                    '2' => [
                        'id' => '2',
                        'dict_id' => '1',
                        'q' => 'x',
                        'a' => 'y',
                        'touched' => 0,
                        'answers1' => 3,
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
                'windowSize' => 1000,
                'stats' => [
                    'transitions' => [],
                    'total' => 0,
                    'finished' => 0,
                    'inProgress' => 0
                ]
            ]
        ]);
    }

    function testUpdateDict()
    {
        // pre: app with data
        $app = $this->app();

        // action: edit the dictionary
        $app->updateDict('1', ['name' => 'foo', 'windowSize' => 10]);

        // post: dictionary renamed
        $dict = $app->getDict('1');
        $this->assertEquals($dict['name'], 'foo');
        $this->assertEquals($dict['windowSize'], 10);
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
        $app->markTouch('1', '1', true);
        $app->markTouch('1', '2', false);

        // post: counters updated correctly
        $e1 = $app->getEntry('1', '1');
        $this->assertEquals($e1['answers1'], 2);
        $this->assertEquals($e1['touched'], 1);
    }

    function testDelete()
    {
        // pre: app with entries
        $app = $this->app();
        // action: delete one entry
        $app->deleteEntry('1', '1');
        // post: entry deleted
        $ids = array_map(function ($e) {
            return $e['id'];
        }, $app->getEntries('1'));
        $this->assertEquals($ids, ['2']);
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
        $test = $app->generateTest($dictID, 10);

        // post: valid test.
        $this->assertEquals($test, [
            'tuples1' => [
                0 => [
                    'id' => $id,
                    'q' => 'q',
                    'a' => 'a',
                    'times' => 0,
                    'score' => 0,
                    'urls' => [],
                ],
            ],
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
                'windowSize' => 1000,
                'stats' => [
                    'transitions' => [],
                    'total' => 2,
                    'finished' => 0,
                    'inProgress' => 0,
                ],
            ],
        ]);
    }

    function testSheet()
    {
        // pre: app with a dict
        $app = $this->app();
        // action: get a sheet
        $r = $app->getSheet('1', 100);

        // post: sheet returned
        $this->assertEquals($r, [
            [
                'id' => '1',
                'q' => 'q',
                'a' => 'a',
                'score' => 1,
                'urls' => [],
            ],
            [
                'id' => '2',
                'q' => 'x',
                'a' => 'y',
                'score' => 3,
                'urls' => [],
            ]
        ]);
    }
}
