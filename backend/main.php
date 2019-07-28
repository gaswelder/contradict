<?php
require 'vendor/autoload.php';
require __DIR__ . '/routes.php';
require __DIR__ . '/App.php';

function registerClasses($dir)
{
    spl_autoload_register(function ($className) use ($dir) {
        $path = "$dir/$className.php";
        if (file_exists($path)) {
            require_once($path);
        }
    });
}

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

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

$storage = new SQLStorage(getenv('DATABASE'));
$theApp = new App($storage);
$app = makeWebRoutes($theApp);
$app->run();
