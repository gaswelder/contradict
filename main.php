<?php
require 'vendor/autoload.php';
require __DIR__ . '/storage.php';
require __DIR__ . '/routes.php';

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

function checkAnswer(Question $q, $answer)
{
    $realAnswer = $q->reverse ? $q->entry()->q : $q->entry()->a;
    return mb_strtolower($realAnswer) == mb_strtolower($answer);
}

function verifyTest(string $dict_id, array $questions, array $answers): TestResults
{
    $results = [];
    foreach ($questions as $i => $question) {
        $answer = $answers[$i];
        $result = [
            'question' => $question->format(),
            'answer' => $answer,
            'correct' => checkAnswer($question, $answer)
        ];
        $results[] = $result;
    }

    // Update correct answer counters
    // For all questions that are correct, increment the corresponding counter (dir 0/1) and save.
    foreach ($questions as $i => $question) {
        $answer = $answers[$i];
        if (!checkAnswer($question, $answer)) {
            continue;
        }
        if ($question->reverse) {
            $question->entry()->answers2++;
        } else {
            $question->entry()->answers1++;
        }
        $question->entry()->save();
    }

    return new TestResults($dict_id, $results);
}

$storage = new Storage();
$app = makeWebRoutes($storage);
$app->run();
