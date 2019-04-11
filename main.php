<?php
require 'vendor/autoload.php';

use havana\App;
use havana\user;
use havana\request;
use havana\response;

$app = new App(__DIR__);

$app->middleware(function ($next) {
    if (!user::getRole('user') && request::url()->isUnder('/api') && request::url()->path != '/api/login') {
        return 401;
    }
    return $next();
});

$app->middleware((function ($next) {
    $r = $next();
    $r->setHeader('Access-Control-Allow-Origin', 'http://localhost:1234');
    $r->setHeader('Access-Control-Allow-Credentials', 'true');
    return $r;
}));

// $app->get('/api/login', function () {
//     return tpl('login');
// });

$app->post('/api/login', function () {
    $pass = request::post('password');
    error_log($pass);
    if ($pass == '123') {
        user::addRole('user');
        return 'ok';
    }
    return response::make(tpl('login'))->setStatus(403);
});

$app->post('/api/logout', function () {
    user::removeRole('user');
    return 'ok';
});

// $app->get('/api/logout', function () {
//     user::removeRole('user');
//     return response::redirect('/api/');
// });

function format($tplName, $data)
{
    $data = json_decode(json_encode($data), true);
    return $data;
    // return tpl($tplName, $data);
}

$app->get('/api/', function () {
    $dicts = array_map(function (Dict $dict) {
        return [
            'id' => $dict->id,
            'name' => $dict->name,
            'stats' => $dict->stats()
        ];
    }, Dict::find([]));
    return format('home', compact('dicts'));
});

// $app->get('/api/{\d+}/add', function ($dict_id) {
//     return tpl('add', compact('dict_id'));
// });

$app->post('/api/{\d+}/add', function ($dict_id) {
    $dict = Dict::load($dict_id);

    $lines = Arr::make(explode("\n", request::post('words')))
        ->map(function ($line) {
            return trim($line);
        })
        ->filter()
        ->map(function ($line) {
            return preg_split('/\s+-\s+/', $line, 2);
        });

    $n = $dict->append($lines->get());
    return [
        'n' => $n
    ];
});

$app->get('/api/{\d+}/test', function ($dict_id) {
    $ft = function ($tuples) {
        return array_map(function (Question $tuple) {
            return $tuple->format();
        }, $tuples);
    };
    $size = 20;
    $dict = Dict::load($dict_id);
    $tuples1 = $ft($dict->pick($size, 0));
    $tuples2 = $ft($dict->pick($size, 1));
    return format('test', compact('tuples1', 'tuples2'));
});

function verifyTest($questions, $answers)
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
    return $results;
}

function parseQuestions()
{
    $entries = Entry::getMultiple(request::post('q'));
    $directions = request::post('dir');
    $questions = [];
    foreach ($entries as $i => $entry) {
        $dir = $directions[$i];
        $questions[] = new Question($entry, $dir == 1);
    }
    return $questions;
}

$app->post('/api/{\d+}/test', function ($dict_id) {
    $answers = request::post('a');
    $questions = parseQuestions();

    $results = verifyTest($questions, $answers);

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

    return format('results', compact('ok', 'fail', 'dict_id', 'stats'));
});

$app->get('/api/entries/{\d+}', function ($id) {
    $entry = Entry::get($id);
    return format('entry', compact('entry'));
});

$app->post('/api/entries/{\d+}', function ($id) {
    $entry = Entry::get($id);
    $entry->q = request::post('q');
    $entry->a = request::post('a');
    $entry->save();
    return 'ok';
    // return response::redirect('/api/entries/' . $id);
});

$app->get('/api/stats', function () {
    $results = TestResult::find([], 't desc');
    return tpl('stats', compact('results'));
});

$app->get('/backup', function () {
    return response::staticFile(__DIR__ . '/dict.sqlite')->downloadAs('dict.sqlite');
});

$app->get('*', function () {
    return file_get_contents(__DIR__ . '/public/index.html');
});

$app->run();
