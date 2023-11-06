<?php
use Marion\Core\Marion;
use Marion\Core\Module;
use Marion\Controllers\AdminController;
use ScssPhp\ScssPhp\Compiler;
class ModuleAdminController extends AdminController{
	public $_auth = 'config';

	public $_enable_market = false;
	public $todo = array(

	);


	/**
	 * Edit css theme
	 *
	 * @return void
	 */
	private function editCSS(): void{
		$this->setMenu('manage_themes');
		$scss_variables = [
			[
				'variable' => 'BASE_URL',
				'value' =>  $this->getBaseUrl(),
				'description' => 'percorso base del sito'
			],
			[
				'variable' => 'THEME_DIR',
				'value' =>  $this->getBaseUrl()."themes/"._MARION_THEME_,
				'description' => 'percorso del tema corrente'
			],
		];
		Marion::do_action('action_register_scss_variables',[&$scss_variables]);
		$theme = _var('module');
		if( $this->isSubmitted()){
			
			$formdata = $this->getFormdata();
			$data = $formdata['data'];
			$scss = new Compiler();
			try{
				$data_tmp = $data;
				$parameters = [];
				foreach($scss_variables as $v){
					$parameters[$v['variable']] = $v['value'];
				}
				
				$string = '';
				foreach($parameters as $key => $value){
					$string .= '$'.$key.':"'.$value.'";';
				}
				
				$data_tmp = $string.$data_tmp;
				
				$compressed = $scss->compileString($data_tmp);
				file_put_contents(_MARION_THEME_DIR_."/".$theme."/theme.scss",$data);
				file_put_contents(_MARION_THEME_DIR_."/".$theme."/theme.css",$compressed->getCss());
				$this->displayMessage('Dati salvati con successo');
			}catch(Exception $e){
				$this->errors[] = $e->getMessage();
				
			}
		}else{
			$data = file_get_contents(_MARION_THEME_DIR_."/".$theme."/theme.scss");

		}
		
		$this->setVar('scss_variables',$scss_variables);
		$this->setVar('tema',$theme);
		$this->setVar('data',$data);
		$this->output('@core/admin/module/css.htm');
	}
	/**
	 * Edit javascript theme
	 *
	 * @return void
	 */
	private function editJS(){
		$this->setMenu('manage_themes');
		$theme = _var('module');

		if( $this->isSubmitted()){

			$formdata = $this->getFormdata();
			file_put_contents(_MARION_THEME_DIR_."/".$theme."/theme.js",$formdata['data']);
			$this->displayMessage('Dati salvati con successo');
		}

		$this->setVar('tema',$theme);
		$data = file_get_contents(_MARION_THEME_DIR_."/".$theme."/theme.js");
		$this->setVar('data',$data);
		$this->output('@core/admin/module/javascript.htm');
	}

	function displayContent(){
		$action = $this->getAction();
		switch($action){
			case 'css':
				$this->editCSS();
				break;
			case 'js':
				$this->editJS();
				break;
		}

	}
	

	function getAccountModules(){

		$list = array(
			'bonifico',
			'paypal',
			'gls'
		);
		return  $list;
	}

	function getMarketModules(){

		$list = array(
			'paypal' => array(
				'id' => '10',
				'price' => '10',
				'currency' => 'EUR'
			)
		);
		return  $list;
	}


	function displayForm(){
		$this->setMenu('add_module');
		
		$action = $this->getAction();

		if( isset($_FILES['file']) && $_FILES['file']['name'] ){
			if( $_FILES['file']['type'] == 'application/zip' || $_FILES['file']['type'] == 'application/x-zip-compressed'){
				
				$file = $_FILES['file']['tmp_name'];
				$name = preg_replace('/\.zip/','',$_FILES['file']['name']);
				
				$zip = new ZipArchive;
				$res = $zip->open($file);
				if ($res === TRUE) {
				  $zip->extractTo('../modules/');
				  $zip->close();
				  $chmod = "0755";
				  chmod('modules/'.$name, octdec($chmod));
				  
				   //Marion::chmod_R('../modules/'.$name, 0777, 0777);

				  
				} else {
				 $this->errors[] ="Si è verificato un errore";
				 
				}
			}else{
				 $this->errors[] ="Il file da caricare deve essere .zip";
				 
			}
		}

		$this->output('@core/admin/module/form.htm');

	}


	function setMedia(){
		$action = $this->getAction();

		switch($action){
			case 'list':
				if( _var('theme') ){
					$this->registerJS($this->getBaseUrlBackend().'assets/js/themes.js?v=2','end');
				}else{
					$this->registerJS($this->getBaseUrlBackend().'assets/js/modules.js?v=2','end');
				}
				break;
			case 'css':
				$this->registerJS('../assets/plugins/codemirror/lib/codemirror.js','head');
				$this->registerJS('../assets/plugins/codemirror/mode/css/css.js','head');
				$this->registerJS('../assets/plugins/codemirror/addon/selection/active-line.js','head');
				$this->registerJS('.../assets/plugins/codemirror/addon/selection/matchbrackets.js','head');
				$this->registerCSS('../assets/plugins/codemirror/lib/codemirror.css');
				$this->registerCSS('.../assets/plugins/codemirror/theme/panda-syntax.css');
				$this->registerJS('../assets/plugins/codemirror/addon/search/search.js','head');
				$this->registerJS('../assets/plugins/codemirror/addon/search/searchcursor.js','head');
				$this->registerJS('../assets/plugins/codemirror/addon/search/jump-to-line.js','head');
				$this->registerJS('../assets/plugins/codemirror/addon/dialog/dialog.js','head');
				$this->registerJS('../assets/plugins/codemirror/addon/display/fullscreen.js','head');
				$this->registerCSS('../assets/plugins/codemirror/addon/dialog/dialog.css');
				break;
			case 'js':
				$this->registerJS('../assets/plugins/codemirror/lib/codemirror.js','head');
				$this->registerJS('../assets/plugins/codemirror/mode/javascript/javascript.js','head');
				$this->registerJS('../assets/plugins/codemirror/addon/selection/active-line.js','head');
				$this->registerJS('../assets/plugins/codemirror/addon/selection/matchbrackets.js','head');
				$this->registerCSS('../assets/plugins/codemirror/lib/codemirror.css');
				$this->registerCSS('../assets/plugins/codemirror/theme/panda-syntax.css');
				$this->registerJS('../assets/plugins/codemirror/addon/search/search.js','head');
				$this->registerJS('../assets/plugins/codemirror/addon/search/searchcursor.js','head');
				$this->registerJS('../assets/plugins/codemirror/addon/search/jump-to-line.js','head');
				$this->registerJS('../assets/plugins/codemirror/addon/dialog/dialog.js','head');
				$this->registerJS('../assets/plugins/codemirror/addon/display/fullscreen.js','head');
				$this->registerCSS('../assets/plugins/codemirror/addon/dialog/dialog.css');
				break;
		}
		

		
	}

	function displayList(){

		$theme = _var('theme');
		if( $theme ){
			$this->setMenu('manage_themes');
		}else{
			$this->setMenu('manage_modules');
		}
		
		$this->showMessage();
		

		

		$database = Marion::getDB();
		$type=_var('type');

		if( !$type ){
			$type = 'all';
		}		
		$search = _var('search');
		$this->setVar('search',$search);
		$user = Marion::getUser();
		
		
		$this->setVar('gruppo',$type);

		
		$modules_db = $database->select('*','modules',"1=1");
		//debugga($modules_db);exit;
		$modules = [];
		$list_module = [];
		if( okArray($modules_db) ){
			foreach($modules_db as $v){
				$list_module[$v['directory']] = $v;
	
			}
		}
		
		if( $theme ){
			$list = scandir('../themes'); 
		}else{
			$list = scandir('../modules'); 
			
			if( file_exists(_MARION_THEME_DIR_._MARION_THEME_."/modules")){
				$list = array_merge($list,scandir(_MARION_THEME_DIR_._MARION_THEME_."/modules"));
			}

			
		}
		
		$tipologie = [];
		foreach($list as $k => $v){
			if( $v != '.' && $v!= '..'){
				if( $theme ){
					$file = '../themes/'.$v."/config.xml"; 
					$file_theme = null;
				}else{
					$file = '../modules/'.$v."/config.xml"; 
					$file_theme = _MARION_THEME_DIR_._MARION_THEME_.'/modules/'.$v."/config.xml"; 

				}
				
				if( file_exists($file) || ($file_theme && file_exists($file_theme)) ){
					$from_theme = false;
					if( file_exists($file) ){
						$data_xml = simplexml_load_file($file);
					}else{
						$from_theme = true;
						$data_xml = simplexml_load_file($file_theme);
					}
					

					
					
					$data = (array)$data_xml->info;
					$compatibility = $this->checkCompatibility($data);
					$tipologie[] = $data['kind'];
					if( $search ){
						if ( !preg_match("/{$search}/i",$data['name']) ){
							continue;
						}
					}
					if( is_object($data['description']) ){
						$data['description'] = $data['description']->__toString();
						
					}
					if( $type != 'all' ){
						
						if(!$data['kind'] || $data['kind'] != $type ){
							//debugga($data);exit;
							continue;
						}
					}
					if( $theme ){
						$data['img'] = "../themes/".$data['tag']."/img/logo.png";
					}else{
						$data['img'] = "../modules/".$data['tag']."/img/logo.png";
					}
					
					
					if( !file_exists($data['img']) ){
					
						$data['img'] = 'assets/images/module-no-image.png';
					}
					if(isset($data_xml->linkSetting)){
						$link_setting = (array)$data_xml->linkSetting;
						if( okArray($link_setting) ){
							if( preg_match('/mod=/',$link_setting[0])){
								$data['link_setting'] = trim($link_setting[0]);
							}else{
								$data['link_setting'] = "/admin/modules/".$v."/".trim($link_setting[0]);
							}
						}
					}
					
					if( okArray($list_module) && array_key_exists($data['tag'],$list_module) ){
						$list_module[$data['tag']]['installed'] = 1;
						$list_module[$data['tag']]['from_theme'] = $from_theme;
						$list_module[$data['tag']]['dir_module'] = $v;
						$list_module[$data['tag']]['version'] = $data['version'];
						$list_module[$data['tag']]['compatibility'] = $compatibility;
						$list_module[$data['tag']]['img'] = $data['img'];
						if( isset($data['link_setting']) ){
							$list_module[$data['tag']]['link_setting'] = $data['link_setting'];
						}
						$modules[] = $list_module[$data['tag']];
					}else{
						$data['from_theme'] = $from_theme;
						$data['dir_module'] = $v;
						$data['compatibility'] = $compatibility;
						$modules[] = $data;
					}

					
				}
			}
		}
		$tipologie = array_unique($tipologie);
		sort($tipologie);
		$this->setVar('tipologies',$tipologie);
		//debugga($modules);exit;
		$this->setVar('enable_market',$this->_enable_market);
		if($this->_enable_market ){

			$modules_account = $this->getAccountModules();
			$info_modules = $this->getMarketModules();
			$this->setVar('info_modules',$info_modules);
			$this->setVar('modules_account',$modules_account);
		}
		
		$this->setVar('theme_list',$theme);
		
		
		$this->setVar('modules',$modules);
		$this->output('@core/admin/module/list.htm');
			
	}

	
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Modulo salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Modulo eliminato con successo','success');
		}
	}

	function saved(){
		$this->redirectTolist(array('saved'=>1));
	}





	function ajax(){
		$action = $this->getAction();
		$id = $this->getID();
		$theme = _var('theme');
		switch($action){
			case 'css':
				$error = null;
				$data = _var('code');
				$scss = new Compiler();
				try{
					$data_tmp = $data;
					
					$scss_variables = [
						[
							'variable' => 'BASE_URL',
							'value' =>  $this->getBaseUrl(),
							'description' => 'percorso base del sito'
						],
						[
							'variable' => 'THEME_DIR',
							'value' =>  $this->getBaseUrl()."themes/"._MARION_THEME_,
							'description' => 'percorso del tema corrente'
						],
					];
					Marion::do_action('action_register_scss_variables',[&$scss_variables]);
					$parameters = [];
					foreach($scss_variables as $v){
						$parameters[$v['variable']] = $v['value'];
					}

					$string = '';
					foreach($parameters as $key => $value){
						$string .= '$'.$key.':"'.$value.'";';
					}
					$data_tmp = $string.$data_tmp;
					$compressed = $scss->compileString($data_tmp);
					file_put_contents(_MARION_THEME_DIR_."/".$theme."/theme.scss",$data);
					file_put_contents(_MARION_THEME_DIR_."/".$theme."/theme.css",$compressed->getCss());
					
				}catch(Exception $e){
					$error = $e->getMessage();
				}
				if($error){
					$risposta = array(
						'result' => 'nak',
						'error' => $error
					);
				}else{
					$risposta = array(
						'result' => 'ok'
					);
				}
				break;
			case 'js':
				$data = _var('code');
				file_put_contents(_MARION_THEME_DIR_."/".$theme."/theme.js",$data);
				$risposta = array(
					'result' => 'ok'
				);
				break;
			case 'active':
			case 'enable':
				$action = 'active';
			case 'disable':
			case 'uninstall':
			case 'install':
			case 'seeder':
				$module = _var('module');
				$from_theme = _var('from_theme');
				if( $theme ){
					$file = "../themes/".$module."/".$module.".php";
				}else{
					if( !$from_theme ){
						$file = "../modules/".$module."/".$module.".php";
					}else{
						$file = _MARION_THEME_DIR_._MARION_THEME_."/modules/".$module."/".$module.".php";
					}
					
				}
				
				
				if( file_exists($file) ){
					
					require_once($file);
					$class = $this->getModuleClassName($module);
					
					
					if( class_exists($class) ){
						if( $from_theme ){
							$obj = new $class($module,_MARION_THEME_DIR_._MARION_THEME_."/modules/");
						}else{
							$obj = new $class($module);
						}
						
						
						
						if( is_object($obj) ){
							$obj->readXML();
							$obj->$action();
							
							
							if( $obj->errorMessage ){
								$risposta = array('result'=>'nak','errore'=>$obj->errorMessage);
							}else{
								$risposta = array('result'=>'ok');
							}
						}else{
							$risposta = array('result'=>'nak','Error');
						}
						
						
					}else{
						$risposta = array('result'=>'nak','errore'=>"classe non trovata");
					}
					
				}else{
					$risposta = array(
						'result'=> 'nak',
						'errore'=> "Modulo non trovato"
					);
				}
				
				break;
				
		}


		echo json_encode($risposta);
		
	}


	function getModuleClassName(string $string):string{
		

		$str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

	
    	return $str;

	}

	function checkCompatibility($data){
		$check = true;
		$data['compatibility'] = (array)$data['compatibility'];
		if(isset($data['compatibility'])){
			if( array_key_exists('min',$data['compatibility']) ){
				$min = $data['compatibility']['min'];
				if( version_compare(_MARION_VERSION_,$min,'<') ) $check = false;
			}

			if( array_key_exists('max',$data['compatibility']) ){
				$max = $data['compatibility']['max'];
				if( version_compare(_MARION_VERSION_,$max,'>') ) $check = false;
			}
		}
		return $check;
	}

}



?>