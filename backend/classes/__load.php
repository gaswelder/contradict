<?php

spl_autoload_register(function ($cn) {
    $path = __DIR__ . "/$cn.php";
    if (file_exists($path)) {
        require_once $path;
    }
});
