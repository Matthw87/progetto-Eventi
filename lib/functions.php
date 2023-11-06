<?php
use Marion\Core\Marion;
use Marion\Core\Translator;
use Marion\Entities\User;

function createIDform(){
	if( getIdForm() ){ 	
		return;
	}
	$request_uri = $_SERVER['REQUEST_URI'];
	
	$id = md5(uniqid(rand(), true)); 
	if( preg_match('/(.*)(\.php\?)(.*)/',$request_uri)){
		$request_uri.="&formID={$id}";
	}else{
		$request_uri.="?formID={$id}";
	}
	$url = $request_uri;
	header("Location:".$url);
	exit;
}

function getIdForm(){
	if(_var('formID')){ 	
		return _var('formID');
	}
	for( $i=1; $i<10; $i++ ){
		if(_var('formID'.$i)){ 	
			return _var('formID'.$i);
		}	
	}
	return false;
}

/**
 * Get params from $_POST or $_GET
 *
 * @param string $value
 * @return mixed
 */
function _var(string $value){
    if ( isset($_GET[$value]) ) {
    	$var = $_GET[$value];
    	return $var;
    }elseif( isset($_POST[$value]) ) {
    	$var = $_POST[$value];
   	 	return $var;
    }else{
        return false;
    }
}

/**
 * Print data 
 *
 * @param mixed $obj
 * @param string $label
 * @return void
 */
function debugga($obj,string $label=''){

	if( isset($obj) && !is_null($obj) && $obj !== false ){	
	    if( defined('_MARION_CONSOLE_') ){
			echo $label."\n";
			print_r($obj)."\n";
		}else{
			echo '<pre>';
			if( $label ){
				echo "<b>$".$label."</b></br>";
			}
			print_r($obj);
			echo '</pre>';
		}
		
	}else{
		if( defined('_MARION_CONSOLE_') ){
			echo $label."\n";
			print_r('$undefined')."\n";
		}else{
			if( $label ){
				echo "<b>$".$label."</b></br>";
				echo '$undefined';
			}else{
				echo '<b>$undefined</b>';
			}
		}
		
	
	}
}

/**
 * Check if valid array
 *
 * @param mixed $array
 * @return boolean
 */
function okArray($array): bool{
	if(!empty($array) && is_array($array) && count($array)>0){
		return true;
	}else{
		return false;
	}
}





if(!function_exists('mime_content_type')) {

	function mime_content_type($filename) {

		$mime_types = array(

			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);

		$ext = strtolower(array_pop(explode('.',$filename)));
		if (array_key_exists($ext, $mime_types)) {
			return $mime_types[$ext];
		}
		elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		}
		else {
			return 'application/octet-stream';
		}
	}
}

//restituisce l'utente se loggato sottoforma di oggetto
function getUser(): ?User{
	return Marion::getUser();
}

/**
 * Check if Ciro user
 *
 * @return boolean
 */
function isCiro(): bool{
	$user = Marion::getUser();
	if($user && $user->username == 'cironapo'){
		return true;
	}
	return false;
}


/**
 * Chekc if is logged
 *
 * @return boolean
 */
function authUser(): bool{
	$user = getUser();
	
	if( is_object($user) ){
		return true;
	}else{
		return false;
	}
}

function authUserNotLogged(){
	if( !authUser() && $_SESSION['sessionCart']['data']['password_not_logged']){
		return true;
	}else{
		return false;
	}
}

function authAdminUser(){
	$user = getUser();
	if(!is_object($user)) return false;
	return $user->authAdminUser();
}



/**
 * Check authentication
 *
 * @param string $type
 * @return boolean
 */
function auth(string $type): bool{
	return Marion::auth($type);
}


function isLocked(){
	return Marion::isLocked();
}




 /*
	Funzione:: getConfig
	Descrizione: prende il valore di configurazione per una specificata chaive ed etichetta. Questio dati sono memorizzati nel config.ini o letti in  read_config
	Input:
		$key :: chaive del gruppo di appartenenza
		$label :: etichetta del gruppo

*/
function getConfig(string $key,string $label=NULL){
	if( $label ){
		if( isset($GLOBALS['setting']['default'][strtoupper($key)][$label]) ){
			return $GLOBALS['setting']['default'][strtoupper($key)][$label];
		}else{
			return false;
		}
		
	}else{
		return $GLOBALS['setting']['default'][strtoupper($key)];
	}

}


 /*
	Funzione:: isMultilocale
	Descrizione: verifica se il sito è multilocale
	
*/
function isMultilocale(): bool{
	return Marion::isMultilocale();
}

 /*
	Funzione:: isMultilocale
	Descrizione: verifica se il sito è multilocale
	
*/
function isMulticurrency(){
	return Marion::isMulticurrency();
}

/**
 * Check if user is superadmin
 *
 * @return boolean
 */
function isDev(): bool{
	if( authUser() ){
		$user = getUser();
		
		return $user->auth('superadmin');
	}else{
		return false;
	}
}

/**
 * translate string
 *
 * @param string|array $string
 * @param string|null $module
 * @return string
 */
function _translate(
	$string,
	string $module=null
	): string{
	return Translator::translate($string,$module);
}


function _formdata($id=NULL,$name = 'formdata'){
    $formdata = _var($name);
	if( $formdata ){
		$formdata = parse_str($formdata, $params);
		if( $params ){
			$formdata = isset($params[$name.$id])?$params[$name.$id]:[];
		}else{
			return false;
		}
	}
    return $formdata;
}


function array_themes(){
	
	$list = scandir(_MARION_THEME_DIR_);
	$array[0] = 'TUTTI';
	if( okArray($list) ){
		foreach($list as $v){
			if( !in_array($v,array('.','..','admin','superadmin')) ){
				$array[$v] = strtoupper($v);		}
		}
	}
	ksort($array);
	return $array;
}



function _env($key,$default_value=null){
	
	if( array_key_exists($key, $_ENV) ) {
		$val = $_ENV[$key];
		if( $val == 'false') $val = false;
		if( $val == 'true') $val = true;
		return $val;
	}
	if( $default_value ) return $default_value;
	return null;
}

/**
 * Create e return dir on media dir
 *
 * @param string $path
 * @return string
 */
function media_dir($path=''): string{
	if($path ){
		$path_dir = _MARION_MEDIA_DIR_.'contents/'.$path;
		if( !file_exists($path_dir) ) {
			mkdir($path_dir,0755,true);
		}
		return $path_dir;
	}else{
		if( !file_exists(_MARION_MEDIA_DIR_.'/contents') ) {
			mkdir(_MARION_MEDIA_DIR_.'/contents');
		}
		return _MARION_MEDIA_DIR_.'/contents';
	}
	
}



?>