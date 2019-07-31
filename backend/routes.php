<?php

use Appget\App;
use havana\request;
use havana\response;

class Auth
{
    function login(string $name, string $password): string
    {
        if ($password == '123') {
            return 'token';
        }
        return '';
    }

    function checkToken(string $token): string
    {
        if ($token == 'token') {
            return 'me';
        }
        return '';
    }
}

function makeWebRoutes(\App $the)
{
    $app = new App(__DIR__);
    $auth = new Auth;

    $app->middleware(function ($next) use ($auth) {
        // Require auth for /api/*.
        if (!request::url()->isUnder('/api')) {
            return $next();
        }

        if (request::url()->path == '/api/login') {
            // Allow only posting to login.
            if (request::method() !== 'post') {
                return 405;
            }
            $password = request::post('password');
            $token = $auth->login('name', $password);
            if ($token) {
                setcookie('token', $token, time() + 3600 * 24);
                return 201;
            } else {
                return response::make('Invalid login/password')->setStatus(403);
            }
        }

        $token = $_COOKIE['token'] ?? '';
        if (!$auth->checkToken($token)) {
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

    /**
     * Returns the list of dicts.
     */
    $app->get('/api/', function () use ($the) {
        $r = ['dicts' => []];
        foreach ($the->dicts() as $dict) {
            $d = $dict->format();
            $d['stats'] = $the->dictStats($dict->id)->format();
            $r['dicts'][] = $d;
        }
        return $r;
    });

    /**
     * Adds words to a dictionary.
     */
    $app->post('/api/{\d+}/add', function ($dict_id) use ($the) {
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
        return $the->appendWords($dict_id, $entries);
    });

    /**
     * Returns a new test.
     */
    $app->get('/api/{\d+}/test', function ($dict_id) use ($the) {
        $test = $the->generateTest($dict_id);
        $hints1 = [];
        $hints2 = [];
        foreach ($test->tuples1 as $q) {
            $hints1[] = $the->hint($q);
        }
        foreach ($test->tuples2 as $q) {
            $hints2[] = $the->hint($q);
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
    $app->post('/api/{\d+}/test', function ($dict_id) use ($the) {
        $directions = request::post('dir');
        $ids = request::post('q');
        $answers = [];
        foreach (request::post('a') as $i => $answer) {
            $a = new Answer;
            $a->answer = $answer;
            $a->entryID = $ids[$i];
            $a->reverse = $directions[$i] == 1;
            $answers[] = $a;
        }

        $results = $the->verifyTest($dict_id, $answers);
        return $results->format();
    });

    /**
     * Returns a single entry by ID.
     */
    $app->get('/api/entries/{\d+}', function ($id) use ($the) {
        return [
            'entry' => $the->entry($id)->format()
        ];
    });

    /**
     * Updates an entry.
     */
    $app->post('/api/entries/{\d+}', function ($id) use ($the) {
        $entry = new Entry;
        $entry->id = $id;
        $entry->q = request::post('q');
        $entry->a = request::post('a');
        $the->updateEntry($entry);
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
