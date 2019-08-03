<?php

use Aws\S3\S3Client;

class CloudCube
{
    private function dir()
    {
        // Something like https://cloud-cube-eu.s3.amazonaws.com/abcdefgh
        return basename(getenv('CLOUDCUBE_URL'));
    }

    private function s3()
    {
        $key_id = getenv('CLOUDCUBE_ACCESS_KEY_ID');
        $key = getenv('CLOUDCUBE_SECRET_ACCESS_KEY');
        return new S3Client([
            'version' => 'latest',
            'region' => 'eu-west-1',
            'endpoint' => 'https://s3.amazonaws.com',
            'credentials' => [
                'key'    => $key_id,
                'secret' => $key,
            ],
        ]);
    }

    function write($name, $data)
    {
        $this->s3()->putObject([
            'Bucket' => 'cloud-cube-eu',
            'Key' => $this->dir() . '/' . $name,
            'Body' => $data
        ]);
    }

    function exists($name): bool
    {
        return $this->s3()->doesObjectExist('cloud-cube-eu', $this->dir() . '/' . $name);
    }

    function read($name)
    {
        $result = $this->s3()->getObject([
            'Bucket' => 'cloud-cube-eu',
            'Key' => $this->dir() . '/' . $name
        ]);
        return $result['Body'];
    }
}
