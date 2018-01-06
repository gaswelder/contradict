<?php

require __DIR__.'/hl/main.php';

use havana\App;
use havana\user;
use havana\request;
use havana\response;

function tplvar($name) {
    switch ($name) {
        case 'loggedIn':
            return user::getRole('user');
        default:
            panic("Unknown tplvar: $name");
    }
}

$app = new App(__DIR__);

$app->middleware(function($next) {
    if (!user::getRole('user') && request::url()->path != '/login') {
        return response::redirect('/login');
    }
    return $next();
});

$app->get('/login', function () {
    return tpl('login');
});

$app->post('/login', function () {
    $pass = request::post('password');
    if ($pass == '123') {
        user::addRole('user');
        return response::redirect('/links');
    }
    return tpl('login');
});

$app->post('/logout', function () {
    user::removeRole('user');
    return response::redirect('/links/login');
});

$app->get('/links', function () {
    $links = Link::active();
    return tpl('list', compact('links'));
});

$app->get('/links/category/{.+}', function($cat) {
    if($cat == 'other') {
        $cat = '';
    }
    $cat = str_replace(':', '/', $cat);
    $links = Link::fromCategory($cat);
    return tpl('list', compact('links'));
});

$app->get('/links/new', function () {
    $categories = Link::categories();
    return tpl('form', compact('categories'));
});

$app->post('/links', function () {
    $cat = request::post('category');

    Arr::make(explode("\n", request::post('url')))
        ->map('trim')->filter()
        ->each(function($url) use ($cat) {
            $link = new Link();
            $link->url = $url;
            $link->category = $cat;
            try {
                $link->title = getPageTitle($url);
            } catch (Exception $e) {
                $link->title = '';
            }
            $link->save();
        });
    return response::redirect('/links');
});

$app->get('/links/{\d+}', function ($id) {
    $link = Link::get($id);
    if (!$link) {
        return 404;
    }
    $categories = Link::categories();

    return tpl('view', compact('link', 'categories'));
});

function getPageTitle($url)
{
    $s = file_get_contents($url);
    if (!preg_match('@<title>(.*?)</title>@', $s, $m)) {
        return null;
    }
    return html_entity_decode($m[1]);
}

$app->post('/links/{\d+}/category', function ($id) {
    $link = Link::get($id);
    if (!$link) {
        return 404;
    }
    $link->category = request::post('category');
    $link->save();
    return response::redirect('/links');
});

$app->post('/links/{\d+}/action', function ($id) {
    $link = Link::get($id);
    if (!$link) {
        return 404;
    }

    $act = request::post('act');
    switch ($act) {
        case 'archive':
            $link->archive = 1;
            break;
        case 'later':
            $link->updated_at = time();
            break;
    }
    $link->save();

    return response::redirect('/links');
});

$app->get('/links/export', function() {
    $links = Link::all();
    $f = tmpfile();
    foreach ($links as $link) {
        $row = [
            $link->created_at,
            $link->updated_at,
            $link->category,
            $link->url,
            $link->archive,
            $link->title
        ];
        fputcsv($f, $row);
    }
    rewind($f);
    return response::make($f)->download('links.csv');
});

$app->get('/links/import', function() {
    return tpl('import');
});

$app->post('/links/import', function() {
    $upload = request::files('file')[0];
    $fields = ['created_at', 'updated_at', 'category', 'url', 'archive', 'title'];
    $f = $upload->stream();
    for (;;) {
        $row = fgetcsv($f);
        if ($row === false) break;
        $link = new Link;
        foreach ($fields as $i => $k) {
            $link->$k = $row[$i];
        }
        $link->save();
    }
    fclose($f);
    exit;
});

$app->get('/pages', function() {
	return response::redirect('/pages/new');
});

$app->get('/pages/{.+}', function($name) {
    $menu = Page::ls();
	$page = new Page($name);
	return tpl('pages', compact('menu', 'page'));
});

$app->post('/pages/{.+}', function($name) {
	$page = new Page($name);
	$page->content = Request::post('content');
	$page->save();
	return response::redirect('/pages/'.$name);
});

require __DIR__ . '/dict.php';

$app->run();
