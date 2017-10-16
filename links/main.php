<?php

require __DIR__.'/../hl/main.php';

use havana\dbobject;
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
    if (!user::getRole('user') && request::url()->path != '/links/login') {
        return response::redirect('/links/login');
    }
    return $next();
});

$app->setPrefix('/links');

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

$app->get('/', function () {
    $links = Link::fromRows(db()->getRows('SELECT * FROM links WHERE archive = 0 ORDER BY updated_at'));
    return linksListView($links);
});

$app->get('/category/{.+}', function($cat) {
    if($cat == 'other') {
        $cat = '';
    }
    $cat = str_replace(':', '/', $cat);
    $links = Link::fromRows(db()->getRecords('select * from links where category = ? and archive = 0', $cat));
    return linksListView($links);
});

function alt($a, $b)
{
    return $a ? $a : $b;
}

function linksListView($links)
{
    $groups = [];
    foreach ($links as $link) {
        $groups[$link->category][] = $link;
    }
    uasort($groups, function ($a, $b) {
        return count($b) - count($a);
    });
    return tpl('list', compact('groups'));
}

$app->get('/new', function () {
    $categories = db()->getValues("select distinct category from links where category <> ''");
    return tpl('form', compact('categories'));
});

$app->post('/', function () {
    $cat = request::post('category');

    Arr::make(explode("\n", request::post('url')))
        ->map('trim')->filter()
        ->each(function($url) use ($cat) {
            $link = new Link();
            $link->url = $url;
            $link->category = $cat;
            $link->save();
        });
    return response::redirect('/links');
});

$app->get('/{\d+}', function ($id) {
    $link = Link::get($id);
    if (!$link) {
        return 404;
    }
    $categories = db()->getValues("select distinct category from links where category <> ''");

    $title = getPageTitle($link->url);

    return tpl('view', compact('link', 'categories', 'title'));
});

function getPageTitle($url)
{
    $s = file_get_contents($url);
    if (!preg_match('@<title>(.+)</title>@', $s, $m)) {
        return null;
    }
    return $m[1];
}

$app->post('/{\d+}/category', function ($id) {
    $link = Link::get($id);
    if (!$link) {
        return 404;
    }
    $link->category = request::post('category');
    $link->save();
    return response::redirect('/links');
});

$app->post('/{\d+}/action', function ($id) {
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


$app->run();
