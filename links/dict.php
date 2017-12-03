<?php

use havana\request;
use havana\response;

$app->get('/dict', function() {
    $d = Dict::load();
    $stats = $d->stats();
    return tpl('dict/home', compact('stats'));
});

$app->get('/dict/add', function() {
    return tpl('dict/add');
});

$app->post('/dict/add', function() {
    $lines = Arr::make(explode("\n", request::post('words')))
        ->map('trim')
        ->filter()
        ->map(function($line) {
            return preg_split('/\s+-\s+/', $line, 2);
        });
    
    Dict::load() -> append($lines->get()) -> save();

    return response::redirect('/dict');
});

$app->get('/dict/test', function() {
    $d = Dict::load();
    $tuples1 = $d->pick(10, 0);
    $tuples2 = $d->pick(10, 1);
    return tpl('dict/test', compact('tuples1', 'tuples2'));
});

$app->post('/dict/test', function() {
    $Q = request::post('q');
    $A = request::post('a');
    $dir = request::post('dir');

    $d = Dict::load();

    $ok = [];
    $fail = [];
    foreach ($Q as $i => $q) {
        $a = $A[$i];
        $result = $d->check($q, $a, $dir[$i]);
        if ($result['ok']) {
            $ok[] = $result;
        } else {
            $fail[] = $result;
        }
    }
    $d->save();
    return tpl('dict/results', compact('ok', 'fail'));
});
