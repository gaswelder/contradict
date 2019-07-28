<?php
require 'vendor/autoload.php';
require __DIR__ . '/routes.php';
require __DIR__ . '/App.php';

function registerClasses($dir)
{
    spl_autoload_register(function ($className) use ($dir) {
        $path = "$dir/$className.php";
        if (file_exists($path)) {
            require_once($path);
        }
    });
}

registerClasses(__DIR__ . '/storage');

function varfmt($var)
{
    ob_start();
    var_dump($var);
    $s = ob_get_clean();
    return $s;
}

function clg(...$var)
{
    foreach ($var as $i => $var) {
        error_log("$i ---> " . varfmt($var));
    }
}

function hint(Storage $s, Question $q)
{
    $entry = $q->entry();
    $sim = $s->similars($entry, $q->reverse);
    if (count($sim) == 0) {
        return null;
    }
    $field = $q->reverse ? 'q' : 'a';
    $values = [];
    foreach ($sim as $entry) {
        $values[] = $entry->$field;
    }
    $hint = h($q->entry()->$field, $values);
    return preg_replace('/\*+/', '...', $hint);
}

function h($word, $others)
{
    $list = array_unique(array_merge([$word], $others));
    if (count($list) < 2) return null;

    $first = array_map(function ($str) {
        return mb_substr($str, 0, 1);
    }, $list);

    if (count(array_unique($first)) == count($first)) {
        return $first[0] . (mb_strlen($word) > 1 ? '*' : '');
    }
    $rest = function ($str) {
        return mb_substr($str, 1);
    };
    $replace = $first[0] == ' ' ? ' ' : '*';
    return $replace . h($rest($word), array_map($rest, $others));
}

function checkAnswer(Question $q, $answer)
{
    $realAnswer = $q->reverse ? $q->entry()->q : $q->entry()->a;
    return mb_strtolower($realAnswer) == mb_strtolower($answer);
}

function dictStats(Storage $s, string $dict_id): Stats
{
    $entries = $s->allEntries($dict_id);

    $stats = new Stats;
    $stats->totalEntries = count($entries);

    foreach ($entries as $e) {
        $isfinished = $e->answers1 >= Storage::GOAL && $e->answers2 >= Storage::GOAL;
        if ($isfinished) {
            $stats->finished++;
            continue;
        }
        if ($e->touched) {
            $stats->touched++;
        }
    }

    $stats->successRate = successRate($s, $dict_id);
    return $stats;
}

function successRate(Storage $s, string $dict_id): float
{
    $scores = $s->lastScores($dict_id);
    $total = 0;
    $n = 0;
    foreach ($scores as $score) {
        $n++;
        $total += $score->right / ($score->right + $score->wrong);
    }
    return $n > 0 ? $total / $n : 1;
}

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

$storage = new SQLStorage(getenv('DATABASE'));
$theApp = new App($storage);
$app = makeWebRoutes($theApp);
$app->run();
