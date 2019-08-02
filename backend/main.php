<?php
require __DIR__ . '/../vendor/autoload.php';
registerClasses(__DIR__ . '/storage');
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

Appget\Env::parse(__DIR__ . '/.env');

$theApp = new App();
$makeStorage = function () {
    $path = getenv('DATABASE');
    return new SQLStorage(__DIR__ . '/' . $path);
};
$app = makeWebRoutes($theApp, $makeStorage);
$app->run();
