<?php
use havana\dbobject;

class Entry extends dbobject
{
    const TABLE_NAME = 'words';

    public $q;
    public $a;
    public $answers1 = 0;
    public $answers2 = 0;
    public $id;
    public $dict_id;

    static function stats()
    {
        $goal = Dict::GOAL;
        $r = self::db()->getRow('select count(*) as n, sum(answers1+answers2) as ok from words');
        $n = $r['n'];
        $ok = $r['ok'];

        $finished = self::db()->getValue("select sum(a1 + a2) from (select answers1 >= $goal as a1, answers2 >= $goal as a2 from words) a");
        $started = self::db()->getValue("select count(*) from words where answers1 + answers2 between 1 and 2 * $goal - 1");
        return [
            'pairs' => $n,
            'progress' => $ok / $goal / $n / 2,
            'finished' => $finished / 2,
            'started' => $started
        ];
    }

    /**
     * Returns a given number of random questions.
     *
     * @param int $dict_id Identifier of the dictionary to get questions from
     * @param int $n Number of questions
     * @param int $dir Translation direction: 0 for direct, 1 for reverse
     * @return array
     */
    static function pick($dict_id, $n, $dir)
    {
        $correctAnswers = $dir == 0 ? 'answers1' : 'answers2';
        $n = intval($n);
        $goal = Dict::GOAL;
        $windowSize = Dict::WINDOW;

        $rows = self::db()->getRows("
            select * from 
                (select * from words
                    where dict_id = ?
                    and $correctAnswers < $goal
                    order by touched desc, id
                    limit $windowSize) a
            order by random()
            limit $n", $dict_id);

        $entries = self::fromRows($rows);

        $ids = array_map(function (Entry $e) {
            return $e->id;
        }, $entries);

        $set = '(' . implode(', ', $ids) . ')';
        self::db()->exec("update words set touched = 1 where id in $set");

        return array_map(function (Entry $e) use ($dir) {
            return new Question($e, $dir == 1);
        }, self::fromRows($rows));
    }

    function toRow()
    {
        return [$this->q, $this->a, $this->answers1, $this->answers2];
    }
}
