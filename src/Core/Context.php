<?php
namespace Marion\Core;
/**
 * @method static string getLang()
 * @method static array getModules() 
 * @method static \Marion\Controllers\Controller getController()
 * @method static \Marion\Router\Route getRoute()
 */
class Context{
    private static $_keys = [];

    public static function set(string $key,$value){
        if( $key === strtolower($key) ){
            self::$_keys[$key] = $value;
        }else{
            throw new \Exception($key.": context's key must be lower case");
        }
        
    }

    public static function get(string $key): mixed{
        if( array_key_exists($key,self::$_keys) ){
            return self::$_keys[$key];
        }else{
            throw new \Exception($key. " not exists in context's keys");
        }
    }

    /**
     * Get context lang
     *
     * @return string
     */
    public static function getLang(): string{
        if( array_key_exists('lang',self::$_keys) ){
            return self::$_keys['lang'];
        }else{
            return 'it';
        }
    }

    public static function __callStatic(string $name , array $arguments){
        if( preg_match('/^get/',$name) ){
            
            preg_match_all('/((?:^|[A-Z])[a-z]+)/',$name,$matches);
            $strings = $matches[1];
            unset($strings[0]);
            $key = '';
            if( count($strings) > 0 ){
                foreach($strings as $s){
                    $s = strtolower($s);
                    $key .= "{$s}_";
                }
                $key = preg_replace('/_$/','',$key);
            }
            return self::get($key);
            
        }
        throw new \Exception("method not exists in Context class");
    }

    /**
     * Return all defined key in context
     *
     * @return array
     */
    public static function getKeys(): array{
        return array_keys(self::$_keys);
    }


    /**
     * Return all context values
     *
     * @return array
     */
    public static function getAll(): array{
        return self::$_keys;
    }


}