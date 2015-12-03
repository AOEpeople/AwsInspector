<?php

namespace AwsInspector\Ssh;

class Command {

    protected $username;
    protected $host;
    protected $command;

    public function __construct($username, $host, $command) {
        $this->username = $username;
        $this->host = $host;
        $this->command = $command;
    }

    public function __toString() {
        return sprintf(
            'ssh -o ControlPersist=yes -o ControlMaster=auto -S ~/mux_%%r@%%h:%%p -o LogLevel=QUIET -o StrictHostKeyChecking=no -A -t %s@%s %s',
            $this->username,
            $this->host,
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

    /**
     * Convenience method
     *
     * @param $hopUsername
     * @param $hopHost
     * @param $targetUsername
     * @param $targetHost
     * @param $command
     * @return mixed
     */
    public static function hopExec($hopUsername, $hopHost, $targetUsername, $targetHost, $command) {
        $target = new Command($targetUsername, $targetHost, $command);
        $hop = new Command($hopUsername, $hopHost, $target);
        return $hop->exec();
    }

}