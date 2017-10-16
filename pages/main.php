<?php

require __DIR__.'/../hl/main.php';

use havana\App;
use havana\user;
use havana\response;
use havana\request;

class Page
{
	public $name;
	public $content;

	function __construct($name)
	{
		$this->name = $name;

		$path = $this->path();
		if (file_exists($path)) {
			$this->content = file_get_contents($path);
		}
	}

	function save()
	{
		$path = $this->path();
		if (file_exists($path)) {
			$dir = dirname($path) . '/.backup';
			if (!file_exists($dir)) mkdir($dir);
			copy($path, $dir.'/'.date('Y-m-d-H-i-s').'_'.$this->name);
		}
		file_put_contents($path, $this->content);
	}

	private function path()
	{
		$dir = __DIR__.'/data';
		$path = $dir.'/'.$this->name;
		return $path;

	}

	static function ls()
	{
		$dir = __DIR__.'/data';
		return array_map('basename', glob($dir .'/*'));
	}
}


$app = new App(__DIR__);

$app->beforeDispatch(function ($url) {
	if (!user::getRole('user') && $url != '/pages/login') {
        return response::redirect('/pages/login');
    }
});

$app->setPrefix('/pages');

$app->get('/login', function () {
    return tpl('login');
});

$app->post('/login', function () {
    $pass = request::post('password');
    if ($pass == '123') {
        user::addRole('user');
        return response::redirect('/pages/p/new');
    }
    return tpl('login');
});


$app->get('/', function() {
	return response::redirect('/pages/p/new');
});

$app->get('/p/{.+}', function($name) {
	$menu = Page::ls();
	$page = new Page($name);
	return tpl('main', compact('menu', 'page'));
});

$app->post('/p/{.+}', function($name) {
	$page = new Page($name);
	$page->content = Request::post('content');
	$page->save();
	return response::redirect('/pages/'.$name);
});

$app->run();
