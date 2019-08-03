<?php
require 'vendor/autoload.php';

registerClasses(__DIR__);
registerClasses(__DIR__ . '/../classes');

use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    function storages()
    {
        $sql = new SQLStorage(__DIR__ . '/../dict.sqlite');
        $blob = new BlobStorage(function () {
            return json_encode([
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
            ]);
        }, function ($data) {
            //
        });
        return [[$sql], [$blob]];
    }

    /**
     * @dataProvider storages
     */
    function testStorage(Storage $s)
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
    function testScores(Storage $s)
    {
        $dict = $s->dicts()[0];

        $scores1 = $s->scores();

        // Add a new score
        $score = new Score;
        $score->right = rand();
        $score->wrong = rand();
        $score->dict_id = $dict->id;
        $s->saveScore($score);

        $lastScores = $s->lastScores($dict->id);
        $f1 = $score->format();
        $f2 = $lastScores[0]->format();
        unset($f1['id']);
        unset($f2['id']);
        $this->assertEquals($f1, $f2, 'the new score should appear first in lastScores');

        $scores2 = $s->scores();
        $this->assertEquals(count($scores1) + 1, count($scores2), 'the number of score records should increase by one');
        // $this->assertEquals($score->right, $sc->right);
        // $this->assertEquals($score->wrong, $sc->wrong);
    }

    private function checkDict(Storage $s, Dict $dict)
    {
        $this->assertNotEmpty($dict->id);
        $this->assertNotEmpty($dict->name);

        $d = $s->dict($dict->id);
        $this->assertEquals($d->id, $dict->id);
        $this->assertEquals($d->name, $dict->name);

        $ee = $s->allEntries($dict->id);
        $this->assertNotEmpty($ee);

        $s->similars($ee[0], false);

        $entry = $ee[0];
        $e = $s->entry($entry->id);
        $this->assertInstanceOf(Entry::class, $entry);

        $ee = $s->entries([$entry->id]);
        $this->assertCount(1, $ee);

        $s->saveEntry($e);
    }
}
