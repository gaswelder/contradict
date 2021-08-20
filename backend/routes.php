<?php

use Appget\App;
use havana\request;
use havana\response;

function makeAuthMiddleware(Auth $auth, $urlPrefix, $loginURL, $logoutURL, $onAuth)
{
    return function ($next) use ($auth, $urlPrefix, $loginURL, $logoutURL, $onAuth) {
        if (!request::url()->isUnder($urlPrefix)) {
            return $next();
        }

        if (request::method() == 'OPTIONS') {
            return $next();
        }

        if (request::url()->path == $loginURL) {
            // Allow only posting to login.
            if (request::method() !== 'POST') {
                return 405;
            }
            $name = request::post('login');
            $password = request::post('password');
            $token = $auth->login($name, $password);
            if ($token) {
                setcookie('token', $token, time() + 3600 * 24);
                return 201;
            } else {
                return response::make('Invalid login/password')->setStatus(403);
            }
        }

        if (request::url()->path == $logoutURL) {
            // Allow only posting to logout.
            if (request::method() !== 'POST') {
                return 405;
            }
            setcookie('token', '');
            return 200;
        }

        $token = $_COOKIE['token'] ?? '';
        $userID = $auth->checkToken($token);
        if (!$userID) {
            return 401;
        }
        $onAuth($userID);
        return $next();
    };
}

function makeWebRoutes(\App $the, $makeStorage)
{
    $app = new App(__DIR__);
    $auth = new CookieAuth(getenv('COOKIE_KEY'));

    /**
     * @var Dictionaries
     */
    $storage = null;
    $onAuth = function ($userID) use ($the, $makeStorage, &$storage) {
        $storage = $makeStorage($userID);
        $the->setStorage($storage);
    };

    $app->middleware(makeAuthMiddleware($auth, '/api', '/api/login', '/api/logout', $onAuth));

    $app->middleware((function ($next) {
        if (request::method() != 'OPTIONS') {
            $r = $next();
        } else {
            $r = new response;
        }
        $r->setHeader('Access-Control-Allow-Origin', 'http://localhost:1234');
        $r->setHeader('Access-Control-Allow-Credentials', 'true');
        $r->setHeader('Access-Control-Allow-Headers', 'Content-Type');
        return $r;
    }));

    /**
     * Returns the list of dicts.
     */
    $app->get('/api/', function () use ($the) {
        $r = [];
        foreach ($the->dicts() as $dict) {
            $d = $dict->format();
            $d['stats'] = $the->dictStats($dict->id)->format();
            $r[] = $d;
        }
        return $r;
    });

    /**
     * Updates a dictionary.
     */
    $app->post('/api/{\d+}', function ($dict_id) use (&$storage) {
        $dict = $storage->dict($dict_id);
        if (!$dict) {
            return 404;
        }
        $data = json_decode(request::body(), true);
        $dict->name = $data['name'] ?? $dict->name;
        $dict->lookupURLTemplate = $data['lookupURLTemplate'] ?? $dict->lookupURLTemplate;
        $storage->saveDict($dict);
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
            $hints1[] = $q->hint();
        }
        foreach ($test->tuples2 as $q) {
            $hints2[] = $q->hint();
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

        $results = $the->submitTest($dict_id, $answers);
        return $results->format();
    });

    /**
     * Returns a single entry by ID.
     */
    $app->get('/api/entries/{\d+}', function ($id) use (&$storage) {
        $dd = $storage->dicts();
        $e = null;
        foreach ($dd as $d) {
            $e = $d->entry($id);
            if ($e) {
                break;
            }
        }
        if ($e) {
            return [
                'entry' => $e->format()
            ];
        }
        return null;
    });

    /**
     * Updates an entry.
     */
    $app->post('/api/entries/{\d+}', function ($id) use (&$storage) {
        // Find the dictionary
        $dict_id = '';
        foreach ($storage->dicts() as $d) {
            $e = $d->entry($id);
            if ($e) {
                $dict_id = $d->id;
                break;
            }
        }
        $dict = $storage->dict($dict_id);

        // Save the entry.
        $entry = new Entry;
        $entry->id = $id;
        $entry->q = request::post('q');
        $entry->a = request::post('a');
        $dict->saveEntry($entry);
        $storage->saveDict($dict);
        return 'ok';
    });

    /**
     * Exports a database.
     */
    $app->get('/api/export', function () use ($storage) {
        return $storage->export();
    });

    /**
     * Imports a database.
     */
    $app->post('/api/export', function () use ($storage) {
        $data = json_decode(request::body(), true);
        $storage->import($data);
    });

    $app->get('/backup', function () {
        return response::staticFile(__DIR__ . '/dict.sqlite')->downloadAs('dict.sqlite');
    });

    $app->get('/', function () {
        return file_get_contents(__DIR__ . '/../public/index.html');
    });

    $app->cmd('genkey', function () {
        echo CookieAuth::generateKey(), "\n";
    });

    return $app;
}
