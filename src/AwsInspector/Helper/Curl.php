<?php

namespace AwsInspector\Helper;

class Curl
{

    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getResponseStatusCodeCommand() {
        return 'curl --insecure --silent --output /dev/null --write-out "%{http_code}" ' . escapeshellarg($this->url);
    }

    public function getResponseBodyCommand() {
        return 'curl --insecure --silent ' . escapeshellarg($this->url);
    }

}