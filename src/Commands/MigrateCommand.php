<?php

namespace Marion\Commands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class MigrateCommand extends Command 
{
    protected function configure()
    {
        $this->setName('migrate')
        ->setDescription('Run migrations')
        ->setHelp('Run migrations')
        ->addOption(
            'm',
            null,
            InputOption::VALUE_OPTIONAL,
            'Module name',
            false // this is the new default value, instead of null
        )
        ->addOption(
            'path',
            null,
            InputOption::VALUE_OPTIONAL,
            'Path',
            false // this is the new default value, instead of null
        );
        
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('migrate');
        $module = $input->getOption('m');
        if( $module ){
            $module = (string)$module;
        }
        $path = $input->getOption('path');
        if( !DB::schema()->hasTable('migrations') ){
            DB::schema()->create("migrations",function(Blueprint $table){
                $table->id(); 
                $table->string("name",200);
                $table->string("module",200)->nullable(true);
                $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));

            });
        }
        $migrations = DB::table('migrations')->get()->toArray();
        $stored = [];
        if(okArray($migrations)){
            foreach($migrations as $v){
                $stored[$v->module?$v->module:'core'][] = $v->name;
            }
        }
        if( $path ){
            
            if( file_exists($path) ){
                include_once($path);
                $classi = get_declared_classes();
                //debugga($classi);
                foreach($classi as $class ){
                    if( (new \ReflectionClass($class))->getFileName() == $path ){
                        $obj = new $class($module);
                    }
                    
                }
               if( is_object($obj) ){
                    if( $obj->upgrade() ){
                   
                    }
               }
                
                
            }
        }else{
            if( $module ){
                if( file_exists(_MARION_ROOT_DIR_."modules/{$module}/migrations") ){
                    $directory = _MARION_ROOT_DIR_."modules/{$module}/migrations";
                }elseif(file_exists(_MARION_THEME_DIR_."{$module}/migrations")){
                    $directory = _MARION_THEME_DIR_."{$module}/migrations";
                }
               
            }else{
                $directory = _MARION_ROOT_DIR_.'migrations';
            }
           
            /*if( !file_exists($directory) ){
                $directory = _MARION_THEME_DIR_._MARION_THEME_.'/migrations';
            }*/
            if( isset($directory) && file_exists($directory) ){
                $scandir = scandir($directory);
                foreach($scandir as $v){
                    

                    $file =  $directory.'/'.$v;
                   
                    if( is_file($file)){
                        $_module = $module?$module: 'core';
                        
                        if( array_key_exists($_module,$stored) ){
                            if( in_array(preg_replace('/\.php$/','',$v),$stored[$_module]) ){
                                continue;
                            }
                        }
                        $arguments = [
                            '--path'  => $file,
                        ];

                        if( $module ){
                            $arguments['--m'] = $module;
                        } 
                        
                        $output->writeln('<comment>'.$file.'</comment>');
                        $inputs = new ArrayInput($arguments);
                        $returnCode = $command->run($inputs, $output);
                        if( $returnCode ){
                            $output->writeln('<error>'.$file.'</error>');
                            return $returnCode;
                        }else{
                            $output->writeln('<info>'.$file.'</info>');
                        }
                    }
                }
            }
        }

        return 0;
    }

}