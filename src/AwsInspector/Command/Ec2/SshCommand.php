<?php

namespace AwsInspector\Command\Ec2;

use AwsInspector\Finder;
use Lib\AwsInspector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SshCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('ec2:ssh')
            ->setDescription('SSH into an ec2 instance')
            ->addArgument(
                'instance',
                InputArgument::REQUIRED,
                'Instance (IP address or instance id)'
            )
            ->addOption(
                'print',
                null,
                InputOption::VALUE_NONE,
                'Print ssh command instead of connecting'
            )
            ->addOption(
                'command',
                null,
                InputOption::VALUE_OPTIONAL,
                'Command'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $instance = $input->getArgument('instance');

        $finder = new Finder();
        $instanceData = $finder->findEc2Instance($instance);
        if ($instanceData === false) {
            throw new \Exception('Could not find instance');
        }

        $keyName = $instanceData['KeyName'];
        $ip = $instanceData['Public IP'];

        $identity = new \AwsInspector\Ssh\Identity('keys/'.$keyName.'.pem');
        $identity->loadIdentity();
        $path = $identity->getPrivateKeyFilePath();

        $descriptorSpec = array(
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        );
        $exec = 'ssh -o "UserKnownHostsFile /dev/null" -o "StrictHostKeyChecking no" -i ' . escapeshellarg($path) . ' ubuntu@'.$ip;

        $command = $input->getOption('command');
        if ($command) {
            $exec .= ' '.escapeshellarg($command);
        }

        if ($input->getOption('print')) {
            $output->writeln($exec);
            return;
        }

        $pipes = array();
        $process = proc_open($exec, $descriptorSpec, $pipes);
        if (is_resource($process)) {
            proc_close($process);
        }
    }

}