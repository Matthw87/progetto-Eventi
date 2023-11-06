<?php
use Marion\Controllers\Controller;
use Marion\Core\Marion;
use Marion\Support\Form\Traits\FormHelper;

class TranslateController extends Controller{
	use FormHelper;
	public $_auth = 'superadmin';
  
    
	

	function getMessages($module,$locale='it'){
		$module_dir = '../modules/'.$module."/translate";
		$list = scandir($module_dir);
		foreach($list as $v){
			$file = $module_dir.'/'.$v;
			if( is_file($file) ){
				$loc = explode('.',$v);
				if( $loc[0] == $locale ){
					$messages = $this->readFile($file,$module);
				}
			}
		}	

		//debugga($data);exit;
		$this->setVar('messages',$messages);
		
		$this->output('@developer/admin/translate_messages.htm');
	}
	

	function display(){
		/*$database = Marion::getDB();
		$modules = $database->select('*','module',"active=1");
		
		debugga($modules);exit;
		debugga($GLOBALS['_translate']);
		debugga('qua');exit;*/
		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			$this->saveMessages($formdata);
			debugga($formdata);exit;
		}
		$this->getMessages('cart_onepage');
	}
	


	function saveMessages($dati){
		$module = $dati['module'];
		$lo = $dati['locale'];
		$side = $dati['type'];
		if( file_exists(_MARION_ROOT_DIR_.'modules/'.$module.'/translate/'.$lo.'.php') ){
			$_string = "<?php \n";
			foreach($dati['messages'] as $key => $v){
				$_string .= '$GLOBALS['."'_translate']['".$module."']['".$side."']['".$key."']=\"{$v['value']}\";//{$v['label']}\n";
			}

			file_put_contents(_MARION_ROOT_DIR_.'modules/'.$module.'/translate/'.$lo.'.php',$_string);
			
		}
		debugga($_string);exit;

	}

	function readFile($file,$module,$type='frontend'){
		  $fn = fopen($file,"r");
		  $k = 0;
		  while(! feof($fn))  {
			
			$result = fgets($fn);
			
			if( trim($result) ){
				
				$data = explode('//',$result);
				//debugga($data[0]);

				$pattern = "/GLOBALS\['_translate'\]\['".$module."'\]\['".$type."'\]\['([a-zA-Z0-9]+)'\]/";
				
				$key = preg_replace($pattern,"$1",rtrim(ltrim($data[0],'$'),'";'));
				$explode = explode('=',$key);
				if( trim($data[1])){
					//debugga($explode[0]);
					$list[$explode[0]]['label'] = trim($data[1]);
					$list[$explode[0]]['value'] = trim($explode[1],'"');
				}
				/*ob_start();

				assert($data[0]);
				
				$val = $GLOBALS['_translate']['cart_onepage'];
				ob_end_clean();*/
				//debugga($key);
				/*$key = preg_replace("/GLOBALS[\'_translate\'][\'cart_onepage\'][\'frontend\'][\'/",'',$data[0]);
				$string = $data[1];
				debugga($data[0]);
				debugga($key);*/
			}
			
			//echo $result;
		  }
		  //exit;
		  //debugga($list);exit;
		  fclose($fn);
		  return $list;
	
	}
}