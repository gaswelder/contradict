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

    static function make()
    {
        return new router;
    }

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

    function dispatch(string $method, string $url)
    {
        $path = parse_url($url)['path'];
        $match = null;
        foreach ($this->routes as $pattern => $res) {
            $p = new router_pattern($pattern);
            [$ok, $args] = $p->match($path);
            if (!$ok) {
                continue;
            }
            if (!$match || $match['p']->specificity < $p->specificity) {
                $match = [
                    'p' => $p,
                    'resource' => $res,
                    'args' => $args,
                ];
            }
        }
        if (!$match) {
            throw new RouteNotFound();
        }
        $func = $match['resource'][strtolower($method)] ?? null;
        if (!$func) {
            throw new MethodNotAllowed();
        }
        return call_user_func_array($func, $match['args']);
    }

    function run()
    {
        return $this->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    }
}

class router_pattern
{
    public $pattern;
    private $parts;
    public $specificity;

    function __construct($pattern)
    {
        $this->pattern = $pattern;
        $s = 1;
        foreach (explode('/', trim($pattern, '/')) as $part) {
            $expr = new expr($part);
            $this->parts[] = $expr;
            $s += 100 + $expr->specificity();
        }
        $this->specificity = $s;
    }

    function match(string $url)
    {
        $parts = array_map('urldecode', explode('/', trim($url, '/')));
        if (count($parts) != count($this->parts)) {
            return [false, []];
        }
        // Every path part may produce 0, 1 or more matched params.
        // They are all collected in a single serial list.
        $allArgs = [];
        foreach ($parts as $i => $part) {
            [$ok, $params] = $this->parts[$i]->match($part);
            if (!$ok) {
                return [false, []];
            }
            $allArgs = array_merge($allArgs, $params);
        }
        return [true, $allArgs];
    }
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

    function match(string $s)
    {
        $p = $this->toPCRE();
        $m = [];
        if (!preg_match($p, $s, $m)) {
            return [false, []];
        }
        return [true, array_slice($m, 1)];
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
