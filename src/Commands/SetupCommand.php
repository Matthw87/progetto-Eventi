<?php

namespace Marion\Commands;
use Marion\Core\Marion;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SetupCommand extends Command 
{
    protected function configure()
    {
        $this->setName('setup')
        ->setDescription('Setup marion')
        ->setHelp('This command install the module from setup.xml')
        ->addArgument('token', InputArgument::OPTIONAL, 'Specific a token')
        ->addOption('skip-errors',null,InputOption::VALUE_NONE, false);
        //->addOption('prod', InputOption::VALUE_NONE, 'Production mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $token = $input->getArgument('token');
        $skip_errors = $input->getOption('skip-errors');
        
        Marion::read_config();
        $xml_file = _MARION_ROOT_DIR_."setup.xml";
        if( !file_exists($xml_file) ){
            $output->writeln('<error>Setup file (setup.xml) not exists</error>');
            return 1;
        }

         

        $data = simplexml_load_file($xml_file);
        $command_install = $this->getApplication()->find('module:install');
        $command_require = $this->getApplication()->find('module:require');
        foreach($data->module as $module){
            if( $token ){

            
                $require_arguments = [
                    'module'  => $module->name,
                    '--token'  => $token,
                ];
            

                $inputs = new ArrayInput($require_arguments);
                $returnCode = $command_require->run($inputs, $output);
                if( $returnCode ){
                    return 1;
                }
            }
            if( $token ){
                $install_arguments = [
                    'module'  => (string)$module->name,
                    '--resolve-dependencies' => true,
                    '--force-download' => true,
                    '--token' => $token,
                ];
            }else{
                $install_arguments = [
                    'module'  => (string)$module->name
                ];
            }
            
            if( isset($module->seed) && $module->seed ){
                $install_arguments['--seed'] = true;
            }
            //debugga($install_arguments);
           
    
            $inputs = new ArrayInput($install_arguments);
            $returnCode = $command_install->run($inputs, $output);
            if( !$skip_errors && $returnCode ){
                $output->writeln('<error>Error install '.$module->name.'</error>');
                return 1;
            }
        }
        return 0;
       
    }

}