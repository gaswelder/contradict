<?php
require 'vendor/autoload.php';

registerClasses(__DIR__);
registerClasses(__DIR__ . '/../classes');

use PHPUnit\Framework\TestCase;



class StorageTest extends TestCase
{
    function storages()
    {
        $blob = new Dictionaries(new TestReadonlyFS(json_encode([
            'version' => 1,
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
                            'answers1' => 0,
                            'answers2' => 0,
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
        ])));
        return [[$blob]];
    }

    /**
     * @dataProvider storages
     */
    function testStorage(Dictionaries $s)
    {
        $dicts = $s->dicts();
        $this->assertNotEmpty($dicts);
        foreach ($dicts as $dict) {
            $this->checkDict($s, $dict);
        }
    }

    /**
     * @dataProvider storages
     */
    function testScores(Dictionaries $s)
    {
        $dict = $s->dicts()[0];

        $scores1 = $dict->lastScores();

        // Add a new score
        $score = new Score;
        $score->right = rand();
        $score->wrong = rand();
        $score->dict_id = $dict->id;
        $dict->saveScore($score);

        $lastScores = $dict->lastScores();
        $f1 = $score->format();
        $f2 = $lastScores[0]->format();
        unset($f1['id']);
        unset($f2['id']);
        $this->assertEquals($f1, $f2, 'the new score should appear first in lastScores');

        $scores2 = $dict->lastScores();
        $this->assertEquals(count($scores1) + 1, count($scores2), 'the number of score records should increase by one');
    }

    /**
     * @dataProvider storages
     */
    function testEntries(Dictionaries $s)
    {
        $dict = $s->dicts()[0];

        $ee = $dict->allEntries();

        // Create a new entry and check that total count increased.
        $e = new Entry;
        $e->dict_id = $dict->id;
        $e->q = uniqid();
        $e->a = uniqid();

        $this->assertFalse($dict->hasEntry($e));

        $e = $dict->saveEntry($e);
        $this->assertCount(count($ee) + 1, $dict->allEntries());
        $this->assertTrue($dict->hasEntry($e));

        // Read the created entry and compare it with what we saved.
        $re = $dict->entry($e->id);
        $this->assertEquals($re, $e);

        // Update the array and compare.
        $re->q = 'qq';
        $re->a = 'aa';
        $dict->saveEntry($re);
        $this->assertEquals($re, $dict->entry($e->id));
    }

    private function checkDict(Dictionaries $s, Dict $dict)
    {
        $this->assertNotEmpty($dict->id);
        $this->assertNotEmpty($dict->name);

        $d = $s->dict($dict->id);
        $this->assertEquals($d->id, $dict->id);
        $this->assertEquals($d->name, $dict->name);

        $ee = $d->allEntries();
        $d->similars($ee[0], false);
    }
}
