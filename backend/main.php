<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    gaswelder\Env::parse(__DIR__ . '/.env');
}

use gaswelder\MethodNotAllowed;
use gaswelder\request;
use gaswelder\response;
use gaswelder\RouteNotFound;
use gaswelder\router;

class Unauthorized extends Exception {}

class Auth
{
    static function login(string $name, string $password): string
    {
        $db = json_decode(file_get_contents(getDataDir() . 'users.json'), true);
        $rec = $db[$name] ?? null;
        if (!$rec || !password_verify($password, $rec['hash'])) {
            return '';
        }
        $userID = $rec['id'];
        $token = bin2hex(random_bytes(20));
        $tokenPath = getDataDir() . "token-$token.json";
        file_put_contents($tokenPath, json_encode(['userID' => $userID]));
        return $token;
    }

    static function checkToken(string $token): string
    {
        if (preg_match('/[^\w]/', $token)) {
            return '';
        }
        $tokenPath = getDataDir() . "token-$token.json";
        if (!file_exists($tokenPath)) {
            return '';
        }
        $data = json_decode(file_get_contents($tokenPath), true);
        return $data['userID'];
    }
}

function getDataDir()
{
    return getenv("DATABASE_DIR") ?? __DIR__ . "/../";
}

function getThe()
{
    $dir = getDataDir();
    $token = $_COOKIE['token'] ?? '';
    $userID = Auth::checkToken($token);
    if (!$userID) {
        throw new Unauthorized;
    }
    return new Contradict($dir . "$userID.json.gz");
}

$router = router::make()
    ->add('post', '/api/login', function () {
        $name = request::post('login');
        $password = request::post('password');
        $token = Auth::login($name, $password);
        if ($token) {
            setcookie('token', $token, time() + 3600 * 24 * 30);
            return response::status(201);
        } else {
            return response::status(403)->setContent('text/plain', 'Invalid login/password');
        }
    })
    ->add('post', '/api/logout', function () {
        setcookie('token', '');
        return response::status(200);
    })
    ->add('get', '/api/', function () {
        return response::json(getThe()->getDicts());
    })
    ->add('post', '/api/', function () {
        getThe()->addDict(request::json()['name']);
        return response::status(200);
    })
    ->add('post', '/api/{\w+}', function ($dict_id) {
        getThe()->updateDict($dict_id, request::json());
        return response::status(200);
    })
    ->add('post', '/api/{\w+}/add', function ($dict_id) {
        return response::json(getThe()->appendWords($dict_id, request::json()['entries']));
    })
    ->add('get', '/api/{\w+}/test', function ($dict_id) {
        $size = intval(request::param("size", "20"));
        if (!$size) {
            $size = 20;
        }
        return response::json(getThe()->generateTest($dict_id, $size));
    })
    ->add('get', '/api/{\w+}/sheet', function ($dict_id) {
        $size = intval(request::param("size", "20"));
        if (!$size) {
            $size = 100;
        }
        return response::json(getThe()->getSheet($dict_id, $size));
    })
    ->add('get', '/api/entries/{\w+}/{\w+}', function ($dictID, $id) {
        $e = getThe()->getEntry($dictID, $id);
        if (!$e) {
            return response::json(null);
        }
        return response::json(['entry' => $e]);
    })
    ->add('post', '/api/entries/{\w+}/{\w+}', function ($dictID, $id) {
        getThe()->updateEntry($dictID, $id, request::post('q'), request::post('a'));
        return response::status(200);
    })
    ->add('post', '/api/touch/{\w+}/{\w+}', function ($dictID, $entryID) {
        $body = request::json();
        getThe()->markTouch($dictID, $entryID, $body['success']);
        return response::status(200);
    })
    ->add('post', '/api/delete/{\w+}/{\w+}', function ($dictID, $entryID) {
        getThe()->deleteEntry($dictID, $entryID);
        return response::status(200);
    })
    ->add('get', '/', function () {
        return response::staticFile('text/html', __DIR__ . '/../public/index.html');
    });


function send($r)
{
    $r->setHeader('Access-Control-Allow-Origin', 'http://localhost:1234');
    $r->setHeader('Access-Control-Allow-Credentials', 'true');
    $r->setHeader('Access-Control-Allow-Headers', 'Content-Type');
    $r->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
    $r->flush();
}
try {
    send($router->run());
} catch (MethodNotAllowed $e) {
    if (request::method() == "OPTIONS") {
        $r = new response;
        $r->setHeader('Access-Control-Allow-Origin', 'http://localhost:1234');
        $r->setHeader('Access-Control-Allow-Credentials', 'true');
        $r->setHeader('Access-Control-Allow-Headers', 'Content-Type');
        $r->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
        $r->flush();
    } else {
        throw $e;
    }
} catch (RouteNotFound $e) {
    if (request::method() == "GET") {
        send(response::staticFile('text/html', __DIR__ . '/../public/index.html'));
    } else {
        error_log("route not found: " . request::url());
        send(response::status(404)->setContent('text/html', "route not found: " . request::url()));
    }
} catch (Unauthorized $e) {
    error_log("unauthorized");
    send(response::status(response::STATUS_UNAUTHORIZED));
} catch (Exception $e) {
    error_log(get_class($e) . ': ' . $e->getMessage());
    send(response::status(500));
}


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
