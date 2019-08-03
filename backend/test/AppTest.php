<?php

require 'vendor/autoload.php';

use PHPUnit\Framework\TestCase;

registerClasses('backend/classes');
registerClasses('backend/storage');
registerClasses('backend');

class TestBlobStorage extends BlobStorage
{
    public $data = [];
    function __construct($data)
    {
        $this->data = $data;
        parent::__construct(function () {
            return json_encode($this->data);
        }, function ($newData) {
            $this->data = json_decode($newData, true);
        });
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
        $storage = new TestBlobStorage(testData());

        $dict_id = '1';
        $answers = [
            Answer::parse(['entryID' => '1', 'answer' => 'a', 'reverse' => false]), // correct
            Answer::parse(['entryID' => '2', 'answer' => 'qq', 'reverse' => false]) // incorrect
        ];
        $app = new App;
        $app->setStorage($storage);
        $app->verifyTest($dict_id, $answers);
        $first = reset($storage->data['scores']);
        $this->assertEquals(1, $first['right']);
        $this->assertEquals(2, $first['wrong']);
        $this->assertEquals(1, $first['dict_id']);
    }

    function testImportExport()
    {
        $storage1 = new TestBlobStorage(testData());
        $app1 = new App;
        $app1->setStorage($storage1);
        $dump = $app1->export();

        $storage2 = new TestBlobStorage(['dicts' => [], 'words' => [], 'scores' => []]);
        $app2 = new App;
        $app2->setStorage($storage2);
        $app2->import($dump);

        $this->assertEquals($storage1->data, $storage2->data);
        $this->assertEquals($storage2->data, testData());
    }
}
