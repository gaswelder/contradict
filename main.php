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
    $correct = $q->checkAnswer($answer);
    if ($correct) {
        $q->save();
    }
    return $correct;
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
    return new TestResults($dict_id, $results);
}

$storage = new Storage();
$app = makeWebRoutes($storage);
$app->run();
