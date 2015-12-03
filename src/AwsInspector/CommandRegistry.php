<?php

namespace AwsInspector;


class CommandRegistry {

    public static function getCommands() {
        return [
            new \AwsInspector\Command\Profile\ListCommand(),
            new \AwsInspector\Command\Profile\UseCommand()
        ];
    }

}