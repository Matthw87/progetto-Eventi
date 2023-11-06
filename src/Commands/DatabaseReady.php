<?php

namespace Marion\Commands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\Capsule\Manager as DB;

class DatabaseReady extends Command 
{
    protected function configure()
    {
        $this->setName('db:ready')
        ->setDescription('Check if database is ready')
        ->setHelp('Check if database is ready');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeout = 100;
        $connection = DB::connection();
        do {
            try {
                $result = $connection->statement('SELECT TRUE;');
            } catch (\Exception $e) {
                --$timeout;
                // Once we timeout, we rethrow to enable diagnosing the issue
                if ($timeout <= 0) {
                    throw $e;
                }

                sleep(1);
            }
        } while (! isset($result));

        $output->writeln('<info>connection ok</info>');
        return 0;
        

    }
    
}