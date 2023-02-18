<?php

class AssertException extends Exception
{
}

class TestCase
{
    function assertEquals($actual, $expected)
    {
        if ($actual != $expected) {
            $e = var_export($expected, true);
            $a = var_export($actual, true);
            echo "expected: $e\n";
            echo "actual:   $a\n";
            throw new AssertException();
        }
    }

    function assertNotEquals($actual, $expected)
    {
        if ($actual === $expected) {
            echo "expected to not equal '$expected'\n";
            throw new AssertException();
        }
    }

    function assertContains($substring, $string)
    {
        if (strpos($string, $substring) === false) {
            echo "expected '$string' to contain '$substring'\n";
            throw new AssertException();
        }
    }

    function assertNotContains($substring, $string)
    {
        if (strpos($string, $substring) !== false) {
            echo "expected '$string' to not contain '$substring'\n";
            throw new AssertException();
        }
    }

    function assertCount($n, $list)
    {
        $m = count($list);
        if ($m != $n) {
            $e = var_export($list);
            echo "expected count of '$e' to be $n, got $m\n";
            throw new AssertException();
        }
    }

    function assertInstanceOf($className, $obj)
    {
        if (!($obj instanceof $className)) {
            $cn = get_class($obj);
            echo "expected object to be instance of $className, got $cn\n";
            throw new AssertException();
        }
    }
}


$cc = get_declared_classes();
$fails = 0;
$ok = 0;
foreach (array_slice($argv, 1) as $path) {
    echo $path, "\n";
    require($path);
    $cc1 = get_declared_classes();
    $newClasses = array_diff($cc1, $cc);
    foreach ($newClasses as $cn) {
        if (!str_ends_with($cn, "Test")) {
            continue;
        }
        $test = new $cn();
        foreach (get_class_methods($test) as $name) {
            if (substr($name, 0, 4) != "test") {
                continue;
            }
            try {
                call_user_func_array([$test, $name], []);
                echo "OK $name\n";
                $ok++;
            } catch (AssertException) {
                echo "FAIL $name\n";
                $fails++;
            }
        }
    }
    $cc = $cc1;
    echo "\n";
}
if ($fails > 0) {
    echo "fails: $fails, successes: $ok\n";
} else {
    echo "successes: $ok, fails: $fails\n";
}
