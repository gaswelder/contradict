<?php

namespace gaswelder;

class MethodNotAllowed extends \Exception
{
}

class RouteNotFound extends \Exception
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
            throw new \Exception("route $method $pattern is already defined");
        }
        $this->routes[$pattern][$_method] = $func;
        return $this;
    }

    function dispatch(string $method, string $path)
    {
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
        $path = parse_url($_SERVER['REQUEST_URI'])['path'];
        return $this->dispatch($_SERVER['REQUEST_METHOD'], $path);
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
        $specificity = 1;
        foreach (explode('/', trim($pattern, '/')) as $part) {
            $p = self::parse_part($part);
            $this->parts[] = $p;
            $specificity += 100 + $p['specificity'];
        }
        $this->specificity = $specificity;
    }

    function match(string $url)
    {
        $urlParts = array_map('urldecode', explode('/', trim($url, '/')));
        if (count($urlParts) != count($this->parts)) {
            return [false, []];
        }
        // Every path part may produce 0, 1 or more matched params.
        // They are all collected in a single serial list.
        $allArgs = [];
        foreach ($urlParts as $i => $urlPart) {
            $patternPart = $this->parts[$i];
            $m = [];
            if (!preg_match($patternPart['regexp'], $urlPart, $m)) {
                return [false, []];
            }
            $allArgs = array_merge($allArgs, array_slice($m, 1));
        }
        return [true, $allArgs];
    }

    private static function parse_part($s)
    {
        $toks = [];
        while (strlen($s) > 0) {
            if ($s[0] == '{') {
                $p = strpos($s, '}');
                if ($p) {
                    $tok = ['pat', substr($s, 1, $p - 1)];
                    $s = substr($s, $p + 1);
                    $toks[] = $tok;
                    continue;
                }
            }
            $p = strpos($s, '{');
            if (!$p) {
                $p = strlen($s);
            }
            $tok = ['lit', substr($s, 0, $p)];
            $s = substr($s, $p);
            $toks[] = $tok;
        }

        $regexp = '/^';
        foreach ($toks as $tok) {
            if ($tok[0] == 'lit') {
                $regexp .= preg_quote($tok[1]);
            } else {
                $regexp .= "($tok[1])";
            }
        }
        $regexp .= '$/';

        $specificity = 0;
        foreach ($toks as $tok) {
            if ($tok[0] == 'lit') $specificity += 10;
            else $specificity += 1;
        }

        return compact('regexp', 'specificity');
    }
}
