<?php
use Marion\Core\Marion;
use FormBuilder\FormBuilder;
use Marion\Controllers\Controller;
use Marion\Support\Form\Traits\FormHelper;

class EditorFormController extends Controller{
	use FormHelper;
	public $_auth = 'cms';

	

	function setMedia(){

			parent::setMedia();

			$this->registerCSS(_MARION_BASE_URL_.'assets/plugins/codemirror/lib/codemirror.css');
			$this->registerCSS(_MARION_BASE_URL_.'assets/plugins/codemirror/addon/display/fullscreen.css');
			$this->registerCSS(_MARION_BASE_URL_.'assets/plugins/codemirror/theme/night.css');
			
			

			$this->registerJS(_MARION_BASE_URL_.'assets/plugins/codemirror/lib/codemirror.js','head');


			
			
			$this->registerJS(_MARION_BASE_URL_.'assets/plugins/codemirror/mode/css/css.js','head');
			$this->registerJS(_MARION_BASE_URL_.'assets/plugins/codemirror/mode/javascript/javascript.js','head');
			$this->registerJS(_MARION_BASE_URL_.'assets/plugins/codemirror/mode/xml/xml.js','head');
			$this->registerJS(_MARION_BASE_URL_.'assets/plugins/codemirror/mode/htmlmixed/htmlmixed.js','head');
			
			$this->registerJS(_MARION_BASE_URL_.'assets/plugins/codemirror/mode/htmlembedded/htmlembedded.js','head');
			$this->registerJS(_MARION_BASE_URL_.'assets/plugins/codemirror/addon/mode/multiplex.js','head');
			$this->registerJS(_MARION_BASE_URL_.'assets/plugins/codemirror/addon/selection/active-line.js','head');
			$this->registerJS(_MARION_BASE_URL_.'assets/plugins/codemirror/addon/selection/matchbrackets.js','head');
			$this->registerJS(_MARION_BASE_URL_.'assets/plugins/codemirror/addon/display/fullscreen.js','head');
			
			
			
			$this->registerJS(_MARION_BASE_URL_.'modules/form_builder/js/formbuilder.js','end');

			
	}
	

	function display(){
	
	
		$id_box = _var('id_box');
		$tab = _var('tab');
		if( !$tab ) $tab = 'generale';
		$this->setVar('id_box',$id_box);
		$this->setVar('tab',$tab);
		

		$database = Marion::getDB();
		$_data = $database->select('*','composed_page_composition_tmp',"id={$id_box}");
		
		if( okArray($_data) ){
			$dati = [];
			if( isset($_data[0]['parameters']) ){
				$dati = unserialize($_data[0]['parameters']);
			}
			
			
			
			if( !okArray($dati) ){
				$dati = array();
			}else{
				$dati_email = isset($dati['email'])?$dati['email']:null;
				$dati_campi = isset($dati['fields'])?$dati['fields']:null;;

				if( okArray($dati_campi) ){
					foreach($dati_campi as $k => $v){
						$campi[$k+1] = $v;
					}
					$this->setVar('campi',$campi);
				}
				
				
			}
			
			
		}

		
		if( $this->isSubmitted()){
			switch($tab){
				case 'email':
					list($status_email,$dati_email,$data_email) = $this->submitFormEmail();
					if( $status_email == 'ok' ){
						$dati['email'] = $data_email;
						$this->displayMessage('Dati salvati con successo!');
					}else{
						$this->errors[] = $data_email;
					}
					break;
				case 'template':
					$formdata = $this->getFormdata();
					$dati['html'] = $formdata['html'];
					$this->displayMessage('Dati salvati con successo!');
					break;
				case 'generale':
					$campi_aggiuntivi = array();
					$formdata = $this->getFormdata();
					if( isset($formdata['enable_redirect']) ){
						$campi_aggiuntivi['redirect_url']['obbligatorio'] = 1;
					}
					$array = $this->checkDataForm('form_builder_general',$formdata,$campi_aggiuntivi);
					if( $array[0] == 'nak' ){
						$this->errors[] = $array[1];
					}
					$dati['action_submit'] = isset($array['action_submit'])?$array['action_submit']:'';
					$dati['recaptcha'] = $array['recaptcha'];
					$dati['enable_redirect'] = $array['enable_redirect'];
					$dati['redirect_url'] = $array['redirect_url'];
					$dati['label'] = $array['label'];
					$dati['key_site_recaptcha'] = $array['key_site_recaptcha'];
					$dati['key_secret_recaptcha'] = $array['key_secret_recaptcha'];
					$this->displayMessage('Dati salvati con successo!');
					break;
			}
			$toupdate = serialize($dati);
				
			$database->update('composed_page_composition_tmp',"id={$id_box}",array('parameters'=>$toupdate));
		}
		

		
		$dataform_general = $this->getDataForm('form_builder_general',$dati);
		$dataform_email = $this->getDataForm('form_builder_email',isset($dati_email)?$dati_email:null);
		$dataform_template = $this->getDataForm('form_builder_template',$dati);
		

		$this->setVar('dataform_general',$dataform_general);
		$this->setVar('dataform_email',$dataform_email);
		$this->setVar('dataform_template',$dataform_template);
		$this->output('@form_builder/admin/editor_form.htm');
	}


	function submitFormEmail(){
		$campi_aggiuntivi = array();
		$dati = $this->getFormdata();
		if( !$dati['use_field_form'] ){
			$campi_aggiuntivi['field_form_sender']['obbligatorio'] = 0;
			$campi_aggiuntivi['email_mittente']['obbligatorio'] = 1;
		}else{
			$campi_aggiuntivi['field_form_sender']['obbligatorio'] = 1;
			$campi_aggiuntivi['email_mittente']['obbligatorio'] = 0;
		}
		$array = $this->checkDataForm('form_builder_email',$dati,$campi_aggiuntivi);
		if( $array[0] == 'ok' ){
			$data = $this->preparaDati($array);
			
		}else{
			$data = $array[1];
		}
		

		return array($array[0],$dati,$data);
	}





	function preparaDati($array){
		
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
		return $data;
	}
		


	function createFormFieldListAjax($id_box,$campi){

		if( okArray($campi) ){
			foreach($campi as $k => $v){
				$campi2[$k+1] = $v;
			}
			$this->setVar('campi',$campi2);
		}
		
		$this->setVar('id_box',$id_box);
		ob_start();
		$this->output('@form_builder/admin/campi.htm');
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}


	function ajax(){
		
		
		$action = _var('action');
		
		$database = Marion::getDB();
		$id_box = _var('id_box');
		$_data = $database->select('*','composed_page_composition_tmp',"id={$id_box}");
		if( okArray($_data) ){
			if( isset($_data[0]['parameters']) ){
				$_dati_old = unserialize($_data[0]['parameters']);
				if( !okArray($_dati_old) ){
					$_dati_old = array();
				}
			}
			
		}
		
		switch($action){
			case 'add_field':
				
				
				$this->setVar('id_box',$id_box);
				if( _var('indice') ){
					$dati = $_dati_old['fields'][_var('indice')-1];
					$dati['indice'] = _var('indice');
				}
				$dati['id_box'] = _var('id_box');
				
				$dataform = $this->getDataForm('form_builder_field',$dati);
				$this->setVar('dataform',$dataform);
				
				ob_start();
				$this->output('@form_builder/admin/campo.htm');
				$html = ob_get_contents();
				ob_end_clean();

				$risposta = array(
					'result' => 'ok',
					'html' => $html
				);
				

				break;
			case 'del_field':
				$id_box = _var('id_box');
				$indice = _var('indice');
				unset($_dati_old['fields'][$indice-1]);

				
				$toupdate = serialize($_dati_old);
					
				$database->update('composed_page_composition_tmp',"id={$id_box}",array('parameters'=>$toupdate));

					
				

				$risposta = array(
					'result' => 'ok',
					'html' => $this->createFormFieldListAjax($id_box,$_dati_old['fields'])
				);
				break;
			case 'save_field':
				$dati = _formdata();

				
				$array = $this->checkDataForm('form_builder_field',$dati);
				if( $array[0] == 'ok' ){


					$dati = $this->preparaDati($array);
					
					
					if( $dati['indice'] ){
						$_dati_old['fields'][$dati['indice']-1] =$dati;
					}else{
						$_dati_old['fields'][] =$dati;
					}
					
					$toupdate = serialize($_dati_old);
					
					$database->update('composed_page_composition_tmp',"id={$id_box}",array('parameters'=>$toupdate));

						
					

					$risposta = array(
						'result' => 'ok',
						'html' => $this->createFormFieldListAjax($id_box,$_dati_old['fields'])

					);
				}else{
					$risposta = array(
						'result' => 'nak',
						'error' => $array[1]
					);
				}
				

				
				

				break;

		}
		
		
		echo  json_encode($risposta);
		exit;
	}
	


	function type_html(){
		

		$list = array(
			'checkbox' => 'checkbox (unico valore)',
			'checkboxes' => 'checkbox (molti valori)',
			'email' => 'email',
			'file' => 'file',
			'hidden' => 'hidden',
			'multiselect' => 'multiselect',
			'datepicker' => 'datepicker',
			'password' => 'password',
			'radio' => 'radio',
			'select' => 'select',
			'text' => 'text',
			'textarea' => 'textarea'

		);
		
		$FormBuilderElements  = array();
		foreach(FormBuilder::$elements as $class){
			if( !is_object($class) ){
				$class = new $class();
			}
			if(is_subclass_of($class,'FormBuilder\FormBuilderElement')) {

				$FormBuilderElements[] = $class;
			}
		}
		foreach($FormBuilderElements as $v){
			$list[$v::getID()] = $v::getName();
		}
		
		return $list;
	}
	


	function actionsAfterSubmit(){
			
			$list = array(
				'send_mail' => 'Invia mail'
			);

		
			
			foreach(FormBuilder::$actions as $class){
				if( !is_object($class) ){
					$class = new $class();
				}
				if(is_subclass_of($class,'FormBuilder\FormBuilderAction')){
					
					$FormBuilderActions[] = $class;
				} 
			}
			

			if( isset($FormBuilderActions) ){
				foreach($FormBuilderActions as $v){
					$azioni= $v::register();
					if( okArray($azioni) ){
						foreach($azioni as $k => $v){
							$list[$k] = $v;
						}
					}
				}
			}
			

			return $list;
	}
	



}



?>