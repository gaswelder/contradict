<?php

use havana\request;
use havana\response;

class Answer
{
    public $dir;
    public $q;
    public $a;
}

function stats() {
    return Dict::load()->stats();
}

$app->get('/dict', function() {
    return tpl('dict/home');
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
    $dict = Dict::load();

    $a = Arr::make(request::post('q'))
        ->map(function($q, $i) use ($A, $dir) {
            $a = new Answer;
            $a->dir = $dir[$i];
            $a->q = $q;
            $a->a = $A[$i];
            return $a;
        })
        ->map(function($answer) use ($dict) {
            return $dict->result($answer);
        });
    $dict->save();

    $ok = $a->filter(function(Result $item) {
        return $item->ok();
    })->get();

    $fail = $a->filter(function(Result $item) {
        return !$item->ok();
    })->get();

    return tpl('dict/results', compact('ok', 'fail'));
});

$app->get('/dict/entries/{\d+}', function($id) {
    $entry = Dict::load()->entry($id);
    return tpl('dict/entry', compact('entry'));
});

$app->post('/dict/entries/{\d+}', function($id) {
    $dict = Dict::load();
    $entry = $dict->entry($id);
    $entry->q = request::post('q');
    $entry->a = request::post('a');
    $dict->save();
    return response::redirect('/dict/entries/'.$id);
});
