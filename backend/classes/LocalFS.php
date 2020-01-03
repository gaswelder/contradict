<?php

class LocalFS implements FileSystem
{
    private $prefix;

    function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    function exists(string $path): bool
    {
        return file_exists($this->prefix . $path);
    }

    function write(string $path, string $data)
    {
        file_put_contents($this->prefix . $path, $data);
    }

    function read(string $path): string
    {
        return file_get_contents($this->prefix . $path);
    }
}
