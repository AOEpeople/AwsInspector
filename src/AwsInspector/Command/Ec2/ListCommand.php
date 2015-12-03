<?php

namespace AwsInspector\Command\Ec2;

use AwsInspector\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('ec2:list')
            ->setDescription('List all instances')
            ->addArgument(
                'tag',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'tag (Example: "Environment=Deploy")'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tags = $input->getArgument('tag');

        $finder = new Finder();
        $rows = $finder->findEc2InstancesByTags($tags);

        $table = new \Symfony\Component\Console\Helper\Table($output);
        $table
            ->setHeaders(array_keys(end($rows)))
            ->setRows($rows)
        ;
        $table->render();
    }

}