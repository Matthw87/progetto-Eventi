<?php
namespace Marion\Core;
use Marion\Entities\Cms\{MenuItem,HomeButton};
use Marion\Entities\{Profile,Permission};
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Support\Cache;

/*********************************************************
Classe che si occupa di installare e disinstallare un modulo
*********************************************************/
class Module{

	private $_version;
	protected $directory_module;
	protected $_modules_path;

	public $config_xml;
	public $config;
	public $info;
	public $errorMessage;
	public $error;
	
	function __construct($path=null,$path_modules = null){
		$this->directory_module = $path;
		$this->_modules_path = $path_modules?$path_modules:_MARION_MODULE_DIR_;
	}


	function setModulePath(string $path){
		$this->_modules_path = $path;
		return $this;
	}



	/**
	 * metodo che legge il file di configurazione XML
	 *
	 * @return void
	 */
	function readXML(): void{
		
		$xml_file = $this->_modules_path.$this->directory_module."/config.xml";
		
		if( file_exists($xml_file) ){
			$data = simplexml_load_file($xml_file);
			$this->config_xml = $data;
			if( is_object($data->info) ){
				$this->_version = isset($this->info['version'])?$this->info['version']:'';
				if(is_object($data->info->description)){
					$data->info->description = $data->info->description->__toString();
				}
			}
			
			$this->config = $this->object_to_array($data);
			
		}
	}

	/**
	 * metodo che installa il modulo
	 *
	 * @return boolean
	 */
	function install(): bool{
		//controllo della compatibilità
		if( !$this->checkCompatibility() ){
			
			return false;
		}

		//controllo se ci sono dipendenze o conflitti
		if( !$this->checkConflits() || !$this->checkDependencies()) return false;
		$sql_file = $this->_modules_path.$this->directory_module."/sql/install.sql";
		if( file_exists($sql_file) ){
			//per la nuova gestione dei moduli
			$this->db_import_sql_from_file($sql_file);
		}
		
		//inserisco il modulo della tabella module
		$this->insertModule();	
		//aggiungo gli hooks
		$this->addHooks();
		//aggiungo le action agli hooks
		$this->addHookActions();
		//creo le voci del menu
		$this->createMenus();
		//creo gli home buttons
		$this->createHomeButtons();
		//salvo i widgets
		$this->saveWidgets();
		//salvo i permessi
		$this->savePermissions();

		
		$this->cleanCache();
		return true;
	}

	/**
	 * metodo che disinstalla il modulo
	 *
	 * @return boolean
	 */
	function uninstall(): bool{
		
		if( !$this->checkUninstall() ){
			return false;
		}
		$sql_file = "../modules/".$this->directory_module."/sql/uninstall.sql";
		if( file_exists($sql_file) ){
			
			$this->db_import_sql_from_file($sql_file);
		}
		//elimino il modulo dalla tabella dei moduli
		$tag = (string)$this->config_xml->info->tag;
		$select_module = DB::table('modules')->where('directory',$tag)->get()->toArray();
		
		if( !okArray($select_module) ){
			return false;
		}
		$info_module = $select_module[0];
		$this->config_xml->info->id = $info_module->id;
		$this->deleteMenus();
		$this->deleteHomeButtons();
		$this->deleteWidgets();
		$this->removeHooks();
		$this->removeHookActions();
		
		$this->deletePermissions();
		$id_module = (int)$this->config_xml->info->id;
		DB::table('modules')->where('id',$id_module)->delete();
		
		$this->cleanCache();
		Marion::read_config();
		return true;
	}


	function isUpgradable(): bool{
		$tag = (string)$this->config_xml->info->tag;
		$module = DB::table('modules')->where('directory',$tag)->first();
		$current_version = (int)preg_replace('/\./','',$module->version);
		$new_version_string = (string)$this->config_xml->info->version;
		$new_version = (int)preg_replace('/\./','',$new_version_string);
		if( $new_version > $current_version ){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * metodo che effettua lu'upgrade di un modulo
	 *
	 * @return boolean
	 */
	function upgrade(): bool{
		//controllo della compatibilità
		if( !$this->checkCompatibility() ){
			
			return false;
		}

		//controllo se ci sono dipendenze o conflitti
		if( !$this->checkConflits() || !$this->checkDependencies()) return false;
		

		$tag = (string)$this->config_xml->info->tag;
		if( $this->isUpgradable() ){
			$new_version_string = (string)$this->config_xml->info->version;
			DB::table('modules')->where('directory',$tag)->update(['version' => $new_version_string]);
			return true;
		}else{
			$this->errorMessage = "no new version found";
			return false;
		}

		
	}

	/**
	 * controlla se il modulo è compatibile con la versione di Marion
	 *
	 * @return boolean
	 */
	private function checkCompatibility(): bool{
		$compatibilty = $this->config['info']['compatibility'];
		if( $compatibilty ){
			
			if( isset($compatibilty['min']) ){
				$min = $compatibilty['min'];
				if( version_compare(_MARION_VERSION_, $min, '<')){
					$this->errorMessage = "Error compatibility: questo modulo richiede una versione di Marion superiore a "._MARION_VERSION_;
					return false;
				}
			}
			if( isset($compatibilty['max']) ){
				$max = $compatibilty['max'];
				if( version_compare(_MARION_VERSION_, $max, '>')){
					$this->errorMessage = "Error compatibility: questo modulo richiede una versione di Marion inferiore a "._MARION_VERSION_;
					return false;
				}
			}
			
		}
		unset($this->config['info']['compatibility']);
		return true;
	}
	
	/**
	 * metodo che controlla se ci sono conflitti tra il modulo che 
	 * si sta installando con quelli già presenti nel CMS
	 *
	 * @return boolean
	 */
	private function checkConflits(): bool{
		$dependencies = isset($this->config['conflicts'])?$this->config['conflicts']:null;
		
		if( okArray($dependencies) ){
			
			$conflicts = [];
			foreach($dependencies['conflict'] as $id){
				$conflicts[] = $id;
			}
			$check = DB::table('modules')->whereIn('id',$conflicts)->where('active',true)->get();
			
			if( okArray($check) ){
				$this->error = "error conflicts";
				return false;
			}else{
				return true;
			}
		}
		return true;
		
	}

	/**
	 * metodo che controlla le dipendenze
	 *
	 * @return boolean
	 */
	private function checkDependencies(): bool{
		$dependencies = $this->getDependencies();

		if( okArray($dependencies) ){

			$select_modules = DB::table('modules')->where('active',true)->select(['directory','version'])->get()->toArray();
			if(okArray($select_modules) ){
				foreach($select_modules as $m){
					$installed_modules[$m->directory] = $m->version; 
				}
			}
			
			foreach($dependencies as $d){
					$module = $d['module'];
					if(!array_key_exists($module,$installed_modules)){
						$this->errorMessage = $module." not installed or not active";
						return false;
					}
					
					if( okArray($d['restrictions']) ){
						$version = $installed_modules[$module];
						
						$min = isset($d['restrictions']['min'])? $d['restrictions']['min']: null;
						$max = isset($d['restrictions']['max'])? $d['restrictions']['max']: null;

						if( $min ){
							if( !version_compare($version,$min,">=") ){
								$this->errorMessage = $module.": the module version must be >= ".$min;
								return false;
							}
						}
						if( $max ){
							if( !version_compare($version,$max,"<=") ){
								$this->errorMessage = $module.": the module version must be <= ".$max;
								return false;
							}
						}
					}
			}
		}
		return true;
	}

	/**
	 * metodo che controlla se è possibile disinstallare il modulo
	 *
	 * @return boolean
	 */
	private function checkUninstall(): bool{
		$installed_modules = Marion::getConfig('modules','installed');

		if( okArray($installed_modules) ){
			$module_dependencies = array();
			foreach($installed_modules as $module){

					$xml_file = $this->_modules_path.$module."/config.xml";
					if( !file_exists($xml_file) ){
						$xml_file = _MARION_THEME_DIR_.$module."/config.xml";
					}
					if( file_exists($xml_file) ){
						$data = simplexml_load_file($xml_file);
						$array = $this->object_to_array($data);
						if( array_key_exists('dependencies',$array) ){
							$dependencies = $array['dependencies'];
							if( !okArray($dependencies['dependence']) ){
								$dependencies['dependence'] = array($dependencies['dependence']);
							}
							if( in_array($this->directory_module,$dependencies['dependence']) ){
								$module_dependencies[] = $module;
							}
						}
					}
			}
			if( okArray($module_dependencies) ){
				$modules_string = '';
				foreach($module_dependencies as $module){
					$modules_string .= $module.",";
				}
				$modules_string = preg_replace('/\,$/','',$modules_string);
				$this->errorMessage = "Action invalid: ".$modules_string." depends from ".$this->directory_module;
				return false;
			}
		}
		return true;
	}
	
	/**
	 * metodo che attiva il modulo
	 *
	 * @return boolean
	 */
	function active(): bool{
		$data = $this->config;
		$info = $data['info'];
		$module = DB::table('modules')->where('directory',$info['tag'])->first();
		
		DB::table('modules')->where('id',$module->id)->update(['active'=> true]);
		DB::table('menu_items')->where('module_id',$module->id)->update(['active'=> true]);
		Marion::refresh_config();
		$this->cleanCache();
		return true;

	}

	/**
	 * metodo che disabilita un modulo
	 *
	 * @return boolean
	 */
	function disable(): bool{
		$data = $this->config;
		$info = $data['info'];
		$module = DB::table('modules')->where('directory',$info['tag'])->first();
		DB::table('modules')->where('id',$module->id)->update(['active'=> false]);
		DB::table('menu_items')->where('module_id',$module->id)->update(['active'=> false]);
		Marion::refresh_config();
		$this->cleanCache();
		return true;

	}


	private function object_to_array($obj) {
		if(is_object($obj)) $obj = (array) $obj;
		if(is_array($obj)) {
			$new = array();
			foreach($obj as $key => $val) {
				$new[$key] = $this->object_to_array($val);
			}
		}else{ 
			$new = $obj;
		}
		return $new;       
	}


	/**
	 * metodo che importa un file sql
	 *
	 * @param [type] $filename
	 * @return boolean
	 */
	private function db_import_sql_from_file(string $filename): bool{
		if ( @file_exists($filename) ) {
			$database = Marion::getDB();
			$templine = '';
			// Read in entire file
			$fp = fopen($filename, 'r');
			// Loop through each line
			while (($line = fgets($fp)) !== false) {
				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || $line == '')
					continue;
				// Add this line to the current segment
				$templine .= $line;
				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';') {
					// Perform the query

					if ( $database->execute($templine) === false ) {
						debugga($templine);
						return false;
					}
					
					// Reset temp variable to empty
					$templine = '';
				}
			}
			
			fclose($fp);
			return true;
		}

		return false;
	}

	

	function exportZip(){
			
		
			$name_file = $this->directory_module.".zip";
			$path_zip = "/tmp/".$name_file;
			

			$dir = getcwd();
			

			$res = $this->zipData($dir,$path_zip);

			header('Content-Type: application/zip');
			header("Content-Disposition: attachment; filename=".$name_file);
			header('Content-Length: ' . filesize($path_zip));
			readfile($path_zip);
			

	

	}



	function zipData($source, $destination) {
		if (extension_loaded('zip') === true) {
			
			if (file_exists($source) === true) {
				$zip = new \ZipArchive();
				if ($zip->open($destination, \ZIPARCHIVE::CREATE) === true) {
					
					$source = realpath($source);
					if (is_dir($source) === true) {
						$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);
						debugga($source);
						foreach ($files as $file) {
							$file = realpath($file);
							if (is_dir($file) === true) {
								$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
							} else if (is_file($file) === true) {
								$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
							}
						}
					} else if (is_file($source) === true) {
						$zip->addFromString(basename($source), file_get_contents($source));
					}
				}
				exit;
				return $zip->close();
			}
		}
		return false;
	}
		
	
	/**
	 * metodo che rimuove i widgets di un modulo
	 *
	 * @return void
	 */
	private function uninstallWidgets(): void{
		DB::table('widget')->where('directory',$this->directory_module)->delete();
	}

	/**
	 * metodo che installa i widgets di un modulo
	 *
	 * @return void
	 */
	private function installWidgets(): void{
		
		$info = $this->config['info'];
		
		if( $this->config['widget'] ){
			$widgets = array();
			if( !okArray( $this->config['widget']['widgets'][0] ) ){
				$widgets = array($this->config['widget']['widgets']);
			}else{
				$widgets = $this->config['widget']['widgets'];
			}
			
			foreach($widgets as $v){
				$v['restrictions'] = serialize(explode(',',$v['restrictions']));
				$v['module'] = $info['id'];
				DB::table('widget')->insert($v);
				
			}
		}
	}

	
	/**
	 * metodo che rimuove gli HomeButton di un modulo
	 *
	 * @return boolean
	 */
	private function deleteHomeButtons(): bool{
		$old_buttons = HomeButton::prepareQuery()->where('module_id',$this->directory_module)->get();
		foreach($old_buttons as $v){
			$v->delete();
		}
		return true;
	}

	/**
	 * rimuove gli hook installati dal modulo
	 *
	 * @return void
	 */
	function removeHooks(): void{
		$id = $this->config_xml->info->id;
		DB::table('hooks')->where('module_id',$id)->delete();
	}

	/**
	 * metodo che rimuove gli hook di un modulo
	 *
	 * @return void
	 */
	private function removeHookActions(): void{
		$id = $this->config_xml->info->id;
		DB::table('hook_actions')->where('module_id',$id)->delete();
	}


	function addHooks(){
		$id_module = (int)$this->config_xml->info->id;
		
		if( $this->config_xml->hooks ){
			foreach($this->config_xml->hooks->hook as $item){
				Marion::create_hook(
					$item->name,
					$item->description?$item->description:$item->name,
					$item->type?$item->type:'action',
					$id_module
			    );
			}
		}
		// register hook for override forms and lists
		if( file_exists($this->_modules_path.$this->directory_module.'/controllers/admin') ){
			$path_ctrl_admin = $this->_modules_path.$this->directory_module.'/controllers/admin';
			$list = scandir($path_ctrl_admin);
			foreach($list as $f){
				$file = $path_ctrl_admin."/".$f;
				if( is_file($file) ){
					$content = file_get_contents($file);
					preg_match_all('/FormHelper::create\(([a-z_A-Z\'\"]+),/',$content,$_matches);
					if( okArray($_matches[1]) ){
						foreach($_matches[1] as $tag ){
							$tag = preg_replace('/\'/','',$tag);
							$tag = preg_replace('/\"/','',$tag);
							$action = "action_extend_form_".$tag;
							Marion::create_hook(
								$action,
								"<b>{$tag}</b> form defined in <b>{$f}</b> in <b>{$this->directory_module}</b> module",
								'action',
								$id_module
							);
						}
					}

					$content = file_get_contents($file);
					preg_match_all('/ListHelper::create\(([a-z_A-Z\'\"]+),/',$content,$_matches);
					if( okArray($_matches[1]) ){
						foreach($_matches[1] as $tag ){
							$tag = preg_replace('/\'/','',$tag);
							$tag = preg_replace('/\"/','',$tag);
							$action = "action_extend_list_".$tag;
							Marion::create_hook(
								$action,
								"<b>{$tag}</b> list defined in <b>{$f}</b> in <b>{$this->directory_module}</b> module",
								'action',
								$id_module
							);
						}
					}

					preg_match_all('/SortableListHelper::create\(([a-z_A-Z\'\"]+),/',$content,$_matches);
					if( okArray($_matches[1]) ){
						foreach($_matches[1] as $tag ){
							$tag = preg_replace('/\'/','',$tag);
							$tag = preg_replace('/\"/','',$tag);
							$action = "action_extend_list_".$tag;
							Marion::create_hook(
								$action,
								"<b>{$tag}</b> list defined in <b>{$f}</b> in <b>{$this->directory_module}</b> module",
								'action',
								$id_module
							);
						}
					}

					preg_match_all('/SimpleListHelper::create\(([a-z_A-Z\'\"]+),/',$content,$_matches);
					if( okArray($_matches[1]) ){
						foreach($_matches[1] as $tag ){
							$tag = preg_replace('/\'/','',$tag);
							$tag = preg_replace('/\"/','',$tag);
							$action = "action_extend_list_".$tag;
							Marion::create_hook(
								$action,
								"<b>{$tag}</b> list defined in <b>{$f}</b> in <b>{$this->directory_module}</b> module",
								'action',
								$id_module
							);
						}
					}
				}
			}
		}
		
	}

	function addHookActions(){
		$id_module = (int)$this->config_xml->info->id;
		if( $this->config_xml->actions ){
			foreach($this->config_xml->actions->action as $item){
				$priority = isset($item->priority)?(int)$item->priority:null;
				if( $priority ){
					Marion::register_action($item->hook,$item->function,$id_module,$priority);
				}else{
					Marion::register_action($item->hook,$item->function,$id_module);
				}
				
			}
		}
	}



	function insertModule(){
		$data = $this->config;
		$info = $data['info'];
		
		if( array_key_exists('scope',$info) && is_array($info['scope']) ) unset($info['scope']);
		
		$info['active'] = 1;
		$info['directory'] = $this->directory_module;
		
		if( !DB::table('modules')->where('directory',$info['directory'])->exists() ){
			$id = DB::table('modules')->insertGetId($info);
			$this->config_xml->info->id = $id;
		}
	}


	function createMenus(){
		$this->deleteMenus();
		$info = $this->config_xml->info;
		
		//CREAZIONI DELLE VOCI ADMIN
		if( isset($this->config_xml->admin->menu->items) ){
		foreach($this->config_xml->admin->menu->items->item as $item){
			
			$id_parent = 0;
			
			if( $parent_tag = trim($item->parent) ){
				$parent_item = MenuItem::prepareQuery()
				->where('tag',$parent_tag)
				->where('scope','admin')
				->getOne();
				
				if( is_object($parent_item) ){
					$id_parent = $parent_item->id;
				}else{
					continue;
				}
			}

			$data_locale = (array)$item->locale;
			$data_lang = array();
			foreach($data_locale as $k => $v){
				foreach($v as $lo => $val){
					$data_lang[$lo][$k] = (string)$val;
				}
	
			}
			
			$data_item = array(
				'module_id'=> (int)$info->id,
				'tag'=> (string)$item->tag,
				'permission' => (string)$item->permission,
				'scope' => 'admin',
				'priority' => (int)$item->priority,
				'icon' => (string)$item->icon,
				'icon_image' => (string)$item->iconImg,
				'url' => (string)$item->url,
				'parent' => $id_parent
			);
			//debugga($data_item);
				
			$item = MenuItem::create()
					->set($data_item)->setDataFromArray($data_lang)
					->save();

		}
		}

		//CREAZIONI DELLE VOCI BACKEND
		if( isset($this->config_xml->backend->menu->items) ){
			foreach($this->config_xml->backend->menu->items->item as $item){
				
				$id_parent = 0;
				if( $parent_tag = trim($item->parentTag) ){
					$parent_item = MenuItem::prepareQuery()->where('tag',$parent_tag)->where('scope','frontend')->getOne();
					if( is_object($parent_item) ){
						$id_parent = $parent_item->id;
					}else{
						continue;
					}
				}

				$data_locale = (array)$item->locale;
				$data_lang = array();
				foreach($data_locale as $k => $v){
					foreach($v as $lo => $val){
						$data_lang[$lo][$k] = (string)$val;
					}
		
				}
				
				$data_item = array(
					'module_id'=> (int)$info->id,
					'tag'=> (string)$item->tag,
					'permission' => (string)$item->permission,
					'scope' => 'frontend',
					'priority' => (int)$item->priority,
					'icon' => (string)$item->icon,
					'icon_image' => (string)$item->iconImg,
					'url' => (string)$item->url,
					'parent' => $id_parent
				);
					
				MenuItem::create()
						->set($data_item)->setDataFromArray($data_lang)
						->save();

			}
		}
	}

	function deleteMenus(){
		
		$menu_old = MenuItem::prepareQuery()->where('module_id',(int)$this->config_xml->info->id)->get();
		foreach($menu_old as $v){
			$v->delete();
		}
	}


	function savePermissions(){
		$this->deletePermissions();
		if( $this->config_xml->permissions ){
			foreach($this->config_xml->permissions->permission as $item){
			
				$data = (array)$item;
				$id_module = $this->config_xml->info->id;
				$dati = array();
				$dati['module_id'] = $id_module;
				$dati['label'] = $data['tag'];
				$dati['order_view'] = 10;
				$dati['active'] = 1;
				foreach($data['locale'] as $k => $v){
					foreach($v as $lo => $val){
						$data_locale[$lo][$k] = $val;
					}
				}
				$permission = Permission::create()
					->set($dati)->setDataFromArray($data_locale)->save();
				$profile = Profile::prepareQuery()->where('superadmin',1)->getOne();

				$profile->permissions[] = $permission->id;
				$profile->save();
			}
		}
		return true;
	}
	function deletePermissions(){
		$id_module = $this->config_xml->info->id;
		$old = Permission::prepareQuery()->where('module_id',$id_module)->get();
		foreach($old as $v){
			$v->delete();
		}
		return true;
	}
	
	/**
	 * metodo che crea gli HomeButton di un modulo
	 *
	 * @return boolean
	 */
	private function createHomeButtons(): bool{
		$this->deleteHomeButtons();
		if( $this->config_xml->backend->homeButtons ){
			foreach($this->config_xml->backend->homeButtons->button as $button){
				$data = (array)$button;
				$data['module_id'] = $this->directory_module;
				$data['icon_image'] = $data['iconImg'];
				foreach($data['locale'] as $k => $v){
					foreach($v as $lo => $val){
						$data_locale[$lo][$k] = $val;
					}
		
				}
				HomeButton::create()
					->set($data)->setDataFromArray($data_locale)->save();

			}
		}
		return true;

	}
	
	/**
	 * metodo che salva i widgets del modulo
	 *
	 * @return boolean
	 */
	private function saveWidgets(): bool{
		
		$id_module = $this->config_xml->info->id;
		if( isset($this->config_xml->widgets) ){
			foreach($this->config_xml->widgets->widget as $w){
				$data = (array)$w;
				$data['module_id'] = $id_module;
				if( isset($data['restrictions']) ){
					$data['restrictions'] = serialize(explode(',',$data['restrictions']));
				}
				DB::table('widgets')->insert($data);
			}
		}
		
		return true;
	}

	
	/**
	 * metodo che elimina i widgets del modulo
	 *
	 * @return void
	 */
	private function deleteWidgets(): void{
		$id_module = (int)$this->config_xml->info->id;
		DB::table('widgets')->where('module_id',$id_module)->delete();
	}




	/**
	 * metodo che resttuisce l'oggetto Faker
	 *
	 * @return \Faker\Generator
	 */
	public function getFaker():\Faker\Generator{
		return \Faker\Factory::create();
	}

	/**
	 * metodo che esegue il seeder
	 *
	 * @return void
	 */
	public function seeder(): void{

	}
	/**
	 * restituisce l'elenco delle dipendenze del modulo
	 *
	 * @return array
	 */
	public function getDependencies(): array{
		$dependencies = isset($this->config['dependencies'])?$this->config['dependencies']:null;
		$toReturn = [];
		if( okArray($dependencies) ){
			foreach($this->config_xml->dependencies->dependence as $d ){
				$attributes = [];
				foreach($d->attributes() as $a => $b){
					if( in_array($a,['min','max']) ){
						$attributes[$a] = (string)$b;
					}
				}
				
				$toReturn[] = [
					'module' => (string)$d,
					'restrictions' => $attributes
				];
			}
		}
		return $toReturn;
	}


	/** STATIC METHODS */
	
	/**
	 * controlla se il modulo è installato
	 *
	 * @param string $module
	 * @return boolean
	 */
	public static function isInstalledModule(string $module): bool{
		return DB::table('modules')->where('directory',$module)->exists();
	}

	/**
	 * controlla se il modulo è attivo
	 *
	 * @param string $module
	 * @return boolean
	 */
	public static function isActivatedModule(string $module): bool{
		return DB::table('modules')
				->where('directory',$module)
				->where('active',true)
				->exists();
	}
	


	/**
	 * clean cache
	 *
	 * @return void
	 */
	private function cleanCache(): void{
		if( Cache::exists("setting") ){
			Cache::remove('setting');
		}
		if( Cache::exists("cached_routes") ){
			Cache::remove('cached_routes');
		}
	}
}



?>