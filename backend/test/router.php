<?php

use gaswelder\RouteNotFound;
use gaswelder\router;

class RouterTest extends TestCase
{
    function testRoot()
    {
        $n = 0;
        router::make()
            ->add('get', '/', function () use (&$n) {
                $n++;
            })
            ->dispatch('GET', '/');
        $this->assertEquals($n, 1);
    }

    function testApi()
    {
        $r = new router();
        $r->add('get', '/api/', function () use (&$n) {
            $n++;
        });
        $r->dispatch('GET', '/api/');
        $r->dispatch('GET', '/api');
        $this->assertEquals($n, 2);
    }

    function testArgs()
    {
        $arg = 0;
        router::make()
            ->add('get', '/x/{\w+}', function ($val) use (&$arg) {
                $arg = $val;
            })
            ->dispatch('get', '/x/123');
        $this->assertEquals($arg, 123);
    }

    function testPatterns()
    {
        $arg = 0;
        $r = router::make()
            ->add('get', '/x/abc{\d+}z', function ($val) use (&$arg) {
                $arg = $val;
            });
        $r->dispatch('get', '/x/abc123z');
        try {
            $r->dispatch('get', '/x/abcabcz');
            throw new Exception("should have thrown a RouteNotFound");
        } catch (RouteNotFound $e) {
            //
        }
    }

    function testRegression1()
    {
        $r = router::make()
            ->add('get', '/api/{\d+}/test', function ($dict_id) {
                return $dict_id;
            })
            ->add('get', '/api/{\d+}/add', function ($dict_id) {
                return $dict_id;
            });
        $this->assertEquals($r->dispatch('GET', '/api/123/test'), 123);
    }

    function testRegression2()
    {
        $r = router::make()
            ->add('post', '/api/{\d+}/add', function ($dict_id) {
                return 1;
            })
            ->add('get', '/api/{\d+}/test', function ($dict_id) {
                return $dict_id;
            });
        $this->assertEquals($r->dispatch('GET', '/api/123/test'), 123);
    }
}
