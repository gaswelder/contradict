<?php

class S3 implements FileSystem
{
    private $s3;
    private $prefix;

    function __construct(CloudCube $s3, string $prefix)
    {
        $this->s3 = $s3;
        $this->prefix = $prefix;
    }

    function exists(string $path): bool
    {
        return $this->s3->exists($this->prefix . $path);
    }

    function write(string $path, string $data)
    {
        $this->s3->write($this->prefix . $path, $data);
    }

    function read(string $path): string
    {
        return $this->s3->read($this->prefix . $path);
    }
}
