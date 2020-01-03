<?php

interface FileSystem
{
    function exists(string $path): bool;
    function write(string $path, string $data);
    function read(string $path): string;
}
