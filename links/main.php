<?php

require __DIR__.'/../hl/app.php';

class Link extends dbobject
{
    const TABLE_NAME = 'links';

    function __construct()
    {
        $this->created_at = time();
        $this->updated_at = time();
    }
}

$app = new App(__DIR__);

$app->beforeDispatch(function ($url) {
    if (!user::select('user') && $url != '/links/login') {
        return Response::redirect('/links/login');
    }
});

$app->get('/links/login', function () {
    return tpl('login');
});

$app->post('/links/login', function () {
    $pass = Request::post('password');
    if ($pass == '123') {
        user::auth('user');
        return Response::redirect('/links');
    }
    return tpl('login');
});

$app->post('/links/logout', function () {
    user::clear('user');
    return Response::redirect('/links/login');
});

$app->get('/links', function () {
    $links = Link::fromRows(db()->getRecords('SELECT * FROM links WHERE archive = 0 ORDER BY updated_at'));
    $groups = [];
    foreach ($links as $link) {
        $groups[$link->category][] = $link;
    }
    uasort($groups, function ($a, $b) {
        return count($b) - count($a);
    });
    return tpl('list', compact('groups'));
});

$app->get('/links/new', function () {
    $categories = db()->getValues("select distinct category from links where category <> ''");
    return tpl('form', compact('categories'));
});

$app->post('/links', function () {
    $url = Request::post('url');
    $cat = Request::post('category');
    $link = new Link();
    $link->url = $url;
    $link->category = $cat;
    $link->save();

    return Response::redirect('/links');
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

$app->post('/links/{\d+}/category', function ($id) {
    $link = Link::get($id);
    if (!$link) {
        return 404;
    }
    $link->category = Request::post('category');
    $link->save();
    return Response::redirect('/');
});

$app->post('/links/{\d+}/action', function ($id) {
    $link = Link::get($id);
    if (!$link) {
        return 404;
    }

    $act = Request::post('act');
    switch ($act) {
        case 'archive':
            $link->archive = 1;
            break;
        case 'later':
            $link->updated_at = time();
            break;
    }
    $link->save();

    return Response::redirect('/links');
});

$app->run();
