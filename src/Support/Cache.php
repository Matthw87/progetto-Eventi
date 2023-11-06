<?php
namespace Marion\Support;
use Phpfastcache\Helper\Psr16Adapter;
use Phpfastcache\Config\ConfigurationOption;

class Cache{
    private static Psr16Adapter $instance;

    public static function isActive(): bool{
   
        return _env('CACHE_ACTIVE');
    }

    /**
     * set data in cache
     *
     * @param string $key
     * @param mixed $data
     * @return boolean
     */
    public static function set(string $key, $data): bool{
        $instance = self::getInstance();
        return $instance->set($key,$data,$_ENV['CACHE_LIFETIME']);
    }

    /**
     * check if key exists
     *
     * @param string $key
     * @return boolean
     */
    public static function exists(string $key): bool{
        $instance = self::getInstance();
        return $instance->has($key); 
    }

    /**
     * get data from cache by key
     *
     * @param string $key
     * @return mixed
     */
    public static function get(string $key){
        $instance = self::getInstance();
        return $instance->get($key);;
    }

    /**
     * return istance cache
     *
     * @return Psr16Adapter
     */
    public static function getInstance(): Psr16Adapter{
        if( isset(self::$instance) ) return self::$instance;
        $defaultDriver = 'Files';
        $Psr16Adapter = new Psr16Adapter($defaultDriver,new ConfigurationOption([
            'path' => _MARION_ROOT_DIR_.'cache', 
        ]));   
        self::$instance = $Psr16Adapter;
        return $Psr16Adapter;
    }
    /**
     * remove data in cache by key
     *
     * @param string $key
     * @return boolean
     */
    public static function remove(string $key): bool{
        $instance = self::getInstance();
        return $instance->delete($key);
    }
}
?>