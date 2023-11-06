<?php

namespace Marion\Commands;
use Marion\Core\{Marion,Module};
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;

class ModuleInstallCommand extends Command 
{
    protected function configure()
    {
        $this->setName('module:install')
        ->setDescription('Install module')
        ->setHelp('This command install a module')
        ->addOption('resolve-dependencies',null,InputOption::VALUE_NONE)
        ->addOption('force-download',null,InputOption::VALUE_NONE)
        ->addOption('seed',null,InputOption::VALUE_NONE)
        ->addOption('token',null,InputOption::VALUE_OPTIONAL,'authorization token')
        ->addArgument('module', InputArgument::REQUIRED, 'Specific a module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $install_dependencies = $input->getOption('resolve-dependencies');
        $force_download = $input->getOption('force-download');
        $seed = $input->getOption('seed');
        $token = $input->getOption('token');
        
        if( $force_download && !$token){
            $output->writeln('<error>for to use --force-download option you must specify the authorization token with --token option</error>');
            return 1;
        }
       

        
        Marion::read_config();
        $module_name = $input->getArgument('module');
        if( file_exists(_MARION_MODULE_DIR_.$module_name) || file_exists(_MARION_THEME_DIR_.$module_name) || $force_download){
            if( !file_exists(_MARION_MODULE_DIR_.$module_name) && !file_exists(_MARION_THEME_DIR_.$module_name) ){
                $output->writeln('<comment>Module '.$module_name.' not exists locally. Try to download it from remote repository...<comment>');
                $command_require = $this->getApplication()->find('module:require');
                $require_arguments = [
                    'module'  => $module_name,
                    '--token'  => $token,
                ];
                $inputs = new ArrayInput($require_arguments);
                $returnCode = $command_require->run($inputs, $output);
               
                if( $returnCode ){
                    return 1;
                }
            }
            if( !DB::table('modules')->where('tag',$module_name)->exists() ){
                $is_theme = false;
                if(file_exists(_MARION_MODULE_DIR_.$module_name)){
                    require_once(_MARION_MODULE_DIR_.$module_name."/{$module_name}.php");
                }else{
                    $is_theme = true;
                    require_once(_MARION_THEME_DIR_.$module_name."/{$module_name}.php");
                }
               
                
                $name_class = $this->getModuleClassName($module_name);
                $module = new $name_class($module_name);
                $module->readXML();
                

                if( $install_dependencies ){
                    //verifico se le dipenendenze sono installate
                    $dependencies = $module->getDependencies();
                    if( okArray($dependencies) ){
                        $command = $this->getApplication()->find('module:install');

                        foreach($dependencies as $_dependence){
                            $dependence = $_dependence['module'];

                            if( Module::isInstalledModule($dependence) ){
                                if( !Module::isActivatedModule($dependence)){
                                    require_once(_MARION_MODULE_DIR_.$dependence."/{$dependence}.php");
                                    $dependence_name_class = $this->getModuleClassName($dependence);
                                    $dependence_module = new $dependence_name_class($dependence);
                                    $dependence_module->active();
                                }
                            }else{
                                $arguments = [
                                    'module' => $dependence,
                                    '--resolve-dependencies' => true
                                ];
                                if( $force_download ){
                                    $arguments['--force-download'] = true;
                                    $arguments['--token'] = $token;
                                }
                                $output->writeln('<comment>Module '.$module_name.' depends from module '.$dependence.'. Try to install it...<comment>');
                                $inputs = new ArrayInput($arguments);
                                $returnCode = $command->run($inputs, $output);
                                
                                if( $returnCode ){
                                    return 1;
                                }
                            }
                            
                        }
                    }
                }
               
                $module->install();
                
               
                if( $module->errorMessage ){
                    $output->writeln('<error>'.$module->errorMessage.'</error>');
                    return 1;
                }else{
                    $autoload = false;
                    if( isset($module->config_xml->info->autoload) ){
                        $autoload = (bool)$module->config_xml->info->autoload;
                    }
                    if( $autoload ){
                        if( file_exists(_MARION_MODULE_DIR_.$module_name."/vendor/autoload.php") ){
                           require_once(_MARION_MODULE_DIR_.$module_name."/vendor/autoload.php");
                        }
                    }
                    

                   
                    
                    $command = $this->getApplication()->find('migrate');

                    $arguments = [
                        '--m'  => $module_name,
                    ];
                    $inputs = new ArrayInput($arguments);
                    $returnCode = $command->run($inputs, $output);
                    if( !$returnCode ){
                        if( $seed ){
                            $command_seeder = $this->getApplication()->find('module:seed');
                            $require_seeder_arguments = [
                                'module' => $module_name,
                            ];
                            ///debugga($require_seeder_arguments);
                            $inputs = new ArrayInput($require_seeder_arguments);
                            $returnCode = $command_seeder->run($inputs, $output);
                            if( $returnCode ){
                                return 1;
                            }
                            //$module->seeder();
                        }
                        $output->writeln('<info>Module '.$module_name.' successfully installed</info>');
                    }
                    
                }
            }else{
                $output->writeln('<comment>Module '.$module_name.' already installed'.'<comment>');
                return 1;
            }
        }else{
            $output->writeln('<error>Module '.$module_name.' not exists. Try --force-download'.'<error>');
            return 1;
        }
        return 0;
    }
    function getModuleClassName(string $string):string{
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        return $str;
    
    }
}