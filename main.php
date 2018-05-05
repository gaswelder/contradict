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

$app->get('/', function () {
    return tpl('home');
});

$app->get('/add', function () {
    return tpl('add');
});

$app->post('/add', function () {
    $lines = Arr::make(explode("\n", request::post('words')))
        ->map(function ($line) {
            return trim($line);
        })
        ->filter()
        ->map(function ($line) {
            return preg_split('/\s+-\s+/', $line, 2);
        });

    Dict::load()->append($lines->get());

    return response::redirect('/');
});

$app->get('/test', function () {
    $size = 20;
    $tuples1 = Entry::pick($size, 0);
    $tuples2 = Entry::pick($size, 1);
    return tpl('test', compact('tuples1', 'tuples2'));
});

$app->post('/test', function () {
    $A = request::post('a');
    $dir = request::post('dir');
    $dict = Dict::load();

    $entries = Entry::getMultiple(request::post('q'));

    $results = Arr::make($entries)->zip(request::post('dir'))
        ->map(function ($list) {
            list($entry, $dir) = $list;
            return new Question($entry, $dir == 1);
        })
        ->zip(request::post('a'))
        ->map(function ($list) {
            list($question, $answer) = $list;
            $correct = $question->checkAnswer($answer);
            if ($correct) $question->save();
            return compact('question', 'answer', 'correct');
        });

    $ok = $results->filter(function ($item) {
        return $item['correct'];
    })->get();
    $fail = $results->filter(function ($item) {
        return !$item['correct'];
    })->get();

    $stats = new TestResult();
    $stats->right = count($ok);
    $stats->wrong = count($fail);
    $stats->save();

    return tpl('results', compact('ok', 'fail'));
});

$app->get('/entries/{\d+}', function ($id) {
    $entry = Entry::get($id);
    return tpl('entry', compact('entry'));
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

$app->run();
