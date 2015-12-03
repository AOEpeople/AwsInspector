<?php

namespace AwsInspector\Ssh;

class Command {

    protected $command;
    protected $connection;

    public function __construct(Connection $connection, $command) {
        $this->connection = $connection;
        $this->command = $command;
    }

    public function __toString() {
        return sprintf(
            '%s %s',
            $this->connection,
            $this->command
        );
    }

    public function exec() {
        // file_put_contents('/tmp/exec.log', $this->__toString() . "\n", FILE_APPEND);
        $returnVar = null;
        exec($this->__toString(), $output, $returnVar);
        return [
            'output' => $output,
            'returnVar' => $returnVar
        ];
    }

    ///**
    // * Convenience method
    // *
    // * @param $hopUsername
    // * @param $hopHost
    // * @param $targetUsername
    // * @param $targetHost
    // * @param $command
    // * @return mixed
    // */
    //public static function hopExec($hopUsername, $hopHost, $targetUsername, $targetHost, $command) {
    //    $target = new Command($targetUsername, $targetHost, $command);
    //    $hop = new Command($hopUsername, $hopHost, $target);
    //    return $hop->exec();
    //}

}