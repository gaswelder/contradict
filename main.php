<?php
require 'vendor/autoload.php';

use havana\App;
use havana\user;
use havana\request;
use havana\response;

$app = new App(__DIR__);

$app->middleware(function ($next) {
    if (!user::getRole('user') && request::url()->path != '/login') {
        return response::redirect('/login');
    }
    return $next();
});

$app->middleware(function ($next) {
    if (request::get('token') == 'bed04814f428bf40ef0e') {
        user::addRole('user');
    }
    return $next();
});

$app->middleware((function ($next) {
    $r = $next();
    $r->setHeader('Access-Control-Allow-Origin', '*');
    return $r;
}));

$app->get('/login', function () {
    return tpl('login');
});

$app->post('/login', function () {
    $pass = request::post('password');
    if ($pass == '123') {
        user::addRole('user');
        return response::redirect('/');
    }
    return tpl('login');
});

$app->post('/logout', function () {
    user::removeRole('user');
    return response::redirect('/');
});

$app->get('/logout', function () {
    user::removeRole('user');
    return response::redirect('/');
});

function format($tplName, $data)
{
    $data = json_decode(json_encode($data), true);
    return tpl($tplName, $data);
    //return $data;
}

$app->get('/', function () {
    $dicts = array_map(function (Dict $dict) {
        return [
            'id' => $dict->id,
            'name' => $dict->name,
            'stats' => $dict->stats()
        ];
    }, Dict::find([]));
    return format('home', compact('dicts'));
});

$app->get('/{\d+}/add', function ($dict_id) {
    return tpl('add', compact('dict_id'));
});

$app->post('/{\d+}/add', function ($dict_id) {
    $dict = Dict::load($dict_id);

    $lines = Arr::make(explode("\n", request::post('words')))
        ->map(function ($line) {
            return trim($line);
        })
        ->filter()
        ->map(function ($line) {
            return preg_split('/\s+-\s+/', $line, 2);
        });

    $dict->append($lines->get());
    return response::redirect('/');
});

$app->get('/{\d+}/test', function ($dict_id) {
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

$app->post('/{\d+}/test', function ($dict_id) {
    $A = request::post('a');
    $dir = request::post('dir');
    $dict = Dict::load($dict_id);

    $entries = Entry::getMultiple(request::post('q'));

    $results = Arr::make($entries)->zip(request::post('dir'))
        ->map(function ($list) {
            list($entry, $dir) = $list;
            return new Question($entry, $dir == 1);
        })
        ->zip(request::post('a'))
        ->map(function ($list) {
            list($questionObj, $answer) = $list;
            $correct = $questionObj->checkAnswer($answer);
            if ($correct) $questionObj->save();
            $question = $questionObj->format();
            $question['a'] = $questionObj->a();
            $question['wikiURL'] = $questionObj->wikiURL();
            return compact('question', 'answer', 'correct');
        });

    $ok = $results->filter(function ($item) {
        return $item['correct'];
    })->get();
    $fail = $results->filter(function ($item) {
        return !$item['correct'];
    })->get();

    $stats = new TestResult();
    $stats->dict_id = $dict_id;
    $stats->right = count($ok);
    $stats->wrong = count($fail);
    $stats->save();

    return format('results', compact('ok', 'fail', 'dict_id', 'stats'));
});

$app->get('/entries/{\d+}', function ($id) {
    $entry = Entry::get($id);
    return format('entry', compact('entry'));
});

$app->post('/entries/{\d+}', function ($id) {
    $entry = Entry::get($id);
    $entry->q = request::post('q');
    $entry->a = request::post('a');
    $entry->save();
    return response::redirect('/entries/' . $id);
});

$app->get('/stats', function () {
    $results = TestResult::find([], 't desc');
    return tpl('stats', compact('results'));
});

$app->get('/backup', function () {
    return response::staticFile(__DIR__ . '/dict.sqlite')->downloadAs('dict.sqlite');
});

$app->run();
