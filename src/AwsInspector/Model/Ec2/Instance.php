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
 * @method getImageId()
 * @method getState()
 * @method getPrivateDnsName()
 * @method getPublicDnsName()
 * @method getStateTransitionReason()
 * @method getKeyName()
 * @method getAmiLaunchIndex()
 * @method getProductCodes()
 * @method getInstanceType()
 * @method getLaunchTime()
 * @method getPlacement()
 * @method getMonitoring()
 * @method getSubnetId()
 * @method getVpcId()
 * @method getArchitecture()
 * @method getRootDeviceType()
 * @method getRootDeviceName()
 * @method getBlockDeviceMappings()
 * @method getVirtualizationType()
 * @method getClientToken()
 * @method getSecurityGroups()
 * @method getSourceDestCheck()
 * @method getHypervisor()
 * @method getNetworkInterfaces()
 * @method getEbsOptimized()
 */
class Instance extends \AwsInspector\Model\AbstractResource
{

    protected $username;

    protected $multiplexSshConnection = false;

    public function getDefaultUsername()
    {
        if (is_null($this->username)) {
            if (getenv('AWSINSPECTOR_DEFAULT_EC2_USER')) {
                $this->username = getenv('AWSINSPECTOR_DEFAULT_EC2_USER');
            } else {
                $this->username = 'ec2-user';
                $ami = $this->getImageId();
                if (in_array($ami, ['ami-47a23a30', 'ami-47360a30'])) {
                    $this->username = 'ubuntu';
                }
            }
        }
        return $this->username;
    }

    public function getPrivateKey()
    {
        $keyName = $this->getKeyName();
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
            $this->getDefaultUsername(),
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
