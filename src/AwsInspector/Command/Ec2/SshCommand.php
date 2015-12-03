<?php

namespace AwsInspector\Command\Ec2;

use AwsInspector\Finder;
use AwsInspector\Model\Ec2\Instance;
use AwsInspector\Model\Ec2\Repository;
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
            ->setDescription('SSH into an EC2 instance')
            ->addArgument(
                'instance',
                InputArgument::REQUIRED,
                'Instance (public or private IP address or instance id)'
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

        $repository = new Repository();
        $instance = $repository->findEc2Instance($instance);
        if (!$instance instanceof Instance) {
            throw new \Exception('Could not find instance');
        }

        $connection = $instance->getSshConnection();

        if ($command = $input->getOption('command')) {
            $commandObj = new \AwsInspector\Ssh\Command($connection, $command);
            if ($input->getOption('print')) {
                $output->writeln($commandObj->__toString());
                return 0;
            }
            $res = $commandObj->exec();
            $output->write($res['output']);
            return $res['returnVar'];
        }

        $connection->connect();
        return 0;
    }

}