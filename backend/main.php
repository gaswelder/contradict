<?php
require '../vendor/autoload.php';
require __DIR__ . '/routes.php';
require __DIR__ . '/App.php';

registerClasses(__DIR__ . '/storage');

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

Appget\Env::parse(__DIR__ . '/.env');
$path = getenv('DATABASE');

$storage = new SQLStorage(__DIR__ . '/' . $path);
$theApp = new App($storage);
$app = makeWebRoutes($theApp);
$app->run();
