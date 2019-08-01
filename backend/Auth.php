<?php

interface Auth
{
    /**
     * Returns a token for a given name and password.
     * Returns the empty string if name or password is invalid.
     */
    function login(string $name, string $password): string;
    /**
     * Returns the user name for a given token.
     * Returns the empty string if the token is invalid.
     */
    function checkToken(string $token): string;
}
