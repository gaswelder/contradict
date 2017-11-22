<?php

require __DIR__.'/../hl/main.php';

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

$app->get('/dict', function() {
    $d = Dict::load();
    $stats = $d->stats();
    return tpl('dict/home', compact('stats'));
});

$app->get('/dict/add', function() {
    return tpl('dict/add');
});

class Dict
{
    const GOAL = 10;

    static function load() {
        return new self();
    }

    function __construct() {
        $path = $this->path();
        $this->rows = [];
        if (!file_exists($path)) {
            return;
        }
        $f = fopen($path, 'rb');
        while (1) {
            $row = fgetcsv($f);
            if (!$row) break;
            $this->rows[] = $row;
        }
        fclose($f);
    }

    private function path()
    {
        return __DIR__ . '/dict.csv';
    }

    function append($tuples) {
        foreach ($tuples as $t) {
            $t[] = 0;
            $t[] = 0;
            $this->rows[] = $t;
        }
        return $this;
    }

    function pick($n, $dir)
    {
        return Arr::make($this->rows)
            ->filter(function($row) use ($dir) {
                return $row[$dir + 2] < self::GOAL;
            })
            ->shuffle()
            ->take($n)
            ->map(function($row) {
                return array_slice($row, 0, 2);
            })
            ->get();
    }

    function save() {
        $path = $this->path();
        if (file_exists($path)) {
            copy($path, $path.date('ymd-his'));
        }
        $f = fopen($path, 'wb');
        foreach ($this->rows as $row) {
            fputcsv($f, $row);
        }
        fclose($f);
        return $this;
    }

    private function find($q, $dir) {
        foreach ($this->rows as $i => $row) {
            if ($row[$dir] == $q) {
                return $i;
            }
        }
        return -1;
    }

    function check($q, $a, $dir)
    {
        $i = $this->find($q, $dir);
        $row = $this->rows[$i];
        $expected = $row[abs($dir-1)];
        $ok = $a == $expected;
        if ($ok) {
            $this->rows[$i][$dir + 2]++;
        }
        return [
            'q' => $q,
            'a' => $a,
            'expected' => $expected,
            'ok' => $a == $expected
        ];
    }

    function stats()
    {
        $ok = 0;
        foreach ($this->rows as $row) {
            $ok += $row[2] + $row[3];
        }
        $n = count($this->rows);
        return [
            'pairs' => $n,
            'progress' => $ok / self::GOAL / $n
        ];
    }
}

$app->post('/dict/add', function() {
    $lines = Arr::make(explode("\n", request::post('words')))
        ->map('trim')
        ->filter()
        ->map(function($line) {
            return preg_split('/\s+-\s+/', $line, 2);
        });
    
    Dict::load() -> append($lines->get()) -> save();

    return response::redirect('/dict');
});

$app->get('/dict/test', function() {
    $d = Dict::load();
    $tuples1 = $d->pick(10, 0);
    $tuples2 = $d->pick(10, 1);
    return tpl('dict/test', compact('tuples1', 'tuples2'));
});

$app->post('/dict/test', function() {
    $Q = request::post('q');
    $A = request::post('a');
    $dir = request::post('dir');

    $d = Dict::load();

    $results = [];
    foreach ($Q as $i => $q) {
        $a = $A[$i];
        $results[] = $d->check($q, $a, $dir[$i]);
    }
    $d->save();
    return tpl('dict/results', compact('results'));
});


$app->run();
