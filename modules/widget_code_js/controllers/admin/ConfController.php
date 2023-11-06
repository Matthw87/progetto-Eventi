<?php

use Marion\Controllers\Controller;
use Marion\Core\Marion;
use Marion\Support\Form\Traits\FormHelper;

class ConfController extends Controller{
	use FormHelper;
	public $_auth = 'cms';
	public $_form_control = 'code_js';

	function setMedia(){


		$this->registerJS('/assets/plugins/codemirror/lib/codemirror.js','head');
		$this->registerJS('/assets/plugins/codemirror/mode/javascript/javascript.js','head');
		$this->registerJS('/assets/plugins/codemirror/addon/selection/active-line.js','head');
		$this->registerJS('/assets/plugins/codemirror/addon/selection/matchbrackets.js','head');
		$this->registerCSS('/assets/plugins/codemirror/lib/codemirror.css');
		$this->registerCSS('/assets/plugins/codemirror/theme/panda-syntax.css');
	}
	

	function display(){
		$database = Marion::getDB();
		$this->id_box = _var('id_box');
		$this->setVar('id_box',_var('id_box'));
		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			
			
			$array = $this->checkDataForm($this->_form_control,$formdata);
			if( $array[0] == 'ok'){
				unset($array[0]);
				
				$data = array();
				foreach($array as $k => $v){
					if( $k != '_locale_data'){
						$data[$k] = $v;
					}
				}
				if( array_key_exists('_locale_data',$array) ){
					foreach($array['_locale_data'] as $k =>$v){
						foreach($v as $k1 => $v1){
							$data[$k1][$k] = $v1;
						}
					}
				}
				
		
				
				$dati = serialize($data);
				
				$database->update('composed_page_composition_tmp',"id={$this->id_box}",array('parameters'=>$dati));
				
				$this->displayMessage('Dati salati con successo!','success');
			}else{
				$this->errors[]= $array[1];
			}
			$dati = $formdata;
			
		}else{
			$data = $database->select('*','composed_page_composition_tmp',"id={$this->id_box}");
			$dati = [];
			if( okArray($data) ){
				if( isset($data[0]['parameters']) ){
					$dati = unserialize($data[0]['parameters']);
				}
				
			}
			
		}

		$dataform = $this->getDataForm($this->_form_control,$dati);

		//debugga($dataform);exit;
		$this->setVar('dataform',$dataform);
		
		$this->output('@widget_code_js/admin/setting.htm');
	}


	
	/*function setMedia(){
		$this->registerCSS($this->getBaseUrl().'modules/code_js/css/tln.min.css');
		$this->registerJS($this->getBaseUrl().'modules/code_js/js/tln.min.js','head');
	}*/
	
	





}



?>