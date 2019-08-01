<?php

class CookieAuth implements Auth
{
    private $key;

    static function generateKey(): string
    {
        return base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
    }

    function __construct(string $key)
    {
        $this->key = base64_decode($key);
        if (strlen($this->key) != SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new Exception("invalid cookie key length");
        }
    }

    function login(string $name, string $password): string
    {
        if (strlen($name) == 0 && strlen($password) == 0) {
            return '';
        }
        $id = sha1(sha1($name) . $password);
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($id, $nonce, $this->key);
        return base64_encode($nonce . $cipher);
    }

    function checkToken(string $token): string
    {
        $bytes = base64_decode($token);
        $nonce = substr($bytes, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        if (strlen($nonce) != SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            return '';
        }
        $cipher = substr($bytes, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $dec = sodium_crypto_secretbox_open($cipher, $nonce, $this->key);
        return $dec;
    }
}
