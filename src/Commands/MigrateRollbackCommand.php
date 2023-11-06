<?php

namespace Marion\Commands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Illuminate\Database\Capsule\Manager as DB;

class MigrateRollbackCommand extends Command 
{
    protected function configure()
    {
        $this->setName('migrate:rollback')
        ->setDescription('Rollback last migration')
        ->setHelp('Rollback last migration')
        ->addOption(
            'm',
            null,
            InputOption::VALUE_OPTIONAL,
            'Module name',
            false // this is the new default value, instead of null
        );
        /*->addOption(
            'path',
            null,
            InputOption::VALUE_OPTIONAL,
            'Path',
            false // this is the new default value, instead of null
        );*/
        
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getOption('m');
        

        
        $query =  DB::table('migrations')->orderBy('id','desc');
        if( $module ){
            $query->where('module',$module);
        }else{
            $query->whereRaw("module IS NULL OR module = ''");
        }
    
        $last = $query->first();
        if( $last ){
            $file =  _MARION_ROOT_DIR_.'migrations/'.$last->name.".php";
            if( $module ){
                $file =  _MARION_ROOT_DIR_."modules/{$module}/migrations/".$last->name.".php";
            }
            if( !file_exists($file) ){
                $file =  _MARION_THEME_DIR_."{$module}/migrations/".$last->name.".php";
            }
            require_once($file);
            $classi = get_declared_classes();
            
            foreach($classi as $class ){
                if( (new \ReflectionClass($class))->getFileName() == $file ){
                    $obj = new $class($module);
                }
                
            }
            if( isset($obj) ){
                $output->writeln('<comment>'.$file.'</comment>');
                if( $obj->downgrade() ){
                    $output->writeln('<info>'.$file.'</info>');
                }
            }
            
        }

        return 0;
    }

}