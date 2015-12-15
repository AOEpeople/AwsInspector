<?php

namespace AwsInspector\Model\SecurityGroup;

/**
 * Class SecurityGroup
 *
 * @method getGroupId()
 * @method getGroupName()
 * @method getOwnerId()
 * @method getDescription()
 * @method getVpcId()
 * @method getTags()
 * @method getIpPermissions()
 * @method getIpPermissionsEgress()
 */
class SecurityGroup extends \AwsInspector\Model\AbstractResource
{

    public function getAssocTags() {
        $assocTags = [];
        foreach ($this->getTags() as $data) {
            $assocTags[$data['Key']] = $data['Value'];
        }
        return $assocTags;
    }

    /**
     * @param $cidrIp string ip range or single ip address
     * @param $port
     * @param string $protocol
     * @return bool
     */
    public function hasAccess($cidrIp, $port, $protocol='tcp') {
        $isRange = (strpos($cidrIp, '/') !== false);
        foreach ($this->getIpPermissions() as $permission) {
            if ($permission['IpProtocol'] != $protocol || $permission['FromPort'] != $port) {
                continue;
            }
            foreach ($permission['IpRanges'] as $ipRange) {
                if ($isRange) {
                    if ($cidrIp == $ipRange['CidrIp']) {
                        return true;
                    }
                } else {
                    if ($this->ipMatchesCidr($cidrIp, $ipRange['CidrIp'])) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    protected function ipMatchesCidr($ip, $range)
    {
        list ($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
        return ($ip & $mask) == $subnet;
    }

}
