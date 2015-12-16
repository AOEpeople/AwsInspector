<?php

namespace AwsInspector\Model\Ec2;

use AwsInspector\Ssh\Connection;
use AwsInspector\Ssh\PrivateKey;

/**
 * Class Instance
 *
 * @method getInstanceId()
 * @method getTags()
 * @method getPublicIpAddress()
 * @method getPrivateIpAddress()
 */
class Instance extends \AwsInspector\Model\AbstractResource
{

    protected $username = 'ubuntu';

    protected $multiplexSshConnection = false;

    public function __construct(array $apiData)
    {
        parent::__construct($apiData);
        if (getenv('AWSINSPECTOR_DEFAULT_EC2_USER')) {
            $this->username = getenv('AWSINSPECTOR_DEFAULT_EC2_USER');
        }
    }

    public function getPrivateKey()
    {
        $keyName = $this->apiData['KeyName'];
        if (empty($keyName)) {
            throw new \Exception('No KeyName found');
        }
        return PrivateKey::get('keys/' . $keyName . '.pem');
    }

    /**
     * Get jump host (bastion server)
     *
     * Overwrite this method in your inheriting class and return
     * a \AwsInspector\Model\Ec2\Instance representing your bastion server
     *
     * @return null|Instance
     */
    public function getJumpHost()
    {
        return null;
    }

    public function getConnectionIp()
    {
        return $this->getPublicIpAddress() ? $this->getPublicIpAddress() : $this->getPrivateIpAddress();
    }

    /**
     * Get SSH connection
     *
     * @return Connection
     * @throws \Exception
     */
    public function getSshConnection()
    {
        return new Connection(
            $this->username,
            $this->getConnectionIp(),
            $this->getPrivateKey(),
            $this->getJumpHost(),
            $this->multiplexSshConnection
        );
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

}
