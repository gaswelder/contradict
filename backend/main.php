<?php
require __DIR__ . '/../vendor/autoload.php';
registerClasses(__DIR__);

// require '/home/gas/code/pub/havana/main.php';
require __DIR__ . '/routes.php';
require __DIR__ . '/App.php';

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
    Appget\Env::parse(__DIR__ . '/.env');
}

function getStorageMaker()
{
    if (getenv('CLOUDCUBE_URL')) {
        error_log("using s3 storage");
        return function ($userID) {
            $s3 = new CloudCube();
            return new Storage(function () use ($s3, $userID) {
                if (!$s3->exists($userID)) {
                    return null;
                }
                return $s3->read($userID);
            }, function ($data) use ($s3, $userID) {
                $s3->write($userID, $data);
            });
        };
    } else {
        error_log("using local storage");
        return function ($userID) {
            $path = __DIR__ . "/database-$userID.json";
            return new Storage(function () use ($path) {
                if (!file_exists($path)) {
                    return null;
                }
                return file_get_contents($path);
            }, function ($data) use ($path) {
                file_put_contents($path, $data);
            });
        };
    }
}

$theApp = new App();

$app = makeWebRoutes($theApp, getStorageMaker());
$app->run();
