<?php

namespace Marion\Commands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationCreateCommand extends Command 
{
    protected function configure()
    {
        $this->setName('make:migration')
        ->setDescription('Create new migration file')
        ->setHelp('This command create ne migration file')
        ->addOption(
            'm',
            null,
            InputOption::VALUE_OPTIONAL,
            'Module name',
            false // this is the new default value, instead of null
        )
        ->addArgument('name', InputArgument::REQUIRED, 'Specific a module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

       $migration_name = $input->getArgument('name');
       $module = $input->getOption('m');
       
       

        if( preg_match('/[\_\-0-9%\/^!#\@\s]/',$migration_name) ){
            $output->writeln('<error>'."Errore: il nome della migration deve essere camelCase. Non può includere caratteri speciali e numeri.".'<error>');
            return 1;
        }
        
        
        /*foreach($argv as $p){
            if( preg_match('/m:/',$p)){
                $module = preg_replace('/m:/','',$p);
                break;
            }
            if( preg_match('/--module=/',$p)){
                $module = preg_replace('/--module=/','',$p);
                break;
            }
        }*/
        
        $tmp_migration_name = $migration_name;
        $tmp_migration_name = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $tmp_migration_name));
        $name_file = date('Y_m_d_H_i_s',time())."_".$tmp_migration_name.".php";
        
        $path_migration = '';
       
        if( $module ){
            if( !file_exists(_MARION_MODULE_DIR_.$module) && !file_exists(_MARION_THEME_DIR_.$module) ){
                $output->writeln('<error>'."module not exists".'<error>');
                return 1;
            }else{
                if( file_exists(_MARION_MODULE_DIR_.$module) ){
                    if( !file_exists(_MARION_MODULE_DIR_.$module."/migrations") ){
                        mkdir(_MARION_MODULE_DIR_.$module."/migrations");
                    }
                    $path_migration = _MARION_MODULE_DIR_.$module."/migrations/{$name_file}";
                }

                if( file_exists(_MARION_THEME_DIR_.$module) ){
                    if( !file_exists(_MARION_THEME_DIR_.$module."/migrations") ){
                        mkdir(_MARION_THEME_DIR_.$module."/migrations");
                    }
                    $path_migration = _MARION_THEME_DIR_.$module."/migrations/{$name_file}";
                }

                
            }
        }else{
            if( !file_exists(_MARION_ROOT_DIR_."migrations") ){
                mkdir(_MARION_ROOT_DIR_."migrations");
                //Console::success(_MARION_ROOT_DIR_."migrations");
            }
            $path_migration = _MARION_ROOT_DIR_."migrations/{$name_file}";
        }
        
        if( file_exists($path_migration) ){
            $output->writeln('<error>'."{$path_migration} exists".'<error>');
            return 1;
        }else{
            $data = "<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Marion\Core\Migration;
class {$migration_name}Migration extends Migration{
            
    public function up(){
        //to do
    }
    
    public function down(){
        //to do
    }
}
?>";
        file_put_contents($path_migration,$data);
        $output->writeln('<info>'.$path_migration.'</info>');
        }    
        return 0;
    }
    
}