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
            $fs = new S3($s3, $userID);
            return new Storage($fs);
        };
    } else {
        error_log("using local storage");
        return function ($userID) {
            $fs = new LocalFS(__DIR__ . "/database-$userID.json");
            return new Storage($fs);
        };
    }
}

$theApp = new App();

$app = makeWebRoutes($theApp, getStorageMaker());
$app->run();
