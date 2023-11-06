<?php
namespace Marion\Core;
class Storage{
    public static $expiration_time = (86400 * 30);

    public static function set($key,$value){
        if( _MARION_COOKIE_SESSION_ ){
			
			setcookie($key, json_encode($value), time() + self::$expiration_time,'/',$_SERVER['SERVER_NAME']); // 86400 = 1 day
			$_COOKIE[$key] = json_encode($value);
		}else{
			$_SESSION[$key] = json_encode($value);
		}
    }

    public static function unset($key){
        if( _MARION_COOKIE_SESSION_ ){
			setcookie($key, "", -1,'/',$_SERVER['SERVER_NAME']); // 86400 = 1 day
		}else{
			unset($_SESSION[$key]);
		}
        
    }

    public static function get($key){
        if( _MARION_COOKIE_SESSION_ ){
			return isset($_COOKIE[$key])?json_decode($_COOKIE[$key],true):null;
		}else{
			return isset($_SESSION[$key])?json_decode($_SESSION[$key],true):null;
		}
    }
}

?>