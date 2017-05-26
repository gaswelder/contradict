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

$app->setPrefix('/links');

$app->get('/login', function () {
    return tpl('login');
});

$app->post('/login', function () {
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

$app->get('/', function () {
    $links = Link::fromRows(db()->getRecords('SELECT * FROM links WHERE archive = 0 ORDER BY updated_at'));
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

class Arr
{
    private $a;

    static function make($a = []) {
        return new self($a);
    }

    function __construct($a = [])
    {
        $this->a = $a;
    }

    function map($func) {
        return new self(array_map($func, $this->a));
    }

    function filter($func = null) {
        $a = $func ? array_filter($this->a, $func) : array_filter($this->a);
        return new self($a);
    }

    function each($func) {
        foreach($this->a as $k => $v) {
            call_user_func($func, $v, $k);
        }
    }

    function get() {
        return $this->a;
    }
}

$app->post('/', function () {
    $cat = Request::post('category');

    Arr::make(explode("\n", Request::post('url')))
        ->map('trim')->filter()
        ->each(function($url) use ($cat) {
            $link = new Link();
            $link->url = $url;
            $link->category = $cat;
            $link->save();
        });
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

$app->post('/{\d+}/category', function ($id) {
    $link = Link::get($id);
    if (!$link) {
        return 404;
    }
    $link->category = Request::post('category');
    $link->save();
    return Response::redirect('/links');
});

$app->post('/{\d+}/action', function ($id) {
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
