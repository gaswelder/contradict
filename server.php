<?php

$uri = $_SERVER['REQUEST_URI'];
$paths = array_slice(explode('/', $uri), 1);

// First pathname is a project name
$project = array_shift($paths);
$path = implode('/', $paths);
if (!is_dir($project)) {
	return false;
}
chdir($project.'/public');

// Send to <project>/public/<path>
// or to <project>/public/index.php
if (file_exists($path)) {
	$mime = mime($path);
	header('Content-Type: '.$mime);
	readfile($path);
	return true;
}

function mime($path)
{
	$types = [
		'.css' => 'text/css',
		'.js' => 'text/javascript',
		'.png' => 'image/png',
		'.jpg' => 'image/jpeg',
		'.jpeg' => 'image/jpeg',
	];
	foreach ($types as $ext => $type) {
		$n = strlen($ext);
		if (substr($path, -$n) == $ext) {
			return $type;
		}
	}
	return mime_content_type($path);
}

require 'index.php';
return true;

?>
