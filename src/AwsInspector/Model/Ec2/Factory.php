<?php

namespace AwsInspector\Model\Ec2;

class Factory
{

    public static function create(array $apiData)
    {
        $type = self::extractValue('Type', $apiData);
        if (!empty($type) && isset($GLOBALS['Ec2InstanceFactory'][$type])) {
            $className = $GLOBALS['Ec2InstanceFactory'][$type];
            $instance = new $className($apiData);
            if (!$instance instanceof Instance) {
                throw new \Exception('Invalid class');
            }
            return $instance;
        }
        return new Instance($apiData);
    }

    public static function extractValue($tagKey, array $entity)
    {
        if (!isset($entity['Tags'])) {
            return null;
        }
        $tags = $entity['Tags'];
        foreach ($tags as $tag) {
            if ($tag['Key'] === $tagKey) {
                return $tag['Value'];
            }
        }
        return null;
    }

}
