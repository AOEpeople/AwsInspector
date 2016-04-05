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

    /**
     * @param $origin SecurityGroup|string security group, ip range or single ip address
     * @param $port
     * @param string $protocol
     * @return bool
     */
    public function hasAccess($origin, $port, $protocol='tcp') {
        foreach ($this->getIpPermissions() as $permission) {
            if ($permission['IpProtocol'] != $protocol || $permission['FromPort'] != $port) {
                continue;
            }
            if ($origin instanceof SecurityGroup) {
                foreach ($permission['UserIdGroupPairs'] as $idGroupPair) {
                    if ($idGroupPair['GroupId'] == $origin->getGroupId()) {
                        return true;
                    }
                }
            } else {
                $isRange = (strpos($origin, '/') !== false);
                foreach ($permission['IpRanges'] as $ipRange) {
                    if ($isRange) {
                        if ($origin == $ipRange['CidrIp']) {
                            return true;
                        }
                    } else {
                        if ($this->ipMatchesCidr($origin, $ipRange['CidrIp'])) {
                            return true;
                        }
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

    /**
     * @return array array of security group ids
     */
    public function getIngressSecurityGroupIds()
    {
        $ingressSecurityGroupIds = [];
        $linkLabelTmp = [];
        foreach ($this->getIpPermissions() as $type) {

            $protocol = ($type['IpProtocol'] == '-1') ? 'All' : $type['IpProtocol'];
            $ports = 'All';
            if (!empty($type['FromPort']) && !empty($type['ToPort'])) {
                if ($type['FromPort'] == $type['ToPort']) {
                    $ports = $type['FromPort'];
                } else {
                    $ports = $type['FromPort'] . '-' .$type['toPort'];
                }
            }

            foreach ($type['UserIdGroupPairs'] as $userIdGroupPair) {
                $ingressSecurityGroupId = $userIdGroupPair['GroupId'];
                $linkLabelTmp[$ingressSecurityGroupId][$protocol][] = $ports;
            }

        }

        foreach ($linkLabelTmp as $source => $data) {
            $tmp = [];
            foreach ($data as $protocol => $linksPerProtocol) {
                sort($linksPerProtocol);
                $tmp[$protocol] = implode(',', $linksPerProtocol);
            }
            $tmp2 = [];
            foreach ($tmp as $protocol => $links) {
                $tmp2[] = "$protocol:$links";
            }
            $label = implode(', ', $tmp2);
            $ingressSecurityGroupIds[$label] = $source;
        }

        return $ingressSecurityGroupIds;
    }


}
