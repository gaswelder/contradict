<?php

spl_autoload_register(function ($cn) {
    $path = str_replace('//', '/', __DIR__ . "/classes/" . str_replace('\\', '//', $cn) . ".php");
    if (file_exists($path)) {
        require_once $path;
    }
});
