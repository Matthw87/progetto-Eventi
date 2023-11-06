<?php 
namespace Api\Controllers;

use Api\ApiKey;
use Marion\Controllers\FrontendController;
Class Controller extends FrontendController{


    /**
	 * Metodo che verifica se l'utente è abilitato ad accedere al controller
	 *
	 * @return boolean
	 */
	function checkAccess(): bool{
        $api_key = $this->getApiKey();
        //debugga($api_key);exit;
        if( !$api_key ){
            
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode([
                'data' => "API-KEY IS NOT VALID",
                'code' => 401
            ]);
            exit;
        }else{
            if( isset($this->_module) && $this->_module ){
                if( !in_array($this->_module,$api_key->enabled_modules) ){
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(403);
                    echo json_encode([
                        'data' => "Not authorized",
                        'code' => 403
                    ]);
                    exit;
                }
            }
            
        }
        return parent::checkAccess();
	}



    /** 
     * Get Api key Authorization
     * */
    public function getApiKey(): ?ApiKey{
        $headers = getAllheaders();
        if( isset($headers['Api-Key'])) $key = $headers['Api-Key'];
        if( !isset($key)) return null;
        return ApiKey::prepareQuery()->where('api_key',$key)->where('active',1)->getOne();
    }

}