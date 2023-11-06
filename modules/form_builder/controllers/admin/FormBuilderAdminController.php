<?php
use Marion\Controllers\AdminModuleController;
use FormBuilder\FormBuilder;
class FormBuilderAdminController extends AdminModuleController{
	public $_auth = 'cms';

	function displayList(){
		
		$list = FormBuilder::prepareQuery()->get();
		$this->setVar('list',$list);

		$this->output('@form_builder/admin/list.htm');
	}

	function setMedia(){

		$this->registerCSS(_MARION_BASE_URL_.'plugins/codemirror/lib/codemirror.css');
		$this->registerCSS(_MARION_BASE_URL_.'plugins/codemirror/theme/panda-syntax.css');
		$this->registerJS(_MARION_BASE_URL_.'plugins/codemirror/lib/codemirror.js','head');
		$this->registerJS(_MARION_BASE_URL_.'plugins/codemirror/addon/hint/show-hint.js','head');
		$this->registerJS(_MARION_BASE_URL_.'plugins/codemirror/addon/hint/xml-hint.js','head');
		$this->registerJS(_MARION_BASE_URL_.'plugins/codemirror/addon/hint/html-hint.js','head');
		$this->registerJS(_MARION_BASE_URL_.'plugins/codemirror/mode/xml/xml.js','head');
		$this->registerJS(_MARION_BASE_URL_.'plugins/codemirror/mode/javascript/javascript.js','head');
		$this->registerJS(_MARION_BASE_URL_.'plugins/codemirror/mode/css/css.js','head');

		$this->registerJS(_MARION_BASE_URL_.'plugins/codemirror/mode/htmlmixed/htmlmixed.js','head');

		$this->registerJS(_MARION_BASE_URL_.'modules/form_builder/js/formbuilder.js','end');

		
	}



	function displayForm(){
		
		$action = $this->getAction();
		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('form_builder',$dati);
			if( $array[0] == 'ok'){
				if( $action == 'add'){
					$obj = FormBuilder::create();
				}else{
					$obj = FormBuilder::withId($array['codice']);
				}
				
				$obj->set($array)->save();
				//$this->displayMessage('Form salvato con successo');
				$this->redirectToList();
			}else{
				$this->errors[] = $array[1];
			}

		}else{
			if( $action == 'edit' ){
				$id = $this->getID();
				$obj = FormBuilder::withId($id);
				$dati = $obj->prepareForm2();
				

				$this->setVar('campi',$obj->getFields());
			}

		}
		
		$dataform = $this->getDataForm('form_builder',$dati);
		$this->setVar('dataform',$dataform);
		$this->output('@form_builder/admin/form.htm');
	}
}