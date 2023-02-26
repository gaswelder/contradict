<?php

class dev
{
    static function varfmt($var)
    {
        ob_start();
        var_dump($var);
        $s = ob_get_clean();
        return trim($s);
    }

    static function clg(...$var)
    {
        error_log(implode(' ', array_map([self::class, 'varfmt'], $var)));
    }
}
