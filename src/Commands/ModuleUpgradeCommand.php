<?php

namespace Marion\Commands;
use Marion\Core\Marion;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;

class ModuleUpgradeCommand extends Command 
{
    protected function configure()
    {
        $this->setName('module:upgrade')
        ->setDescription('Upgrade module')
        ->setHelp('This command upgrade a module')
        ->addArgument('module', InputArgument::REQUIRED, 'Specific a module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

       
       
        Marion::read_config();
        $module_name = $input->getArgument('module');
        if( file_exists(_MARION_MODULE_DIR_.$module_name) || file_exists(_MARION_THEME_DIR_.$module_name)){
           
            if( DB::table('modules')->where('tag',$module_name)->exists() ){
                if( file_exists(_MARION_MODULE_DIR_.$module_name) ){
                    require_once(_MARION_MODULE_DIR_.$module_name."/{$module_name}.php");    
                }else{
                    require_once(_MARION_THEME_DIR_.$module_name."/{$module_name}.php");    
                }
                
                $name_class = $this->getModuleClassName($module_name);
                $module = new $name_class($module_name);
                $module->readXML();

               
                
                
               
                $res = $module->isUpgradable();
                if( !$res ){
                    $output->writeln('<error>no new version found</error>');
                    return 1;
                }

                $command = $this->getApplication()->find('migrate');

                $arguments = [
                    '--m'  => $module_name,
                ];


                
        
                $inputs = new ArrayInput($arguments);
                $returnCode = $command->run($inputs, $output);
                if( !$returnCode ){
                    $module->upgrade();
                    $new_version_string = (string)$module->config_xml->info->version;
                    $output->writeln('<info>'.'Module successfully upgraded. New version '.$new_version_string.'<info>');
                    return 0;
                }
                return 1;
               
               
            }else{
                $output->writeln('<error>'.'Module not installed'.'<error>');
                return 1;
            }
        }else{
            $output->writeln('<error>'.'Module not exists'.'<error>');
            return 1;
        }
        return 0;
    }
    function getModuleClassName(string $string):string{
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        return $str;
    
    }
}