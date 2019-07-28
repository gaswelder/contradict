<?php

use havana\App;
use havana\user;
use havana\request;
use havana\response;

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
            $d = $dict->format();
            $d['stats'] = $storage->dictStats($dict->id)->format();
            $d['stats']['successRate'] = successRate($storage, $dict->id);
            $r['dicts'][] = $d;
        }
        return $r;
    });

    /**
     * Adds words to a dictionary.
     */
    $app->post('/api/{\d+}/add', function ($dict_id) use ($storage) {
        // Parse words posted as text file
        // to array of [word, translation] pairs.
        $str = request::post('words');
        $lines = array_map('trim', explode("\n", $str));
        $lines = array_filter($lines, 'strlen');
        $lines = array_map(function ($line) {
            return preg_split('/\s+-\s+/', $line, 2);
        }, $lines);

        $entries = [];
        foreach ($lines as $tuple) {
            $entry = new Entry;
            $entry->dict_id = $dict_id;
            $entry->q = $tuple[0];
            $entry->a = $tuple[1];
            $entries[] = $entry;
        }
        $n = appendWords($storage, $dict_id, $entries);
        return [
            'n' => $n
        ];
    });

    /**
     * Returns a new test.
     */
    $app->get('/api/{\d+}/test', function ($dict_id) use ($storage) {
        $test = $storage->test($dict_id);

        $hints1 = [];
        $hints2 = [];
        foreach ($test->tuples1 as $q) {
            $hints1[] = hint($storage, $q);
        }
        foreach ($test->tuples2 as $q) {
            $hints2[] = hint($storage, $q);
        }

        $f = $test->format();
        foreach ($f['tuples1'] as $k => $tuple) {
            $f['tuples1'][$k]['hint'] = $hints1[$k];
        }
        foreach ($f['tuples2'] as $k => $tuple) {
            $f['tuples2'][$k]['hint'] = $hints2[$k];
        }
        return $f;
    });

    /**
     * Parses test answers and returns results.
     */
    $app->post('/api/{\d+}/test', function ($dict_id) use ($storage) {
        $answers = request::post('a');
        $directions = request::post('dir');
        $entries = $storage->entries(request::post('q'));

        $qa = [];
        foreach ($entries as $i => $entry) {
            $dir = $directions[$i];
            $qa[] = [new Question($entry, $dir == 1), $answers[$i]];
        }

        $testresults = verifyTest($dict_id, $qa, $storage);
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

    $app->get('/backup', function () {
        return response::staticFile(__DIR__ . '/dict.sqlite')->downloadAs('dict.sqlite');
    });

    $app->get('*', function () {
        return file_get_contents(__DIR__ . '/public/index.html');
    });

    return $app;
}
