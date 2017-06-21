<?php


$uri = $_SERVER['REQUEST_URI'];

$path = 'public'.$uri;

if(file_exists($path)) {
	return false;
}

$dirs = array_slice(explode('/', $path), 0, 2);

$dir = implode('/', $dirs);
$entry = $dir.'/index.php';
if(file_exists($entry)) {
	chdir($dir);
	require $entry;
	return true;
}

return false;


?>
