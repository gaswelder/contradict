<?php

use havana\App;
use havana\user;
use havana\request;
use havana\response;

/**
 * Parses words posted as text file,
 * returns array of [word, translation] pairs.
 */
function parseLines(string $str): array
{
    $lines = Arr::make(explode("\n", $str))
        ->map(function ($line) {
            return trim($line);
        })
        ->filter()
        ->map(function ($line) {
            return preg_split('/\s+-\s+/', $line, 2);
        });
    return $lines->get();
}

function makeWebRoutes(Storage $storage)
{
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

    $app->post('/api/login', function () {
        $pass = request::post('password');
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

    /**
     * Returns the list of dicts.
     */
    $app->get('/api/', function () use ($storage) {
        $r = ['dicts' => []];
        foreach ($storage->dicts() as $dict) {
            $r['dicts'][] = array_merge(
                $dict->format(),
                [
                    'stats' => $storage->dictStats($dict->id)->format()
                ]
            );
        }
        return $r;
    });

    /**
     * Adds words to a dictionary.
     */
    $app->post('/api/{\d+}/add', function ($dict_id) use ($storage) {
        $lines = parseLines(request::post('words'));
        $n = $storage->appendWords($dict_id, $lines);
        return [
            'n' => $n
        ];
    });

    /**
     * Returns a new test.
     */
    $app->get('/api/{\d+}/test', function ($dict_id) use ($storage) {
        $test = $storage->test($dict_id);
        return $test->format();
    });

    /**
     * Parses test answers and returns results.
     */
    $app->post('/api/{\d+}/test', function ($dict_id) use ($storage) {
        $answers = request::post('a');
        $directions = request::post('dir');
        $entries = $storage->entries(request::post('q'));

        $questions = [];
        foreach ($entries as $i => $entry) {
            $dir = $directions[$i];
            $questions[] = new Question($entry, $dir == 1);
        }

        $testresults = verifyTest($dict_id, $questions, $answers);
        return $testresults->format();
    });

    /**
     * Returns a single entry by ID.
     */
    $app->get('/api/entries/{\d+}', function ($id) use ($storage) {
        $entry = $storage->entry($id);;
        return ['entry' => $entry->format()];
    });

    /**
     * Updates an entry.
     */
    $app->post('/api/entries/{\d+}', function ($id) use ($storage) {
        $entry = $storage->entry($id);
        $entry->q = request::post('q');
        $entry->a = request::post('a');
        $storage->saveEntry($entry);
        return 'ok';
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

    return $app;
}
