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


function verifyTest(string $dict_id, array $questions, array $answers): TestResults
{
    $results = [];

    foreach ($questions as $i => $question) {
        $answer = $answers[$i];
        $correct = $question->checkAnswer($answer);
        if ($correct) {
            $question->save();
        }

        $result = [
            'question' => $question->format(),
            'answer' => $answer,
            'correct' => $correct
        ];
        $results[] = $result;
    }

    $ok = [];
    $fail = [];
    foreach ($results as $result) {
        if ($result['correct']) {
            $ok[] = $result;
        } else {
            $fail[] = $result;
        }
    }

    $stats = new TestResult();
    $stats->dict_id = $dict_id;
    $stats->right = count($ok);
    $stats->wrong = count($fail);
    $stats->save();

    return new TestResults($dict_id, $stats, $results);
}

$storage = new Storage();
$app = makeWebRoutes($storage);
$app->run();
