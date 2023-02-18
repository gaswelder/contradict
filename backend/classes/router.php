<?php

class MethodNotAllowed extends Exception
{
}

class RouteNotFound extends Exception
{
}

class router
{
    // path -> resource
    private $routes = [];

    /**
     * Adds a route.
     * 
     * @param string $method
     * @param string $pattern
     * @param callable $func
     */
    function add($method, $pattern, $func)
    {
        if (!isset($this->routes[$pattern])) {
            $this->routes[$pattern] = [];
        }
        $_method = strtolower(trim($method));
        if (isset($this->routes[$pattern][$_method])) {
            throw new Exception("route $method $pattern is already defined");
        }
        $this->routes[$pattern][$_method] = $func;
        return $this;
    }

    function dispatch()
    {
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        $url = parse_url($_SERVER['REQUEST_URI']);
        [$resource, $args] = $this->find($url['path']);
        if (!$resource) {
            throw new RouteNotFound();
        }
        if (!isset($resource[$method])) {
            throw new MethodNotAllowed();
        }
        return call_user_func_array($resource[$method], $args);
    }

    private function find($url)
    {
        $match = null;
        $specificity = 0;
        $args = [];
        foreach ($this->routes as $pattern => $resource) {
            [$matches, $_args] = match_pattern($pattern, $url);
            if (!$matches) {
                continue;
            }
            $ps = pattern_specificity($pattern);
            if ($ps <= $specificity) {
                continue;
            }
            $match = $resource;
            $args = $_args;
            $specificity = $ps;
        }
        return [$match, $args];
    }
}

function match_pattern($pattern, $url)
{
    if ($pattern == '*') {
        return [true, []];
    }

    $parts = [];
    $pat_parts = explode('/', trim($pattern, '/'));
    foreach ($pat_parts as $part) {
        $parts[] = new expr($part);
    }

    $uri_parts = array_map('urldecode', explode('/', trim($url, '/')));
    if (count($uri_parts) != count($parts)) {
        return false;
    }

    $args = [];
    foreach ($uri_parts as $i => $part) {
        $m = [];
        if (!$parts[$i]->match($part, $m)) {
            return [false, []];
        }
        $args = array_merge($args, array_slice($m, 1));
    }
    return [true, $args];
}

function pattern_specificity($pattern)
{
    if ($pattern == '*') {
        return 0;
    }

    $parts = [];
    $pat_parts = explode('/', trim($pattern, '/'));
    foreach ($pat_parts as $part) {
        $parts[] = new expr($part);
    }

    $s = 1;
    foreach ($parts as $expr) {
        $s += 100 + $expr->specificity();
    }
    return $s;
}

class expr
{
    private $s;
    private $toks = [];

    function __construct($s)
    {
        $this->s = $s;
        while (strlen($this->s) > 0) {
            $this->read();
        }
    }

    function match($s, &$m)
    {
        $p = $this->toPCRE();
        return preg_match($p, $s, $m);
    }

    function specificity()
    {
        $s = 0;
        foreach ($this->toks as $tok) {
            if ($tok[0] == 'lit') $s += 10;
            else $s += 1;
        }
        return $s;
    }

    function toPCRE()
    {
        $s = '';
        foreach ($this->toks as $tok) {
            if ($tok[0] == 'lit') {
                $s .= preg_quote($tok[1]);
            } else {
                $s .= "($tok[1])";
            }
        }

        $delims = ['/', '@'];

        foreach ($delims as $delim) {
            if (strpos($s, $delim) === false) {
                return $delim . '^' . $s . '$' . $delim;
            }
        }

        throw new Exception("Couldn't find suitable delimiter for regular expression: $s");
    }

    private function read()
    {
        if ($this->s[0] == '{') {
            $p = strpos($this->s, '}');
            if ($p) {
                $tok = ['pat', substr($this->s, 1, $p - 1)];
                $this->s = substr($this->s, $p + 1);
                $this->toks[] = $tok;
                return;
            }
        }

        $p = strpos($this->s, '{');
        if (!$p) {
            $p = strlen($this->s);
        }
        $tok = ['lit', substr($this->s, 0, $p)];
        $this->s = substr($this->s, $p);
        $this->toks[] = $tok;
    }
}
