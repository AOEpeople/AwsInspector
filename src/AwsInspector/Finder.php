<?php

namespace AwsInspector;

class Finder {

    public function findElb() {

    }

    public function findRds() {

    }

    public function findCache() {

    }

    public function findFpc() {

    }

    public function findSessionStorage() {

    }

    public function findEc2Instance($value) {
        foreach (['instance-id', 'ip-address', 'private-ip-address'] as $field) {
            $instance = $this->findEc2InstanceBy($field, $value);
            if ($instance !== false) {
                return $instance;
            }
        }
        return false;
    }

    public function findEc2InstanceBy($field, $value) {
        if (!in_array($field, ['instance-id', 'ip-address', 'private-ip-address'])) {
            throw new \InvalidArgumentException('Invalid field');
        }
        $filters = [
            ['Name' => 'instance-state-name', "Values" => ['running']],
            ['Name' => $field, "Values" => [$value]]
        ];
        $instances = $this->findEc2Instances($filters);
        if (count($instances) == 1) {
            return end($instances);
        }
        return false;
    }

    public function findEc2Instances(array $filters=[]) {

        $headers = ['Instance-ID', 'State', 'Subnet', 'AZ', 'Name', 'Public IP', 'Private IP'];

        $queryParts = [];
        foreach ($filters as $filter) {
            $parts = explode(':', $filter['Name']);
            if ($parts[0] == 'tag') {
                $queryParts[] = "Tags[?Key==`{$parts[1]}`].Value | [0],";
                $headers[] = $parts[1];
            }
        }

        $headers[] = 'KeyName';

        $ec2Client = \AwsInspector\SdkFactory::getClient('ec2'); /* @var $ec2Client \Aws\Ec2\Ec2Client */
        $result = $ec2Client->describeInstances(['Filters' => $filters]);
        $rows = $result->search('Reservations[].Instances[].[
            InstanceId,
            State.Name,
            SubnetId,
            Placement.AvailabilityZone,
            Tags[?Key==`Name`].Value | [0],
            PublicIpAddress,
            PrivateIpAddress,
            '.implode('', $queryParts).'
            KeyName
        ]');

        $result = [];

        foreach ($rows as $row) {
            $result[] = array_combine($headers, $row);
        }

        return $result;
    }

    public function findEc2InstancesByTags(array $tags=array()) {
        $filters = [['Name' => 'instance-state-name', "Values" => ['running']]];
        foreach ($tags as $key => $value) {
            if (is_numeric($key)) {
                list($key, $value) = explode('=', $value);
            }
            $filters[] = ['Name' => 'tag:'.$key, "Values" => [$value]];
        }
        return $this->findEc2Instances($filters);
    }

}