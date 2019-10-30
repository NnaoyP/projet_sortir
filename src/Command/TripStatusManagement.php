<?php


namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TripStatusManagement extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'command:manage:trip:status';

    protected function configure() {
        $this->setDescription('Manage the status trip.')
            ->setHelp('This command allows you to manage status trip automatically');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container = $this->getApplication()->getKernel()->getContainer();
    }
}