<?php
require __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($cn) {
    $path = __DIR__ . "/classes/$cn.php";
    if (file_exists($path)) {
        require_once $path;
    }
});

function varfmt($var)
{
    ob_start();
    var_dump($var);
    $s = ob_get_clean();
    return $s;
}

function clg(...$var)
{
    foreach ($var as $i => $var) {
        error_log("$i ---> " . varfmt($var));
    }
}

if (file_exists(__DIR__ . '/.env')) {
    Env::parse(__DIR__ . '/.env');
}

function getThe()
{
    $auth = new CookieAuth(getenv('COOKIE_KEY'));
    $token = $_COOKIE['token'] ?? '';
    $userID = $auth->checkToken($token);
    if (!$userID) {
        throw response::make(response::STATUS_UNAUTHORIZED);
    }
    return new Contradict($userID);
}

function send($r)
{
    $r->setHeader('Access-Control-Allow-Origin', 'http://localhost:1234');
    $r->setHeader('Access-Control-Allow-Credentials', 'true');
    $r->setHeader('Access-Control-Allow-Headers', 'Content-Type');
    $r->flush();
}

function main()
{
    $router = new router();

    if (request::method() == "OPTIONS") {
        $r = new response;
        $r->setHeader('Access-Control-Allow-Origin', 'http://localhost:1234');
        $r->setHeader('Access-Control-Allow-Credentials', 'true');
        $r->setHeader('Access-Control-Allow-Headers', 'Content-Type');
        $r->flush();
        return;
    }

    $router->add('post', '/api/login', function () {
        $name = request::post('login');
        $password = request::post('password');
        $auth = new CookieAuth(getenv('COOKIE_KEY'));
        $token = $auth->login($name, $password);
        if ($token) {
            setcookie('token', $token, time() + 3600 * 24);
            send(response::make(201));
        } else {
            send(response::make('Invalid login/password')->setStatus(403));
        }
    });

    $router->add('post', '/api/logout', function () {
        setcookie('token', '');
        send(response::make(200));
    });

    $router->add('get', '/api/', function () {
        $the = getThe();
        $list = [];
        foreach ($the->dicts() as $dict) {
            $d = $dict->format();
            $d['stats'] = $the->dictStats($dict->id)->format();
            $list[] = $d;
        }
        send(response::json($list));
    });

    /**
     * Updates a dictionary.
     */
    $router->add('post', '/api/{\d+}', function ($dict_id) {
        $data = json_decode(request::body(), true);
        try {
            getThe()->updateDict($dict_id, $data);
        } catch (DictNotFound $e) {
            send(response::make(404));
            return;
        }
        send(response::make(200));
    });

    /**
     * Adds words to a dictionary.
     */
    $router->add('post', '/api/{\d+}/add', function ($dict_id) {
        $the = getThe();
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
        send(response::json($the->appendWords($dict_id, $entries)));
    });

    $router->add('get', '/api/{\d+}/test', function ($dict_id) {
        $the = getThe();
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
        send(response::json($f));
    });

    /**
     * Parses test answers and returns results.
     */
    $router->add('post', '/api/{\d+}/test', function ($dict_id) {
        $the = getThe();
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
        send(response::json($results->format()));
    });

    /**
     * Returns a single entry by ID.
     */
    $router->add('get', '/api/entries/{\d+}', function ($id) {
        $e = getThe()->getEntry($id);
        if ($e) {
            send(response::json(['entry' => $e->format()]));
        } else {
            send(response::json(null));
        }
    });

    /**
     * Updates an entry.
     */
    $router->add('post', '/api/entries/{\d+}', function ($id) {
        getThe()->updateEntry($id, request::post('q'), request::post('a'));
        send(response::make('ok'));
    });

    $router->add('post', '/api/touches/{\d+}', function ($id) {
        $body = json_decode(request::body(), true);
        getThe()->markTouch($id, $body['dir'], $body['success']);
        send(response::make('ok'));
    });

    try {
        $router->dispatch();
    } catch (RouteNotFound $e) {
        send(response::make(404));
    } catch (Exception $e) {
        send(response::make(500));
    }
}

main();




// /**
//  * Exports a database.
//  */
// $app->get('/api/export', function () use (&$storage) {
//     return $storage->export();
// });

// /**
//  * Imports a database.
//  */
// $app->post('/api/export', function () use ($storage) {
//     $data = json_decode(request::body(), true);
//     $storage->import($data);
// });

// $app->get('/backup', function () {
//     return response::staticFile(__DIR__ . '/dict.sqlite')->downloadAs('dict.sqlite');
// });

// $app->get('/', function () {
//     return file_get_contents(__DIR__ . '/../public/index.html');
// });
