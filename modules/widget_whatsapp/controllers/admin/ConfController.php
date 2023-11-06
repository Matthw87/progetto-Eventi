<?php
use Marion\Controllers\Controller;
use Marion\Core\Marion;
use Marion\Support\Form\Traits\FormHelper;

class ConfController extends Controller{
	use FormHelper;
	
	public $_auth = 'cms';
	public $_form_control = 'whatsapp_icon_conf';

	function setMedia(){
		parent::setMedia();
		$this->loadJS('spectrum');
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
			
			if( okArray($data) ){
				if( isset($data[0]['parameters']) ){
					$dati = unserialize($data[0]['parameters']);
				}else{
					$dati = [];
				}
				
			}
			
		}

		$dataform = $this->getDataForm($this->_form_control,$dati);
		
		$this->setVar('dataform',$dataform);

		$this->output('@widget_whatsapp/admin/setting.htm');
	}


	
	
	


	// FUNZIONI PER IL FORM
	function displayMode(){
		$toreturn = array(
			'show' => 'show',
			'hide' => 'hide',
			'fade' => 'fade'
		);

		return $toreturn;
	}


}



?>