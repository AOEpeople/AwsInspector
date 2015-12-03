<?php

namespace AwsInspector;

class SdkFactory {

    protected static $sdk;

    /**
     * @return \Aws\Sdk
     */
    public static function getSdk()
    {
        if (is_null(self::$sdk)) {
            self::$sdk = new \Aws\Sdk([
                'version' => 'latest',
                'region' => getenv('AWS_DEFAULT_REGION')
            ]);
        }
        return self::$sdk;
    }

    /**
     * @param string $client
     * @return \Aws\AwsClientInterface
     * @throws \Exception
     */
    public static function getClient($client) {
        return self::getSdk()->createClient($client);
    }

}
