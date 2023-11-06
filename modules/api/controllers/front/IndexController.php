<?php

use Marion\Controllers\FrontendController;
use Marion\Core\Marion;
use OpenApi\Generator;
class IndexController extends FrontendController{
    
    function json(string $module): void{
       
        spl_autoload_register('marion_api_autoloader');
        $GLOBALS['_opeapi_module'] = $module;
        //debugga(Marion::$modules);exit;

        $enabled_modules = unserialize(Marion::getConfig('api_configuration','swagger_enabled_modules'));
        if( !in_array($module,$enabled_modules) ){
            header('Location: /index.php');
            die();
        }
        
        $directories = [];
        $directories[] = _MARION_MODULE_DIR_."api/controllers/openapi/";
        $directories[] = _MARION_MODULE_DIR_.$module."/controllers/front/";
        //debugga($directories);exit;
        $openapi = Generator::scan($directories);
        $openapi->info->title = "$module API documentation";

        Marion::do_action('action_override_api_swagger',[$module,$openapi]);
        header('Content-Type: application/json');
        echo $openapi->toJSON();
    }

    function swagger(?string $module): void{
        if( !$module ) $module='api';

        $enabled_modules = unserialize(Marion::getConfig('api_configuration','swagger_enabled_modules'));
        if( !in_array($module,$enabled_modules) ){
            header('Location: /index.php');
            die();
        }
        //debugga($module);exit;
        $this->setVar('module_json',$module);
        $this->output('@api/index.html');
    }
}

function marion_api_autoloader(string $name) {
    $module = $GLOBALS['_opeapi_module'];
    $file = _MARION_MODULE_DIR_."/".$module."/controllers/front/{$name}.php";
    
    
   
    
    if( file_exists(_MARION_MODULE_DIR_."/api/controllers/openapi/{$name}.php") ){
        require_once _MARION_MODULE_DIR_."/api/controllers/openapi/{$name}.php";
    }

    if( file_exists(_MARION_THEME_DIR_."/modules/{$module}/controllers/{$name}.php") ){
        require_once _MARION_THEME_DIR_."/modules/{$module}/controllers/{$name}.php";
    }else{
        if( file_exists(_MARION_MODULE_DIR_."/".$module."/controllers/front/{$name}.php") ){
            if( $file != _MARION_MODULE_DIR_."/api/controllers/front/IndexController.php"){
                require_once _MARION_MODULE_DIR_."/".$module."/controllers/front/{$name}.php";
            }
        }
    }
    
  
  
    
}
?>