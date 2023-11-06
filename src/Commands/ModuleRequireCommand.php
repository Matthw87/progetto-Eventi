<?php

namespace Marion\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;


class ModuleRequireCommand extends Command 
{
    protected function configure()
    {
        $this->setName('module:require')
        ->setDescription('Download module')
        ->setHelp('This command download a module')
        ->addArgument('module', InputArgument::REQUIRED, 'Specific a module')
        ->addOption('token',null,InputOption::VALUE_REQUIRED,'authorization token')
        ->addOption('force-download',null,InputOption::VALUE_NONE,'force download');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

       
       
       
        $module_name = $input->getArgument('module');
        $force_download = $input->getOption('force-download');

        if( !$force_download && file_exists(_MARION_MODULE_DIR_.$module_name) || file_exists(_MARION_THEME_DIR_.$module_name)){
            $output->writeln('<comment>Module '.$module_name.' already downloaded</comment>');
            return 0;
        }
        $token = $input->getOption('token');
        
        $endpoint = 'http://api.repository.3d0.it/api/core/'._MARION_VERSION_.'/modules/'.$module_name;
        //debugga($endpoint);
        //header('Content-Type: application/json'); // Specify the type of data
        $ch = curl_init($endpoint); // Initialise cURL
       
        $authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
        //debugga($authorization);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $result = curl_exec($ch); // Execute the cURL statement
       
        curl_close($ch); // Close the cURL connection
        
        if( $result ){
            $response = json_decode($result,true); // Return the received data
            if( !$response ){
                $output->writeln('<error>Error download module '.$module_name.'</error>');
                return 1;
            }
            if( $response['code'] != 200 ){
                $output->writeln('<error>'.$response['error'].'</error>');
                return 1;
            }
            
            $data = base64_decode($response['data']['file']);
            
            $module_dir = 'modules';

            if( $response['data']['theme'] ){
                $module_dir = 'themes';
                $path = _MARION_THEME_DIR_.$module_name;
            }else{
                $path = _MARION_MODULE_DIR_.$module_name;
            }
            if( !file_exists($path)){
                mkdir($path);
            }
            $path = "{$module_dir}/{$module_name}.tar.gz";
            file_put_contents($path,$data);
            $process = Process::fromShellCommandline("tar -xf {$path} --directory $module_dir/$module_name");
            $process->run();
            $output->writeln('<info>Module successfully downloaded</info>');
            /*if (!$process->isSuccessful()) {
                //throw new ProcessFailedException($process);
            }*/
            unlink($path);
        }else{

        }
        return 0;
        
    }
    function getModuleClassName(string $string):string{
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        return $str;
    
    }

    function gzfile_get_contents($filename, $use_include_path = 0)
    {
        //File does not exist
        if( !@file_exists($filename) )
        {    return false;    }
    
        //Read and imploding the array to produce a one line string
        $data = gzfile($filename, $use_include_path);
        $data = implode($data);
        return $data;
    }
}