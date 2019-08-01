<?php

use PHPUnit\Framework\TestCase;

class CookieAuthTest extends TestCase
{
    function test()
    {
        $this->checkAuth(new CookieAuth('nIkpxL8wkMRZIAf+r+eldeSPVrhm+jj38TjoiGbjk8Y='));
    }

    function checkAuth(Auth $auth)
    {
        // Obtain tokens, verify tokens.
        $t1 = $auth->login('bob', 'marley');
        $t2 = $auth->login('bruce', 'willis');
        $id1 = $auth->checkToken($t1);
        $id2 = $auth->checkToken($t2);

        // Both tokens should be not empty.
        $this->assertNotEquals('', $t1);
        $this->assertNotEquals('', $t2);

        // The tokens should be different.
        $this->assertNotEquals($t1, $t2);

        // The token's payload should be non-empty and different.
        $this->assertNotEquals($id1, $id2);
        $this->assertNotEquals('', $id1);
        $this->assertNotEquals('', $id2);

        // Invalid tokens.
        $this->assertEquals('', $auth->checkToken('no such token'));
        $this->assertEquals('', $auth->checkToken(''));

        // Invalid but plausible tokens.
        $t3 = 'x' . substr($t2, 1);
        $this->assertEquals('', $auth->checkToken($t3));

        // Shouldn't return the same token for the same user.
        $t1 = $auth->login('bob', 'marley');
        $t2 = $auth->login('bob', 'marley');
        $this->assertNotEquals($t1, $t2);
    }
}
