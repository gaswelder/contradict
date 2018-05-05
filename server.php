<?php

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

$uri = $_SERVER['REQUEST_URI'];
$path = 'public'.$uri;
$pos = strpos($path, '?');
if ($pos) {
	$path = substr($path, 0, $pos);
}

if (file_exists($path) && !is_dir($path)) {
	$mime = mime($path);
	header('Content-Type: '.$mime);
	readfile($path);
	return true;
}

chdir('public');

require '../main.php';
return true;

?>
