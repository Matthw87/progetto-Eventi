<?php

namespace Marion\Commands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\Helper\Table;

class MigrationListCommand extends Command 
{
    protected function configure()
    {
        $this->setName('migrate:list')
        ->setDescription('List migrations')
        ->setHelp('Print list migrations')
        ->addOption(
            'all',
            null,
            InputOption::VALUE_NONE,
            'display all migrations'
        )
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
        
        $all = $input->getOption('all');
        //$migrations_path = _MARION_ROOT_DIR_."/migrations";

        $migrations_paths = [];
        if( $all ){
            
            foreach(scandir(_MARION_MODULE_DIR_) as $m){
                if( $m != '..' && $m != '.' && file_exists(_MARION_ROOT_DIR_."modules/{$m}/migrations")){
                    $migrations_paths[$m] = _MARION_ROOT_DIR_."modules/{$m}/migrations";
                }
            }
            $migrations_paths['core'] = _MARION_ROOT_DIR_."migrations";
        }else{
            if( $module ){
                $migrations_paths[$module] = _MARION_ROOT_DIR_."modules/{$module}/migrations";
            }else{
                $migrations_paths['core'] = _MARION_ROOT_DIR_."migrations";
            }
        }
        
        
        if( !okArray($migrations_paths)){
            return 0;
        }

        $migration_files = [];
        foreach($migrations_paths as $mod => $migrations_path){
            $migration_files[$mod] = scandir($migrations_path);
        }
        debugga($migration_files);
       
    
        
        
        foreach($migration_files as $mod => $migrations){
            foreach($migrations as $migration){
                if( is_file($migrations_path.'/'.$migration)){
                    $migration_name = explode('.',$migration)[0];
                    $migrations_list[$mod."-".$migration_name] = [
                        'name' => $migration_name,
                        'module' => $mod,
                        'timestamp' => ''
                    ];
                }
            }
            
        }

        debugga($migrations_list);
        return 0;
       
        $get_migrations_query =  DB::table('migrations')->orderBy('timestamp','asc');
        if( !$all ){
            if( $module ){
                $get_migrations_query->where('module',$module);
            }else{
                $get_migrations_query->whereNull('module');
            }
        }
        $stored_migrations = $get_migrations_query->get();
       
        foreach($stored_migrations as $v){
            $migrations_list[$v->name] = [
                'name' => "{$v->name}",
                'module' => $v->module,
                'timestamp' => $v->timestamp
            ];
        }

        $print = [];
        foreach($migrations_list as $mod){
            $print[] = array_values($mod);
        }
        $table = new Table($output);
        $table
            ->setHeaders(['NAME','MODULE','TIMESTAMP'])
            ->setRows($print)
        ;
        $table->render();
        

        return 0;








        
        $query =  DB::table('migrations')->orderBy('id','desc');
        if( $module ){
            $query->where('module',$module);
        }else{
            $query->whereNull('module');
        }
    
        $last = $query->first();
        if( $last ){
            $file =  _MARION_ROOT_DIR_.'migrations/'.$last->name.".php";
            if( $module ){
                $file =  _MARION_ROOT_DIR_."modules/{$module}/migrations/".$last->name.".php";
            }
            require_once($file);
            $classi = get_declared_classes();
            $last = $classi[count($classi)-1];
            $obj = new $last();
            $output->writeln('<comment>'.$file.'</comment>');
            if( $obj->downgrade() ){
                $output->writeln('<info>'.$file.'</info>');
            }
        }

        return 0;
    }

}