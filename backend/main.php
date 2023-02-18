<?php
require __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($cn) {
    $path = __DIR__ . "/classes/$cn.php";
    if (file_exists($path)) {
        require_once $path;
    }
});

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

$router = router::make()
    ->add('post', '/api/login', function () {
        $name = request::post('login');
        $password = request::post('password');
        $auth = new CookieAuth(getenv('COOKIE_KEY'));
        $token = $auth->login($name, $password);
        if ($token) {
            setcookie('token', $token, time() + 3600 * 24);
            return response::make(201);
        } else {
            return response::make('Invalid login/password')->setStatus(403);
        }
    })
    ->add('post', '/api/logout', function () {
        setcookie('token', '');
        return response::make(200);
    })
    ->add('get', '/api/', function () {
        return response::json(getThe()->getDicts());
    })
    ->add('post', '/api/{\d+}', function ($dict_id) {
        getThe()->updateDict($dict_id, request::json());
        return response::make(200);
    })
    ->add('post', '/api/{\d+}/add', function ($dict_id) {
        return response::json(getThe()->appendWords($dict_id, request::json()['entries']));
    })
    ->add('get', '/api/{\d+}/test', function ($dict_id) {
        return response::json(getThe()->generateTest($dict_id));
    })
    ->add('post', '/api/{\d+}/test', function ($dict_id) {
        $results = getThe()->submitTest($dict_id, request::post('dir'), request::post('q'), request::post('a'));
        return response::json($results);
    })
    ->add('get', '/api/entries/{\d+}', function ($id) {
        $e = getThe()->getEntry($id);
        if ($e) {
            return response::json(['entry' => $e->format()]);
        } else {
            return response::json(null);
        }
    })
    ->add('post', '/api/entries/{\d+}', function ($id) {
        getThe()->updateEntry($id, request::post('q'), request::post('a'));
        return response::make('ok');
    })
    ->add('post', '/api/touches/{\w+}', function ($id) {
        $body = request::json();
        getThe()->markTouch($id, $body['dir'], $body['success']);
        return response::make('ok');
    })
    ->add('get', '/', function () {
        return response::make(file_get_contents(__DIR__ . '/../public/index.html'));
    });


function send($r)
{
    $r->setHeader('Access-Control-Allow-Origin', 'http://localhost:1234');
    $r->setHeader('Access-Control-Allow-Credentials', 'true');
    $r->setHeader('Access-Control-Allow-Headers', 'Content-Type');
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
        $r->flush();
    } else {
        throw $e;
    }
} catch (RouteNotFound $e) {
    error_log("route not found: " . request::url());
    send(response::make(404)->setContent("route not found: " . request::url()));
} catch (Exception $e) {
    error_log(get_class($e) . ': ' . $e->getMessage());
    send(response::make(500));
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
