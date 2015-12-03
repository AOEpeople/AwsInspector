<?php

namespace AwsInspector\Ssh;

class Connection {

    protected $username;
    protected $host;
    protected $privateKey;

    public function __construct($username, $host, PrivateKey $privateKey=null) {
        $this->username = $username;
        $this->host = $host;
        $this->privateKey = $privateKey;
    }

    public function __toString() {
        return sprintf(
            'ssh %s -o ControlPersist=yes -o ControlMaster=auto -S ~/mux_%%r@%%h:%%p -o LogLevel=QUIET -o StrictHostKeyChecking=no -A -t %s@%s',
            $this->privateKey ? '-i ' .$this->privateKey->getPrivateKeyFile() : '',
            $this->username,
            $this->host
        );
    }

    public function exec($command) {
        $command = new Command($this, $command);
        return $command->exec();
    }

    public function connect() {
        $descriptorSpec = array(
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        );
        $pipes = array();
        $process = proc_open($this->__toString(), $descriptorSpec, $pipes);
        if (is_resource($process)) {
            proc_close($process);
        }
    }



}