<?php
use havana\request;
use havana\response;

$app->get('/dict', function () {
    return tpl('dict/home');
});

$app->get('/dict/add', function () {
    return tpl('dict/add');
});

$app->post('/dict/add', function () {
    $lines = Arr::make(explode("\n", request::post('words')))
        ->map(function ($line) {
            return trim($line);
        })
        ->filter()
        ->map(function ($line) {
            return preg_split('/\s+-\s+/', $line, 2);
        });

    Dict::load()->append($lines->get());

    return response::redirect('/dict');
});

$app->get('/dict/test', function () {
    $size = 20;
    $tuples1 = Entry::pick($size, 0);
    $tuples2 = Entry::pick($size, 1);
    return tpl('dict/test', compact('tuples1', 'tuples2'));
});

$app->post('/dict/test', function () {
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

    return tpl('dict/results', compact('ok', 'fail'));
});

$app->get('/dict/entries/{\d+}', function ($id) {
    $entry = Entry::get($id);
    return tpl('dict/entry', compact('entry'));
});

$app->post('/dict/entries/{\d+}', function ($id) {
    $entry = Entry::get($id);
    $entry->q = request::post('q');
    $entry->a = request::post('a');
    $entry->save();
    return response::redirect('/dict/entries/' . $id);
});

$app->get('/dict/stats', function () {
    $results = TestResult::find([], 't desc');
    return tpl('dict/stats', compact('results'));
});
