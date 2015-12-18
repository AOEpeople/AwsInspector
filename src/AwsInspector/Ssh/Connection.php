<?php

namespace AwsInspector\Ssh;
use AwsInspector\Registry;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Connection
 *
 * @package AwsInspector\Ssh
 *
 * @author Fabrizio Branca
 */
class Connection
{

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var PrivateKey
     */
    protected $privateKey;

    /**
     * @var bool
     */
    protected $multiplex;

    /**
     * @var \AwsInspector\Model\Ec2\Instance
     */
    protected $jumpHost;

    /**
     * Connection constructor.
     *
     * @param $username
     * @param $host
     * @param PrivateKey|null $privateKey
     * @param \AwsInspector\Model\Ec2\Instance|null $jumpHost
     * @param bool $multiplex
     */
    public function __construct($username, $host, PrivateKey $privateKey = null, \AwsInspector\Model\Ec2\Instance $jumpHost = null, $multiplex=false)
    {
        $this->username = $username;
        $this->host = $host;
        $this->privateKey = $privateKey;
        $this->jumpHost = $jumpHost;
        $this->multiplex = $multiplex;
    }

    public function __toString()
    {
        $parts = ['ssh'];

        if ($this->privateKey) {
            $parts[] = '-i ' . $this->privateKey->getPrivateKeyFile();
        }

        if (!is_null($this->jumpHost)) {
            if ($output = Registry::get('output')) { /* @var $output OutputInterface */
                $output->writeln("[Using jump host: " . $this->jumpHost->getDefaultUsername() . '@' . $this->jumpHost->getPublicIpAddress() . "]");
            }
            $proxyCommand = new Command($this->jumpHost->getSshConnection(), 'nc %h %p');
            $parts[] = '-o ProxyCommand="' . $proxyCommand->__toString() . '"';
        }

        if ($this->multiplex) {
            // $parts[] = '-o ControlPersist=yes -o ControlMaster=auto -S ~/mux_%%r@%%h:%%p';
            $parts[] = '-o ControlPersist=yes -o ControlMaster=auto -S ~/mux_'.$this->username.'@'.$this->host.':22';
        }

        $parts[] = '-o ConnectTimeout=5';
        $parts[] = '-o LogLevel=QUIET';
        $parts[] = '-o StrictHostKeyChecking=no';
        $parts[] = '-t'; // Force pseudo-tty allocation.
        $parts[] = "{$this->username}@{$this->host}";

        return implode(' ', $parts);
    }

    /**
     * Execute command on this connection
     *
     * @param string $command
     * @return array
     */
    public function exec($command)
    {
        $command = new Command($this, $command);
        return $command->exec();
    }

    /**
     * Interactive connection
     */
    public function connect()
    {
        $descriptorSpec = [0 => STDIN, 1 => STDOUT, 2 => STDERR];
        $pipes = [];
        $process = proc_open($this->__toString(), $descriptorSpec, $pipes);
        if (is_resource($process)) {
            proc_close($process);
        }
    }


}