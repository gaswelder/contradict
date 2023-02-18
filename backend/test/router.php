<?php

use gaswelder\router;

class RouterTest extends TestCase
{
    function testFoo()
    {
        $r = new router();
        $n = 0;
        $r->add('get', '/', function () use (&$n) {
            $n++;
        });
        $r->dispatch('GET', '/');
        $this->assertEquals($n, 1);
    }

    function testApi()
    {
        $r = new router();
        $r->add('get', '/api/', function () use (&$n) {
            $n++;
        });
        $r->dispatch('GET', '/api/');
        $this->assertEquals($n, 1);
    }

    function testArgs()
    {
        $r = new router();
        $n = 0;
        $arg = 0;
        $r->add('get', '/', function () use (&$n) {
            $n++;
        })->add('get', '/x/{\d+}', function ($val) use (&$arg) {
            $arg = $val;
        });
        $r->dispatch('GET', '/');
        $r->dispatch('get', '/x/123');
        $this->assertEquals($n, 1);
        $this->assertEquals($arg, 123);
    }

    function testR0()
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

    function testR1()
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
