<?php
namespace Marion\Core;
use Illuminate\Database\Capsule\Manager as DB;

/*********************************************************
Classe che si occupa di installare e disinstallare un modulo
*********************************************************/
class Theme extends Module{

    protected $_modules_path;
	
	function __construct($path=null){
		$this->directory_module = $path;
		$this->_modules_path = _MARION_THEME_DIR_;
	}

    function insertModule(){
		$data = $this->config;
		$info = $data['info'];
		
		if( array_key_exists('scope',$info) && is_array($info['scope']) ) unset($info['scope']);
		
		$info['active'] = 1;
        $info['theme'] = 1;
		$info['directory'] = $this->directory_module;
		
		
        $id = DB::table('modules')->insertGetId($info);
        $this->config_xml->info->id = $id;
		
        $this->disableOtherThemes();
        Marion::setConfig('theme_setting','active',$this->directory_module);
        Marion::refresh_config();
	}

    /**
	 * metodo che attiva il modulo
	 *
	 * @return boolean
	 */
	function active(): bool{
        parent::active();
        $this->disableOtherThemes();
        Marion::setConfig('theme_setting','active',$this->directory_module);
        Marion::refresh_config();
		return true;
	}

     /**
	 * metodo che attiva il modulo
	 *
	 * @return boolean
	 */
	function disable(): bool{
        parent::disable();
        Marion::setConfig('theme_setting','active','');
        Marion::refresh_config();
		return true;
	}

    /**
     * disabilita gli altri moduli
     *
     * @return void
     */
    private function disableOtherThemes(): void{
        $other_themes = DB::table('modules')
        ->where('theme',true)
        ->where('active',true)
        ->where('directory','<>',$this->directory_module)
        ->pluck('directory')
        ->toArray();
        
    if( okArray($other_themes) ){
        //disabilito gli altri moduli
        foreach( $other_themes as $module) {
            $file = $this->_modules_path.$module."/".$module.".php";
            
            if( file_exists($file) ){
                
                require_once($file);
                $class = $this->getModuleClassName($module);
               
                if( class_exists($class) ){
                    $obj = new $class($module);
                    $obj->readXML();
                    $obj->disable();
                }
            }
        }
        
    }
    }

    /**
     * restituisce il nome della classe del modulo
     *
     * @param string $string
     * @return string
     */
    private function getModuleClassName(string $string):string{
		$str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    	return $str;

	}
}
?>