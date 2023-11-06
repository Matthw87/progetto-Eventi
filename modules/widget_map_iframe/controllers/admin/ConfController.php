<?php
use Marion\Controllers\Controller;
use Marion\Core\Marion;
use Marion\Support\Form\Traits\FormHelper;

class ConfController extends Controller{
	use FormHelper;
	public $_auth = 'cms';
	public $_form_control = 'widget_map_iframe';

	

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
				foreach($array['_locale_data'] as $k =>$v){
					foreach($v as $k1 => $v1){
						$data[$k1][$k] = $v1;
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
		
		$this->setVar('dataform',$dataform);

		$this->output('@widget_map_iframe/admin/setting.htm');
	}


	
	
	


	// FUNZIONI PER IL FORM
	function array_widget_iframe_zoom(){
		$toreturn = array(
			'0' => 'max zoom',
			'1' => '4000 Km',
			'2' => '2000 Km',
			'3' => '1000 Km',
			'4' => '400 km (continente)',
			'5' => '200 km',
			'6' => '100 km (nazione)',
			'7' => '50 km',
			'8' => '30 km',
			'9' => '15 km',
			'10' => '8 km',
			'11' => '4 km',
			'12' => '2 km (città)',
			'13' => '1km',
			'14' => '400 m (distretto)',
			'15' => '200 m',
			'16' => '100 m',
			'17' => '50 m (strada)',
			'18' => '20 m',
			'19' => '10 m',
			'20' => '5 m',
			'21' => '2,5 m',
			
		);

		return $toreturn;
	}


	function array_widget_iframe_view(){
		

		$toreturn = array(
			'' => 'mappa',
			'k' => 'satellite',
			'h' => 'ibrida',
			'p' => 'terreno',
		);
		return $toreturn;
		


	}

}

?>