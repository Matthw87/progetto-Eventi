<?php

namespace Marion\Commands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ScssPhp\ScssPhp\Compiler;

class BuildCssCommand extends Command 
{
    protected function configure()
    {
        $this->setName('build:css')
        ->setDescription('Build css from scss file')
        ->setHelp('Build css from scss file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $list = scandir(_MARION_THEME_DIR_);
        
        $parameters = array(
            'BASE_URL' => _MARION_BASE_URL_,
            'THEME_DIR' => _MARION_BASE_URL_."themes/"._MARION_THEME_,
        );
        $string_base = '';
        foreach($parameters as $key => $value){
            $string_base .= '$'.$key.':"'.$value.'";';
        }
        foreach($list as $theme){
            if( $theme != '.' && $theme != '..' ){
                $scss = new Compiler();
                try{
                    $path_scss = _MARION_THEME_DIR_.$theme."/theme.scss";
                    $data_tmp = '';
                    if( file_exists($path_scss) ){
                        $data_tmp = file_get_contents($path_scss);
                        $data_tmp = $string_base.$data_tmp;
                        
                        $compressed = $scss->compileString($data_tmp);
                        
                        $destination = _MARION_THEME_DIR_.$theme."/theme.css";
                        file_put_contents($destination,$compressed->getCss());
                        $output->writeln('<info>'.$destination.'</info>');
                        //Console::success($destination);
                    } 
                    
                }catch( \Exception $e){
                    $output->writeln('<error>'.$e->getMessage().'</error>');
                    return 1;
                }
            }
        }
        return 0;

    }
    
}