<?php

namespace Marion\Commands;
use Marion\Core\{Marion,Module};
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class ModuleListCommand extends Command 
{
    protected function configure()
    {
        $this->setName('module:list')
        ->setDescription('Shows list modules')
        ->setHelp('This command prints the module list')
        ->addOption(
            'type',
            null,
            InputOption::VALUE_OPTIONAL,
            'Filter by type',
            false // this is the new default value, instead of null
        )
        ->addOption(
            'search',
            null,
            InputOption::VALUE_OPTIONAL,
            'Search module',
            false // this is the new default value, instead of null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        Marion::read_config();
       
        $type = $input->getOption('type');
        $search = $input->getOption('search');
        
        $stored_modules = DB::table('modules')
                ->orderBy('tag')
                ->get(['tag as id','name','author as autore','kind as tipo','active'])
                ->toArray();
        $installed_modules = [];
        $actived_modules = [];
        foreach($stored_modules as $data_module){
            $data_module = (array)$data_module;
            $installed_modules[] = $data_module['id'];
            if( $data_module['active']){
                $actived_modules[]= $data_module['id'];
            }
            
        }
        $module_directories = scandir(_MARION_MODULE_DIR_);
       
        $modules = [];
        foreach($module_directories as $directory){
            if( $directory != '.' && $directory != '..'){
                
                if( file_exists(_MARION_MODULE_DIR_.$directory."/config.xml")){
                    $mod = new Module($directory);
                    $mod->readXML();
                    $info = $mod->config['info'];
                    
                    if( $type ){
                        if( $info['kind'] != $type ) continue;
                    }
                    if( $search ){
                        if( !preg_match("/{$search}/",$info['tag']) ) continue;
                    }
                    $modules[] = [
                        '' => in_array($info['tag'],$installed_modules)?'*':'',
                        'id' => $info['tag'],
                        'nome' => $info['name'],
                        'tipo' => $info['kind'],
                        'Attivo' => in_array($info['tag'],$actived_modules)?'Yes':'',
                    ];
                }
                
                
            }
            
        }
        $print = [];
        foreach($modules as $mod){
            $print[] = array_values($mod);
        }
        $table = new Table($output);
        $table
            ->setHeaders(['', 'ID', 'NAME','TYPE','ACTIVE'])
            ->setRows($print)
        ;
        $table->render();

        //$now = date('c');
        //$message = sprintf("Current date and time: %s", $now);

        //$output->writeln($message);
        return 0;
    }
}