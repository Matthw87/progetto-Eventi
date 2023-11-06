<?php
namespace Marion\Controllers;
use Marion\Controllers\Controller;
header('Access-Control-Allow-Origin: *');
header('Access-Control-Request-Method: *');
header("Access-Control-Allow-Headers: *");

define('_MISSING_JWT_',"JWT IS MISSING");

/** Controller per creare un servizio di tipo REST */
class ApiController extends Controller{
	public $_auth = ''; //permessi per accedere al controller
	public $_module;
    public $_jwt_check = false; //flag che stabilisce se occorre controllare il token JWT
    private $_jwt_string;
    private $_jwt_object;
    public $_log_path = ''; //path dei log. Se non specificato verranno stamapti nel percorso di default di apache
   

	
	public function init($options=array()){
		parent::init();
		$this->_module = _var('mod');
		
    }
    
    
    function __construct($options=array()){
		$this->init($options);
		
        $action = $this->getAction();
        if( $action == 'login' ){
            $this->login();
            exit;
        } 
        //se è abilitato il controllo del JWT
        if( $this->_jwt_check ){
            
            //controllo il token JWT
            $this->checkJWT();
        }
        
        //se 
        if( !$this->checkAccess()){
            // non sei autorizzato
            $this->error('RESOURCE_NOT_FOUND');
        }

        $this->process();
    }



    function process(){
        $action = $this->getAction();
        
        if( method_exists($this,$action) ){
            $this->$action();
        }
    
    }


    function login(){
        $data = $this->getRequestData();
        $user = \User::login($data['username'],$data['password']);
		if( is_object($user)){
            $this->_user = $user;
             
            $this->_jwt_string =  $this->_user->generateJWT();
			$this->success(
                $this->getDataUser($user)
			);
		}else{
			$this->error($user);
		}
    }
	

    function checkJWT(){
        $this->_jwt_string = $this->getBearerToken();
        if( $this->_jwt_string ){
            $user = \User::loginWithJWT($this->_jwt_string );
            if(is_object($user)){
                $this->_jwt_object = \Firebase\JWT\JWT::decode($this->_jwt_string , _MARION_JWT_KEY_, array('HS256'));
                $this->_user = $user;
                return true;
            }
            $this->error($user);
        }else{
            $this->error(_MISSING_JWT_);
        }
       
       
        
    }

    function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }else if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]);
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

    function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return ''.trim($matches[1]);
            }
        }
        return null;
    }


    //metodo che restituisce il successo in formato JSON
	function success($data){
        
		$data = array(
			'success' => 1,
            'data' => $data,
		);
		$this->send($data);

    }
    
    function send($data=array()){
        $jwtTemp = $this->_jwt_string;
		if( is_object($this->_jwt_object) ){
            if($this->_jwt_object["exp"] >= time('-10 minutes')){
                $jwtTemp = $this->_user->generateJWT();
            }
         }
		 $data['jwt'] = $jwtTemp;
       
		echo json_encode($data);
		exit;
    }


    function error($message=''){
		$data = array(
			'success' => 0,
			'error_message' => $message	
		);
		$this->send($data);
	}


    //dati che vengono restituiti dopo il login. Riceve in input un oggetto di tipo user
    function getDataUser($user){
        return array(
            'id' => $user->id,
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
            'username' => $user->username,
        );
    }

    // metodo che prende i dati di un post
	function getRequestData(){
		return  json_decode(file_get_contents('php://input'), true);
    }
    

    function log($data=null){
		if( $this->_log_path ){
			error_log(print_r($data,true), 3,$this->_log_path);
		}else{
			error_log(print_r($data,true));
		}
		
	}


    
    
   
    

    



	

}
?>