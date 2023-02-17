<?php
require __DIR__ . '/../vendor/autoload.php';
registerClasses(__DIR__ . '/classes');
registerClasses(__DIR__);

// require '/home/gas/code/pub/havana/main.php';
require __DIR__ . '/routes.php';

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

$theApp = new App();
makeWebRoutes($theApp)->run();
