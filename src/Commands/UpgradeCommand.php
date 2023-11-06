<?php

namespace Marion\Commands;
use Marion\Core\Marion;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class UpgradeCommand extends Command 
{
    protected function configure()
    {
        $this->setName('upgrade')
        ->setDescription('Upgrade all modules')
        ->setHelp('This command upgrade all modules');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        Marion::read_config();
        $modules = DB::table('modules')->get();
        
        foreach($modules as $m){
            $module_name = $m->directory;
            $command = $this->getApplication()->find('module:upgrade');
            $arguments = [
                'module'  => $module_name
            ];
           
            $inputs = new ArrayInput($arguments);
            $output->writeln('<info>'.'I\'m trying to upgrade the '.$module_name.' module<info>');
            $returnCode = $command->run($inputs, $output);
        }
        return 0;
    }
}