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


function makeS3Storage($userID)
{
    $s3 = new CloudCube();
    return new BlobStorage(function () use ($s3, $userID) {
        if (!$s3->exists($userID)) {
            return null;
        }
        return $s3->read($userID);
    }, function ($data) use ($s3, $userID) {
        $s3->write($userID, $data);
    });
}

function makeLocalStorage($userID)
{
    $path = getenv('DATABASE');
    return new SQLStorage(__DIR__ . '/' . $path);
}

if (getenv('CLOUDCUBE_URL')) {
    error_log("using s3 storage");
    $makeStorage = 'makeS3Storage';
} else {
    error_log("using local storage");
    $makeStorage = 'makeLocalStorage';
}

$theApp = new App();
// $makeStorage = function ($userID) {
//     $path = __DIR__ . "/database-$userID.json";
//     return new BlobStorage(function () use ($path) {
//         if (!file_exists($path)) {
//             return null;
//         }
//         return file_get_contents($path);
//     }, function ($data) use ($path) {
//         file_put_contents($path, $data);
//     });
// };
$app = makeWebRoutes($theApp, $makeStorage);
$app->run();
