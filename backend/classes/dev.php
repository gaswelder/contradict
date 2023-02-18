<?php

class dev
{
    static function varfmt($var)
    {
        ob_start();
        var_dump($var);
        $s = ob_get_clean();
        return $s;
    }

    static function clg(...$var)
    {
        foreach ($var as $i => $var) {
            error_log("$i ---> " . self::varfmt($var));
        }
    }
}
