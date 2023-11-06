<?php

namespace Marion\Commands;
use Marion\Core\Marion;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ModuleEnableCommand extends Command 
{
    protected function configure()
    {
        $this->setName('module:enable')
        ->setDescription('Active module')
        ->setHelp('This command active a module')
        ->addArgument('module', InputArgument::REQUIRED, 'Specific a module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

       
       
        Marion::read_config();
        $module_name = $input->getArgument('module');

        if( file_exists(_MARION_MODULE_DIR_.$module_name) || file_exists(_MARION_THEME_DIR_.$module_name) ){
           
            if( DB::table('modules')->where('tag',$module_name)->exists() ){

                if(file_exists(_MARION_MODULE_DIR_.$module_name)){
                    require_once(_MARION_MODULE_DIR_.$module_name."/{$module_name}.php");
                }else{
                    $is_theme = true;
                    require_once(_MARION_THEME_DIR_.$module_name."/{$module_name}.php");
                }
                $name_class = $this->getModuleClassName($module_name);
                $module = new $name_class($module_name);
                $module->readXML();
                
               
                $module->active();
                if( $module->errorMessage ){
                    $output->writeln('<error>'.$module->errorMessage.'</error>');
                    return 1;
                }else{
                    $output->writeln('<info>Module successfully enabled</info>');
                }
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