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

    $router
        ->add('get', '/api/', function () {
            send(response::json(getThe()->getDicts()));
        })
        ->add('post', '/api/{\d+}', function ($dict_id) {
            getThe()->updateDict($dict_id, request::json());
            send(response::make(200));
        })
        ->add('post', '/api/{\d+}/add', function ($dict_id) {
            send(response::json(getThe()->appendWords($dict_id, request::json()['entries'])));
        })
        ->add('get', '/api/{\d+}/test', function ($dict_id) {
            send(response::json(getThe()->generateTest($dict_id)));
        })
        ->add('post', '/api/{\d+}/test', function ($dict_id) {
            $results = getThe()->submitTest($dict_id, request::post('dir'), request::post('q'), request::post('a'));
            send(response::json($results));
        })
        ->add('get', '/api/entries/{\d+}', function ($id) {
            $e = getThe()->getEntry($id);
            if ($e) {
                send(response::json(['entry' => $e->format()]));
            } else {
                send(response::json(null));
            }
        })
        ->add('post', '/api/entries/{\d+}', function ($id) {
            getThe()->updateEntry($id, request::post('q'), request::post('a'));
            send(response::make('ok'));
        })
        ->add('post', '/api/touches/{\w+}', function ($id) {
            $body = request::json();
            getThe()->markTouch($id, $body['dir'], $body['success']);
            send(response::make('ok'));
        })
        ->add('get', '/', function () {
            send(response::make(file_get_contents(__DIR__ . '/../public/index.html')));
        });

    try {
        $router->dispatch();
    } catch (RouteNotFound $e) {
        error_log("route not found: " . request::url());
        send(response::make(404)->setContent("route not found: " . request::url()));
    } catch (Exception $e) {
        error_log($e->getMessage());
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
