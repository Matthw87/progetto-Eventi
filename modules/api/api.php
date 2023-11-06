<?php
use Marion\Core\{Marion,Module};
use Marion\Support\Form\Form;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
class Api extends Module
{

    function install(): bool{
       
        $res = parent::install();
        if( $res ){
            Marion::setConfig('api_configuration','token_duration',3600000);
            Marion::setConfig('api_configuration','swagger_enabled_modules',serialize(['api']));
        }
        return $res;
    }

    function uninstall(): bool{
        
        $res = parent::uninstall();
        if( $res ){
            Marion::delConfig('api_configuration','token_duration');
            Marion::delConfig('api_configuration','swagger_enabled_modules');
        }
        return $res;
    }
}