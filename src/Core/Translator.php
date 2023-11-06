<?php
namespace Marion\Core;
class Translator{
    public static $translations = [];


    /**
     * Load translations message
     *
     * @param array $translations
     * @return void
     */
    public static function loadTranslations(array $translations): void{
        $_translations = [];
        if( okArray($translations) ){
            foreach($translations as $group => $trans){
                if( okArray($trans)){
                    foreach($trans as $key1 => $value1){
                        if(is_array($value1)){
                            foreach($value1 as $key2 => $value2){
                                if(is_array($value2)){
                                    foreach($value2 as $key3 => $value3){
                                        if(is_array($value3)){
                                            foreach($value3 as $key4 => $value4){
                                                if(is_array($value4)){
                                                    foreach($value4 as $key5 => $value5){
                                                        $_translations[$group][$key1.".".$key2.".".$key3.".".$key4.".".$key5] = $value5;
                                                    }
                                                }else{
                                                    $_translations[$group][$key1.".".$key2.".".$key3.".".$key4] = $value4;
                                                }
                                            }
                                        }else{
                                            $_translations[$group][$key1.".".$key2.".".$key3] = $value3;
                                        }
                                    }
                                }else{
                                    $_translations[$group][$key1.".".$key2] = $value2;
                                }
                            }
                        }else{
                            $_translations[$group][$key1] = $value1;
                        }
                    }
                }
            }
        }
        self::$translations = $_translations;
    }


    /**
     * Translate sstring
     *
     * @param [type] $key
     * @param string|null $module
     * @return string
     */
    public static function translate($key,string $module=null): string{
        if( $module ){
            if( array_key_exists($module,self::$translations) ){
                $translations = self::$translations[$module];
            }
            //debugga( self::$translations);exit;
        }else{
            $translations = array_key_exists('_default',self::$translations)?self::$translations['_default']:[];
            $shared_translations =  array_key_exists('_shared',self::$translations)?self::$translations['_shared']:[];
            if( $shared_translations ){
                $translations = array_merge($shared_translations,$translations);
            }
            
        }
        
        if( isset($translations) && okArray($translations) ){
            $params = array();
            if( is_array($key) ){
                //controllo se in input è stato passato un array
                if(okArray($key)){
                    foreach((array)$key as $k => $v){
                        if($k > 0){
                            $params[] = $v;
                        }
                    }
                    $key = $key[0];
                }
            }
            
            if( array_key_exists($key,$translations) ){
                $key = $translations[$key];
            }
           
            
            if(okArray($params)){
                return vsprintf($key,$params);
            }else{
                return $key;
            }
        }

        return (string)$key;

    }

}

?>