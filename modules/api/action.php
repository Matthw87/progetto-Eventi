<?php

use Marion\Core\Marion;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Marion\Core\Context;
use Marion\Entities\User;

function api_action_after_init(){
    $token = api_get_bearer_token();
    if( $token ){
        $key = 'MARION_API';
        try{
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            if( $decoded ){
                $user = User::withId($decoded->id);
                Marion::setUser($user, false);
            }
        }catch( Exception $e){
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode([
                'data' => "JWT error",
                'code' => 401
            ]);
            exit;
        }
        
    }
}

Marion::add_action('action_after_init','api_action_after_init');


function api_action_override_set_language(){
    if( isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] == 'application/json') {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $_SERVER['HTTP_ACCEPT_LANGUAGE']) {
            if( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ){
                Context::set('lang',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
            }
            
        }
    }
    
   
}

Marion::add_action('action_override_set_language','api_action_override_set_language');



/** 
 * Get header Authorization
 * */
function api_get_authorization_header(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

/**
 * get access token from header
 * */
function api_get_bearer_token() {
    $headers = api_get_authorization_header();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}


?>