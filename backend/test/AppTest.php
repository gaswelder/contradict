<?php

require 'vendor/autoload.php';

use PHPUnit\Framework\TestCase;

registerClasses('backend/classes');
registerClasses('backend/storage');
registerClasses('backend');

class AppTest extends TestCase
{
    function test()
    {
        $data = [
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
            'scores' => []
        ];
        $storage = new BlobStorage(function () use (&$data) {
            return json_encode($data);
        }, function ($newData) use (&$data) {
            $data = json_decode($newData, true);
        });

        $dict_id = '1';
        $answers = [
            Answer::parse(['entryID' => '1', 'answer' => 'a', 'reverse' => false]), // correct
            Answer::parse(['entryID' => '2', 'answer' => 'qq', 'reverse' => false]) // incorrect
        ];
        $app = new App;
        $app->setStorage($storage);
        $app->verifyTest($dict_id, $answers);
        $this->assertArraySubset([
            'right' => 1,
            'wrong' => 1,
            'dict_id' => '1'
        ], reset($data['scores']));
    }
}
