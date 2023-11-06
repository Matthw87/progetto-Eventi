<?php
namespace Marion\Core;
use Marion\Entities\User;
use Marion\Providers\DatabaseCache;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Support\Cache;
use Browser;
use Marion\Router\Router;
use Mobile_Detect;
use Marion\Core\Translator;
use Marion\Core\Context;
class Marion{

	private static User $current_user;

	//widgets hooks array   
    private static $widgets = array();
	
	//action hooks array   
	private static $actions = array();
	
	
	public static $actions_module = array();

	//action hooks priority array   
    private static $_priority_actions = array();
	
	public static $modules = array();
	
	/**
	 * Load hooks
	 *
	 * @return void
	 */
	public static function loadHooks(): void{
		if( !DB::schema()->hasTable('hooks')) return;
		$db = self::getDB();

		$select = $db->select('h.name as hook_name,a.function,a.priority,m.active,m.directory as module_name',"(hooks as h join hook_actions as a on h.id=a.hook_id) join modules as m on m.id=a.module_id","m.active=1");
		if( okArray($select) ){
			foreach($select as $v){
				
				//if( !array_key_exists($v['hook_name'],self::$actions_module)){
				$key = "{$v['module_name']}::{$v['function']}";
				self::$actions_module[$v['hook_name']] = array(
					$key => array(
						'priority' => $v['priority'],
						'module' => $v['module_name'],
					)
				);
				//}
	
				self::$actions[$v['hook_name']][] = $v['module_name']."::".$v['function'];
				self::$_priority_actions[$v['hook_name']][] = $v['priority'];
			
			}
		}
		
	}

	/**
	 * create a hook
	 *
	 * @param string $hook_name
	 * @param string $description
	 * @param string $type
	 * @param integer $id_module
	 * @return boolean
	 */
	 public static function create_hook(
		 string $hook_name,
		 string $description,
		 string $type,
		 int $id_module=0): bool
    {    
        $hook_name=mb_strtolower($hook_name);
        
		$database = self::getDB();
		$check = $database->select('*','hooks',"name='{$hook_name}'");
		if( !okarray($check) ){
			$toinsert = array(
				'name' => $hook_name,
				'description' => $description,
				'type' => $type,
				'module_id' => $id_module
			);
			$id = $database->insert('hooks',$toinsert);
			if( $id ){
				return true;
			}
		}
        return false ;
    }
	
	
	/**
	 * regiter a function to an action hook
	 *
	 * @param string $hook
	 * @param string $function
	 * @param integer $id_module
	 * @param integer $priority
	 * @return boolean
	 */
	 public static function register_action(
		 string $hook,
		 string $function,
		 int $id_module=0, 
		 int $priority=10): bool
    {    
        $hook=mb_strtolower($hook);
        
		$database = self::getDB();
		$check = $database->select('*','hooks',"name='{$hook}'");
		
		if( okarray($check) ){
			$id_hook = $check[0]['id'];
			$toinsert = array(
				'function' => $function,
				'hook_id' => $id_hook,
				'module_id' => $id_module,
				'priority' => $priority
			);
			$id = $database->insert('hook_actions',$toinsert);
			if( $id ){
				return true;
			}
		}
        return false ;
    }

    /**
	 * Add a function to an action hook
	 *
	 * @param string $hook
	 * @param string $function
	 * @param integer $priority
	 * @return boolean
	 */
    public static function add_action(string $hook,string $function, int $priority=10): bool
    {    
        $hook=mb_strtolower($hook);
        // create an array of function handlers if it doesn't already exist
        if(!self::exists_action($hook))
        {
            self::$actions[$hook] = array(); 
        }
 
        // append the current function to the list of function handlers
        if (is_callable($function))
        {
            self::$actions[$hook][] = $function;
			self::$_priority_actions[$hook][] = $priority;
			
			$ewquired_files_list = get_required_files();
			$last_required_file = $ewquired_files_list[count($ewquired_files_list)-1];
			
			self::$actions_module[$hook][$function] = array(
				'priority' => $priority,
				'module' => basename(dirname($last_required_file)),
			);	
            return true;
        }
        return false ;
    }
 
	/**
     * executes the functions for the given hook
	 * 
     * @param string $hook
     * @param mixed $params
     * @return boolean true if a hook was setted
     */
    public static function do_action(string $hook, $params=NULL, $return_value = false)
    {

		
        $hook=mb_strtolower($hook);
        if(isset(self::$actions[$hook]))
        {
			$actions_sorted = [];
			//ordino le funzioni per priorità
			foreach(self::$actions[$hook] as $k => $v){
				$actions_sorted[$k]['function'] =$v;
				$actions_sorted[$k]['priority'] =self::$_priority_actions[$hook][$k];
			}

			uasort($actions_sorted,function($a,$b){
				if ($a['priority']==$b['priority']) return 0;
				return ($a['priority']<$b['priority'])?-1:1;
			});

			
            // call each function handler associated with this hook
            foreach($actions_sorted as $function)
            {
				
				if( preg_match('/::/',$function['function']) ){
					list($module,$func) = explode('::',$function['function']);
					$path_class = _MARION_MODULE_DIR_.$module."/".$module.".php";
					if( !file_exists($path_class) ){
						$path_class = _MARION_THEME_DIR_.$module."/".$module.".php";
					}


					if( file_exists($path_class) ){
						require_once($path_class);
						$class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $module)));
						
						
						if( class_exists($class_name) ){
							$obj = new $class_name();
							if (is_array($params) ){
								$res = call_user_func_array([$obj,$func],$params);
							}else{
								$res = call_user_func([$obj,$func],$params);
							}
							
						}
					}
					
				}else{
					if (is_array($params) )
					{
						$res = call_user_func_array($function['function'],$params);
					}
					else 
					{
						$res = call_user_func($function['function']);
					}
				}
				if( $return_value ){
					if( isset($res) ) return $res;
				}
				
                //cant return anything since we are in a loop! dude!
            }
        }
    }
 
	/**
     * gets the functions for the given hook
     * @param string $hook
     * @return mixed 
     */
    public static function get_action($hook)
    {
        $hook=mb_strtolower($hook);
        return (isset(self::$actions[$hook]))? self::$actions[$hook]:false;
    }
 
	/**
     * check exists the functions for the given hook
     * @param string $hook
     * @return boolean 
     */
    public static function exists_action($hook)
    {
        $hook=mb_strtolower($hook);
        return (isset(self::$actions[$hook]))? true:false;
    }


	/**
	 * Load theme
	 *
	 * @return void
	 */
	public static function loadTheme(): void{
		 if( !DB::schema()->hasTable('settings')) return;
		 if (self::exists_action('load_theme'))
        {
            self::do_action('load_theme',func_get_args());
        }else {	
			
			
			//verifico se la cache è attiva
			//debugga('qua');exit;
			$data = array();
			// se la cache è attiva allora prendo l'eventuale configurazione salvata
			if(Cache::isActive()){
				$data = Cache::get('setting_themes');
			}
			
			
			if(!okArray($data)){
				$database = self::getDB();
				$theme_setting = $database->select('chiave,valore','settings',"gruppo='theme_setting'");
				
				$data = array();
				if( okArray($theme_setting) ){
					foreach($theme_setting as $k => $v){
						$data[$v['chiave']] = $v['valore'];
					}
				}
				
				if( Cache::isActive() ){
					
					Cache::set('setting_theme',$data);
				}
					
			}

			if(okArray($data)){
				$GLOBALS['activetheme'] = $data['active'];
				define('_MARION_THEME_',$GLOBALS['activetheme']);
			}
		
		}

	}

	/**
	 * detect client
	 *
	 * @return void
	 */
	public static function detectClient(): void{
		//unset($_SESSION['_MARION_DEVICE_']);
		if(isset($_SESSION['_MARION_DEVICE_']) ){
			define('_MARION_DEVICE_',$_SESSION['_MARION_DEVICE_']);
			define('_MARION_BROWSER_',$_SESSION['_MARION_BROWSER_']);
			define('_MARION_ENABLE_WEBP_',$_SESSION['_MARION_ENABLE_WEBP_']);
		}else{
			//classe che permette di verificare se il client è mobile/tablet/web
			
			$detect = new Mobile_Detect();
			$browser = new Browser();
			
			//debugga($browser);exit;
			// Exclude tablets.
			if( $detect->isMobile() ){
				if( $detect->isTablet() ){
					define('_MARION_DEVICE_','TABLET');
				}else{
					define('_MARION_DEVICE_','MOBILE');
				}
			}else{
				if( $detect->isTablet() ){
					define('_MARION_DEVICE_','TABLET');
				}else{
					define('_MARION_DEVICE_','DESKTOP');
				}
				
			}
			

			define('_MARION_BROWSER_',$browser->getBrowser());
			if( (_MARION_BROWSER_ == 'Firefox'|| _MARION_BROWSER_ == 'Chrome') && !$detect->isiOS() ){
				define('_MARION_ENABLE_WEBP_',1);
			}else{
				define('_MARION_ENABLE_WEBP_',0);
			}
			
			$_SESSION['_MARION_BROWSER_'] = _MARION_BROWSER_;
			$_SESSION['_MARION_ENABLE_WEBP_'] = _MARION_ENABLE_WEBP_;
			$_SESSION['_MARION_DEVICE_'] = _MARION_DEVICE_;
		}
		

	}

	
	/**
	 * Load language
	 *
	 * @return void
	 */
	public static function loadLang(): void{
		if( defined('_MARION_LANG_') ) return;
		if( !DB::schema()->hasTable('settings')) return;
		$config = array();

		// se la cache è attiva allora prendo l'eventuale configurazione salvata
		if(Cache::isActive()){
			$config = Cache::get('setting_locale');
		}
		if(!okArray($config)){
			$database = self::getDB();
			
			$locale_setting = $database->select('chiave,valore','settings',"gruppo='locale'");
			if( okArray($locale_setting) ){
				foreach($locale_setting as $v){
					if( $v['chiave'] == 'supportati' ){
						$config[$v['chiave']] = unserialize($v['valore']);
						if( okArray($config[$v['chiave']]) ){
							$where = "code in (";
							foreach($config[$v['chiave']] as $v1){
								$where .= "'{$v1}',";
							}
							$where = preg_replace('/\,$/',")",$where);
							$time_locale = $database->select('code,time',"locale",$where);
							if( okArray($time_locale) ){
								foreach($time_locale as $v2){
									$time_locale_config[$v2['code']] = $v2['time'];
								}
								$config['timezone'] = $time_locale_config;
							}
							
						}
					}else{
						$config[$v['chiave']] = $v['valore'];
					}
				}
			}

			
			if( $GLOBALS['setting']['default']['CACHE']['active'] ){
				$time_cache = $GLOBALS['setting']['default']['CACHE']['time'];
				Cache::get('setting_locale',$config,$time_cache);
			}
				
		}
		if( okArray($config) ){
			foreach($config as $k => $v){
				$GLOBALS['setting']['default']['LOCALE'][$k] = $v;
			}
		}
		
		
		if( !isset($GLOBALS['activelocale']) ){
			if( _var('lang') ){
				$GLOBALS['activelocale'] = _var('lang');
				$_SESSION['activelocale'] = $GLOBALS['activelocale'];
				
				if( $route = _var('_redirect_route') ){
					header('Location: '.$route);
					die();
				}
			}elseif( !empty($_SESSION['activelocale']) ){
				$GLOBALS['activelocale'] = $_SESSION['activelocale'];
			}elseif ( isset($GLOBALS['setting']['default']['LOCALE']['default']) && !empty($GLOBALS['setting']['default']['LOCALE']['default']) ){
				$GLOBALS['activelocale'] = $GLOBALS['setting']['default']['LOCALE']['default'];
			}else{
				$GLOBALS['activelocale'] = 'it';
			}
		}

		
		Context::set('lang',$GLOBALS['activelocale']);
		$_locale_timezone = Marion::getConfig('locale','timezone');
		if( okArray( $_locale_timezone) ){
			$_timezone = $_locale_timezone[$GLOBALS['activelocale']];
			//setLocale('LC_TIME',$_timezone.".UTF-8"); // PHP 7 todo
		}

		//debugga($_locale_timezone);exit;
		//debugga($GLOBALS);exit;
		unset($_timezone);
		unset($_locale_timezone);
		
	}


	/*public static function loadCurrency(){

		$config = array();


		
		
		
		

		// se la cache è attiva allora prendo l'eventuale configurazione salvata
		if(Cache::isActive()){
			$config = Cache::get('setting_currency');
		}
		if(!okArray($config)){
			$database = self::getDB();
			
			// lettura delle valute attive
			$select_valute = $database->select('*','currency',"1=1");
			if( okArray($select_valute) ){
				
				foreach($select_valute as $v){
					if( $v['active'] ){
						if( $v['defaultValue'] ){
							$config['default'] = $v['code'];
						}
						$config['supported'][] = $v['code']; 
						$config['exchangeRate'][$v['code']] =$v['exchangeRate'];
					}
					$config['html'][$v['code']] =$v['html'];
				}
			}

			
			
			if( $GLOBALS['setting']['default']['CACHE']['active'] ){
				$time_cache = $GLOBALS['setting']['default']['CACHE']['time'];
				Cache::set('setting_currency',$config,$time_cache);
			}
				
		}
		if( okArray($config) ){
			foreach($config as $k => $v){
				$GLOBALS['setting']['default']['CURRENCY'][$k] = $v;
			}
		}
		

		//lettura della valuta corrente
		if( !isset($GLOBALS['activecurrency']) ){
			if( _var('currency') ){
				$GLOBALS['activecurrency'] = _var('currency');
				$_SESSION['activecurrency'] = $GLOBALS['activecurrency'];
			}elseif( !empty($_SESSION['activecurrency']) ){
				$GLOBALS['activecurrency'] = $_SESSION['activecurrency'];
			}elseif ( getConfig('currency','default') ){
				$GLOBALS['activecurrency'] = getConfig('currency','default');
			}else{
				$GLOBALS['activecurrency'] = 'EUR';
			}
		}

		define('_MARION_CURRENCY_',$GLOBALS['activecurrency']);
		


	
		

		

	}
	*/

	/**
	 * Load translations
	 *
	 * @return void
	 */
	public static function loadTranslations(): void{
		$lang = Context::getLang();
		if( !$lang ) return;
		$scope = 'front';
		if (defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_) {
			$scope = 'admin';
		}

		$key = 'translations_'.$scope."_".$lang;

		if(Cache::isActive()){
			$translations = Cache::get($key);
			if( okArray($translations) ){
				Translator::loadTranslations($translations);
				return;
			}
		}


		$_root_document = _MARION_ROOT_DIR_;
			
		if ($scope == 'admin') {
			$_root_document .= "backend/";
		}
		
		$data = [];
		
		
		
		if( file_exists($_root_document."translations/".$lang.".json")){
						
			$data['_default'] =json_decode(file_get_contents($_root_document."translations/".$lang.".json"),true);
			
		}
		if( file_exists(_MARION_ROOT_DIR_."backend/translations/shared/".$lang.".json")){
						
			$data['_shared'] =json_decode(file_get_contents(_MARION_ROOT_DIR_."backend/translations/shared/".$lang.".json"),true);
			
		}

		//leggo i moduli installati
		$modules = self::$modules;
		if( okArray($modules) ){
			foreach($modules as $mod){
				
				$directory = $mod['directory'];
				$translation_flag = false;
				
				if($mod['active']){
					if( !$mod['scope'] ){
						$translation_flag = true;
					}else{
						if (defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_ ) {
							if( $mod['scope'] == 'admin' ){
								$translation_flag = true;
							}
						}else{
							if( $mod['scope'] == 'frontend' ){
								$translation_flag = true;
							}
						}
					}
					
					

				}
				if( $translation_flag ){
					//debugga(_MARION_MODULE_DIR_.$directory."/translations/"._MARION_LANG_.".json");exit;
					if( file_exists(_MARION_MODULE_DIR_.$directory."/translations/".$lang.".json")){
						$data[$directory] = json_decode(file_get_contents(_MARION_MODULE_DIR_.$directory."/translations/".$lang.".json"),true);
						
					}

					if( file_exists(_MARION_THEME_DIR_.$directory."/translations/".$lang.".json")){
						$data[$directory] = json_decode(file_get_contents(_MARION_THEME_DIR_.$directory."/translations/".$lang.".json"),true);
						
					}
				}
			}
		}
		if (defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_) {
			if( file_exists(_MARION_THEME_DIR_._MARION_THEME_."/translations/".$lang.".json") ){
				$data[_MARION_THEME_] = json_decode(file_get_contents(_MARION_THEME_DIR_._MARION_THEME_."/translations/".$lang.".json"),true);
			}
			
		}
		
		Translator::loadTranslations($data);
		if(Cache::isActive()){
			Cache::set($key,$data);
		}
	}
	
	public static function read_config(): void{
		
		 if( !DB::schema()->hasTable('settings')) return;
		 if (self::exists_action('read_config'))//if we remove this will perform the hooks plus normal functionality
        {
            self::do_action('read_config',func_get_args());
        }else {

			$config = array();
			//lettura configurazioni
			self::do_action('before_read_config',func_get_args());
			

			//verifico se la cache è attiva
			
			

			// se la cache è attiva allora prendo l'eventuale configurazione salvata
			if(Cache::isActive()){
				$config = Cache::get('setting');
			}

			
			
			
			//se non esiste una configurazione salvata allora la ricalcolo
			if(!okArray($config)){
				$config = array();
				$database = self::getDB(); 
				$select_setting = $database->select('*','settings',"gruppo <> 'image' AND gruppo <> 'locale' order by ordine");
				$select_image = $database->select('*','settings',"gruppo = 'image' order by ordine");
				

				if( okArray($select_setting) ){
					foreach($select_setting as $v){
						$config[strtoupper($v['gruppo'])][$v['chiave']] = $v['valore']; 
					}
				}
				if( okArray($select_image) ){
				
					foreach($select_image as $v){
						if( $v['chiave'] == 'resize') $v['valore'] = unserialize($v['valore']);
						$config[strtoupper($v['gruppo'])][$v['etichetta']][$v['chiave']] = $v['valore']; 
					}
				}
				
				//leggo i moduli installati
				$modules = self::$modules;
				
				if( okArray($modules) ){
					foreach($modules as $v){
						$config['MODULES']['installed'][] = $v['directory'];
						if( !$v['active'] ) continue;
						$config['MODULES']['actived'][] = $v['directory'];
						if( $v['scope'] ){
							$config['MODULES'][$v['scope']][] = $v['directory'];
						}else{
							$config['MODULES']['admin'][] = $v['directory'];
							$config['MODULES']['frontend'][] = $v['directory'];
						}
					}
				}
				
				if( Cache::isActive() ){
					Cache::set('setting',$config);
				}
			}
			
			foreach($config as $k => $v){
				$GLOBALS['setting']['default'][$k] = $v;
			}

			
			
			
			$_root_document = _MARION_ROOT_DIR_;
			
			if (defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_) {
				$_root_document .= "backend/";
				if( isset($GLOBALS['setting']['default']['MODULES']['admin'])){
					Context::set('modules',$GLOBALS['setting']['default']['MODULES']['admin']);
				}else{
					Context::set('modules',[]);
				}
			}else{
				if( isset($GLOBALS['setting']['default']['MODULES']['frontend'])){
					Context::set('modules',$GLOBALS['setting']['default']['MODULES']['frontend']);
				}else{
					Context::set('modules',[]);
				}
			}

			
			
			
		
			if (defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_ ) {
				
				if( okArray($config['MODULES']['admin']) ){
					foreach($config['MODULES']['admin'] as $directory){
						if( file_exists(_MARION_MODULE_DIR_.$directory."/action.php")){
							require_once(_MARION_MODULE_DIR_.$directory."/action.php");
						}
						if( file_exists(_MARION_MODULE_DIR_.$directory."/widget.php")){
							require_once(_MARION_MODULE_DIR_.$directory."/widget.php");
						}
					}

				}
				if(file_exists(_MARION_THEME_DIR_._MARION_THEME_."/action.php")){
					require_once(_MARION_THEME_DIR_._MARION_THEME_."/action.php");
				}
					
			}else{
				
				if( isset($config['MODULES']['frontend']) && okArray($config['MODULES']['frontend']) ){
					foreach($config['MODULES']['frontend'] as $directory){
						
						$path_theme_modules = _MARION_THEME_DIR_._MARION_THEME_.'/modules/';
						foreach($config['MODULES']['frontend'] as $directory){
							
							if( file_exists($path_theme_modules.$directory."/action.php")){
								require_once($path_theme_modules.$directory."/action.php");
							}else{
								if( file_exists(_MARION_MODULE_DIR_.$directory."/action.php")){
									require_once(_MARION_MODULE_DIR_.$directory."/action.php");
								}

							}
							if( file_exists($path_theme_modules.$directory."/widget.php")){
								require_once($path_theme_modules.$directory."/widget.php");
							}else{
								if( file_exists(_MARION_MODULE_DIR_.$directory."/widget.php")){
									require_once(_MARION_MODULE_DIR_.$directory."/widget.php");
								}

							}
						}
					}
				}

				if(file_exists(_MARION_THEME_DIR_._MARION_THEME_."/action.php")){
					require_once(_MARION_THEME_DIR_._MARION_THEME_."/action.php");
				}
				if(file_exists(_MARION_THEME_DIR_._MARION_THEME_."/widget.php")){
					require_once(_MARION_THEME_DIR_._MARION_THEME_."/widget.php");
				}
			}
			
			if( file_exists(_MARION_ROOT_DIR_.'widget.php') ){
				require_once(_MARION_ROOT_DIR_.'widget.php');
			}
			if( file_exists(_MARION_ROOT_DIR_.'action.php')){
				require_once(_MARION_ROOT_DIR_.'action.php');
			}
		}
		
		//lettura configurazioni
		self::do_action('after_read_config',func_get_args());
		

		//controllo se è stato effettuato il login con un token
		/*if( _var('token') ){
			if( !authUser()){
				$check_user = User::loginWithToken(_var('token'));
				if( is_object($check_user) ){
					self::setUser($check_user);
				}
			}
		}*/
		

		/*if (self::getConfig('generale',"restrict_area")) {
			$_user_restricted = $_SERVER['PHP_AUTH_USER'];
			$_pass_restricted = $_SERVER['PHP_AUTH_PW'];
			//debugga($_SERVER);exit;
			$nomesito = $BLOBALS['setting']['default']['GENERALE']['nomesito'];
			$database = self::getDB();
			if( !okArray($database->select('*','user',"username='{$_user_restricted}' and password='{$_pass_restricted}' and restricted=1") ) ){
				  header('WWW-Authenticate: Basic realm="'.$nomesito.'"');
				  header('HTTP/1.0 401 Unauthorized');
				  die ("Not authorized");
			}else{
				unset($_user_restricted);
				unset($_pass_restricted);
			}


	
		}*/
		
	}

	/**
	 * get value from configuration
	 *
	 * @param string $key
	 * @param [type] $label
	 * @return mixed
	 */
	public static function getConfig(string $key, string $label=NULL){
		if( $label ){
			if( isset($GLOBALS['setting']['default'][strtoupper($key)][$label]) ){
				return $GLOBALS['setting']['default'][strtoupper($key)][$label];
			}else{
				return false;
			}
			
		}else{
			if( isset($GLOBALS['setting']['default'][strtoupper($key)]) ){
				return $GLOBALS['setting']['default'][strtoupper($key)];
			}else{
				return false;
			}
			
		}

	}

	/**
	 * Add value to configuration
	 *
	 * @param string $group
	 * @param string $key
	 * @param string $value
	 * @return boolean
	 */
	public static function setConfig(string $group,string $key,string $value): bool{
		if( $group && $key){
			
			$database = self::getDB();
			if( okArray($database->select('*',"settings","gruppo='{$group}' AND chiave = '{$key}'") ) ){
				$database->update('settings',"gruppo='{$group}' AND chiave = '{$key}'",array('valore'=>$value));
			}else{
				
				$toinsert = array(
					'gruppo' => $group,
					'chiave' => $key,
					'valore' => $value,
					);
				$database->insert('settings',$toinsert);
			}
			
			return true;
		}else{
			return false;
		}

	}

	/**
	 * Remove value from configuration
	 *
	 * @param string $group
	 * @param string $key
	 * @return boolean
	 */
	public static function delConfig(string $group, string $key): bool{
		if( $group && $key){
			$database = self::getDB();
			$database->delete('settings',"gruppo='{$group}' AND chiave='{$key}'");
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Return url site
	 *
	 * @return string
	 */
	public static function getAsboluteBaseUrl(): string{
		if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])){
			$_protocollo = $_SERVER['HTTP_X_FORWARDED_PROTO'];
		}else{
			$_protocollo = !empty($_SERVER['HTTPS']) ? "https" : "http";
		}
		$url = $_protocollo."://".Marion::getConfig('general','baseurl')._MARION_BASE_URL_;

		return $url;
	}
	
	/**
	 * refresh configurazione
	 *
	 * @return void
	 */
	public static function refresh_config(): void{
		
		if( Cache::exists("setting") ){
			Cache::remove('setting');
		}	
		if( Cache::exists("setting_locale") ){
			Cache::remove('setting_locale');
		}
		if( Cache::exists("setting_currency") ){
			Cache::remove('setting_currency');
		}
		if( Cache::exists("setting_theme") ){
			Cache::remove('setting_theme');
		}
		Marion::loadLang();
		//Marion::loadCurrency();
		Marion::read_config();

		
		
	}
	

	public static function getLocales(){
		return Marion::getConfig('locale','supportati');
	}

	/**
	 * Check if site is multilanguage
	 *
	 * @return boolean
	 */
	public static function isMultilocale(): bool{
		if( !self::getConfig('locale','supportati') ){
			return false;
		}
		if( count(self::getConfig('locale','supportati')) > 1){
			return true;	
		}else{
			return false;
		}
		
	}
	public static function getCurrencies(){
		return Marion::getConfig('currency','supported');
	}

	public static function isMulticurrency(){
		
		if( count(getConfig('currency','supported')) > 1){
			return true;	
		}else{
			return false;
		}
		
	}


	public static function getExchangeRate($code=NULL){
		if( !$code ) $code = $GLOBALS['activecurrency'];
		$rates = self::getConfig('currency','exchangeRate');
		
		$rate = $rates[$code];
		
		if( $rate ){
			return $rate;
		}else{
			return 1;
		}
	}

	public static function getHtmlCurrency($code=NULL){
		if( !$code ) $code = $GLOBALS['activecurrency'];
		$htmls = self::getConfig('currency','html');
		
		if( $htmls && array_key_exists($code,$htmls) ){
			$html = $htmls[$code];
		}
		
		
		if( isset($html)){
			return $html;
		}else{
			return "&euro;";
		}
	}

	
	/**
	 * get current user logged
	 *
	 * @return User
	 */
	public static function getUser(): ?User{
		if( isset(self::$current_user) ){
			return self::$current_user;
		}
		$storage_user = Storage::get('marion_userdata');
		if( $storage_user ){
			$user = User::withData($storage_user);
			self::setUser($user);
			return $user;
		}
		return null;
	}

	/**
	 * Logout system
	 *
	 * @return void
	 */
	public static function logout(): void{
		Marion::do_action('action_before_logout');
		Storage::unset('marion_userdata');
		session_unset();
		session_destroy();
		Marion::do_action('action_after_logout');

		
	} 

	/**
	 * Set current user application
	 *
	 * @param User $user
	 * @param bool $store
	 * @return void
	 */
	public static function setUser(User $user, bool $store = true): void{
		self::$current_user = $user;
		if( $store ){
			Storage::set('marion_userdata',$user);
		}
		
	}

	/**
	 * Check authorization
	 *
	 * @param string $type
	 * @return boolean
	 */
	public static function auth(string $type): bool{
		$user = self::getUser();
		if(!is_object($user)) return false;
		return $user->auth($type);
	}


	public static function isLocked(){
		if(!authAdminUser()) return false;
		if($_SERVER['REDIRECT_admin'] != 'active') return false;
		$user = self::getUser();

		if(!is_object($user)) return false;
		return $user->locked;
	}

	/**
	 * check if module is active
	 *
	 * @param string $module
	 * @return boolean
	 */
	public static function isActiveModule(string $module): bool{
		$database = self::getDB();
		$module = $database->select('*','modules',"tag='{$module}' and active=1");

		if( okArray($module) ){
			return true; 
		}else{
			return false;
		}
	}

	/**
	 * Slugify string
	 *
	 * @param string $text
	 * @return string
	 */
	public static function slugify(string $text): string
	{
	  // replace non letter or digits by -
	  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

	  // transliterate
	  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	  // remove unwanted characters
	  $text = preg_replace('~[^-\w]+~', '', $text);

	  // trim
	  $text = trim($text, '-');

	  // remove duplicate -
	  $text = preg_replace('~-+~', '-', $text);

	  // lowercase
	  $text = strtolower($text);

	  if (empty($text))
	  {
		return 'n-a';
	  }

	  return $text;
	}


	

	/*metodo che crea le combinazioni di valori di un array di array

		
		Marion::combinations(
				array(
					array('A1','A2','A3'), 
					array('B1','B2','B3'), 
					array('C1','C2')
				)
			)


	*/
	public static function combinations($arrays, $i = 0){
		
		if (!isset($arrays[$i])) {
			return array();
		}
		if ($i == count($arrays) - 1) {
			return $arrays[$i];
		}

		// get combinations from subsequent arrays
		$tmp = Marion::combinations($arrays, $i + 1);

		$result = array();

		// concat each array from tmp with each element from $arrays[$i]
		foreach ($arrays[$i] as $v) {
			foreach ($tmp as $t) {
				$result[] = is_array($t) ? 
					array_merge(array($v), $t) :
					array($v, $t);
			}
		}

		return $result;
	}




	

	//metodo che estende l'operazione di inserimento in sessione di una valore
	public static function sessionize($key=NULL,$value=NULL){
		if( $key && $value ){
			
			
			//verifico se un valore è encodato on base64 e nel caso lo decifro
			if ( base64_encode(base64_decode($value, true)) === $value){
				$value = base64_decode($value);
			}
			
			//verifico se il valore è serializzato e nel caso lo unserializzo
			if( Base::is_serialized($value) ){
				$value = unserialize($value);
			}
			$_SESSION[$key] = $value;
		}
	}


	/**
	 * Return random string
	 *
	 * @param integer $len
	 * @return string
	 */
	public static function randomString(int $len=6): string{
		
		$result = "";
		$chars = 'abcdefghijklmnopqrstuvwxyz$_?!-0123456789';
		$charArray = str_split($chars);
		for($i = 0; $i < $len; $i++){
			$randItem = array_rand($charArray);
			$result .= "".$charArray[$randItem];
		}
		return $result;
		
	}

	/**
	 * Close datbase Connection
	 *
	 * @return void
	 */
	public static function closeDB(): void{
		if( is_object($GLOBALS['Database']) ){
			$GLOBALS['Database']->close();
		}
	}

	/**
	 * Get database instance 
	 *
	 * @return DatabaseCache
	 */
	public static function getDB():DatabaseCache{
		if( !isset($GLOBALS['database']) ){
			$options = $GLOBALS['setting']['default']['DATABASE']['options'];
			$database = new DatabaseCache($options);
			$GLOBALS['database'] = $database;

		}
		return $GLOBALS['database'];
	}

	
	/**
	 * Load routes
	 *
	 * @return void
	 */
	public static function loadRoutes(): void{
		
		if(Cache::isActive()){
			$data = Cache::get('cached_routes');
			if( $data ){
				Router::setRoutes($data);
				return;
			}
		}

		$path_routes = _MARION_ROOT_DIR_."routes.php";
		if( file_exists($path_routes) ){
			require_once($path_routes);
		}
		
		
		if( okArray(self::$modules) ){
			foreach(self::$modules as $v){
				
				if( $v['active'] ){
					$path_routes = _MARION_MODULE_DIR_.$v['directory']."/routes.php";
					if( file_exists($path_routes) ){
						require_once($path_routes);
					}else{
						$path_routes = _MARION_THEME_DIR_.$v['directory']."/routes.php";
						if( file_exists($path_routes) ){
							require_once($path_routes);
						}
					}
				}
			}
		}
		
		Router::buildRoutes();
		
		if(Cache::isActive()){
			Cache::set('cached_routes',Router::getRoutes());
		}
		

	}
	/**
	 * Load modules
	 *
	 * @return void
	 */
	public static function loadModules(): void{
		if( DB::schema()->hasTable('modules') ){
			$db = self::getDB();

			self::$modules = $db->select('directory,scope,active,autoload,theme','modules',"1=1");
			
			if( okArray(self::$modules) ){
				foreach(self::$modules as $mod){
					if( $mod['autoload'] ){
						
						if(file_exists(_MARION_MODULE_DIR_.$mod['directory']."/vendor/autoload.php")){
							require(_MARION_MODULE_DIR_.$mod['directory']."/vendor/autoload.php");
						}else{
							if( $mod['theme'] && $mod['active']){
								if( file_exists(_MARION_THEME_DIR_.$mod['directory']."/vendor/autoload.php")) {
									require(_MARION_THEME_DIR_.$mod['directory']."/vendor/autoload.php");
								}
							}
						}
					}
				}
			}
		}
	}
}










?>