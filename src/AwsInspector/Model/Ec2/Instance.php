<?php

namespace AwsInspector\Model\Ec2;

use AwsInspector\Ssh\Connection;
use AwsInspector\Ssh\PrivateKey;

class Instance
{

    protected $apiData;

    protected $username = 'ubuntu';

    public function __construct(array $apiData)
    {
        $this->apiData = $apiData;
        if (getenv('AWSINSPECTOR_DEFAULT_EC2_USER')) {
            $this->username = getenv('AWSINSPECTOR_DEFAULT_EC2_USER');
        }
    }

    public function getPublicIp()
    {
        return $this->apiData['PublicIpAddress'];
    }

    public function getPrivateIp()
    {
        return $this->apiData['PrivateIpAddress'];
    }

    public function getPrivateKey() {
        $keyName = $this->apiData['KeyName'];
        if (empty($keyName)) {
            throw new \Exception('No KeyName found');
        }
        return PrivateKey::get('keys/'.$keyName.'.pem');
    }

    public function getSshConnection() {
        return new Connection($this->username, $this->getPublicIp(), $this->getPrivateKey());
    }

    public function exec($command)
    {
        return $this->getSshConnection()->exec($command);
    }

    public function fileExists($file)
    {
        $result = $this->exec('test -f ' . escapeshellarg($file));
        return ($result['returnVar'] == 0);
    }

    public function directoryExists($file)
    {
        $result = $this->exec('test -d ' . escapeshellarg($file));
        return ($result['returnVar'] == 0);
    }

    public function linkExists($file)
    {
        $result = $this->exec('test -l ' . escapeshellarg($file));
        return ($result['returnVar'] == 0);
    }

    public function getFileContent($file)
    {
        $result = $this->exec('cat ' . escapeshellarg($file));
        return implode("\n", $result['output']);
    }

    public function getHttpStatusCode($url)
    {
        $result = $this->exec('curl -s -o /dev/null -w "%{http_code}" ' . escapeshellarg($url));
        return intval(end($result['output']));
    }

    public function extractData(array $mapping) {
        $result=[];
        foreach ($mapping as $fieldName => $expression) {
            $result[$fieldName] = \JmesPath\Env::search($expression, $this->apiData);
        }
        return $result;
    }

}
