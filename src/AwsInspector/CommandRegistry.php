<?php

namespace AwsInspector;


class CommandRegistry {

    public static function getCommands() {
        return [
            new \AwsInspector\Command\Profile\ListCommand(),
            new \AwsInspector\Command\Profile\EnableCommand(),
            new \AwsInspector\Command\Profile\DisableCommand(),
            new \AwsInspector\Command\Ec2\ListCommand(),
            new \AwsInspector\Command\Ec2\SshCommand(),
            new \AwsInspector\Command\Agent\AddIdentityCommand(),
            new \AwsInspector\Command\Graph\NetworkCommand()
        ];
    }

}