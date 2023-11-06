<?php
namespace Marion\Components;
class PageComposerComponent extends WidgetComponent{
	public $_parameters;
	public $_data_composer;
	public $_container_box;

	

	function build($data=array()){
		
	}

	function registerJS($data=NULL){

	}

	function registerCSS($data=NULL){

	}

	function init($data){
		
		$this->setDataComposer($data);
		$this->getParameters($data);
		$this->id_box = $data['id'];

		//$this->getBoxContainer($data);
	}
	function getDataComposer(){
		return $this->_data_composer;
	}
	function setDataComposer($data){
		$this->_data_composer = $data;

	}

	function getIdBox(){
		return $this->id_box;
	}
	function setParameters($parameters){
		$this->_parameters = $parameters;
	}
	
	function getParameters($data=null){
		if( okArray($this->_parameters) ) return $this->_parameters;
		if( !$data ) return;
		if( isset($data['parameters']) ){
			$parameters = unserialize($data['parameters']);
		}else{
			$parameters = [];
		}
		
		$this->setParameters($parameters);
		return $parameters;
	}

	function getBoxContainer(){
		
		return $this->_container_box;
	}

	public static function getTypeBox($parent_container){
		return $parent_container;
	}
	


	
	


	function isAvailable($box){
		

		return true;
	}


	function getLogo(){

		$reflector = new \ReflectionClass(get_class($this));
		
		$path = dirname($reflector->getFileName())."/img/logo.png";
		
		if( file_exists($path) ){
			
			return  substr($path, strlen($_SERVER['DOCUMENT_ROOT']));
		}else{
			return false;
		}
		
	}

	
	function isEditable(){
		

		return true;
	}

	function isCopyable(){
		

		return true;
	}
	

	function customCSS(){
		

		return true;
	}



	function render($parameters=array()){

	}


	function initTemplateTwig(): void{
		$reflector = new \ReflectionClass(get_class($this));
		
		$path = dirname($reflector->getFileName());
		
		$explode = explode('/',$path);
		$module = $explode[count($explode)-1];
		$this->setModule($module);

		parent::initTemplateTwig();
	}
	




	//metodo che viene richiamato quando una pagina viene esportata
	function export($directory){

	}

	//metodo che viene richiamato quando una pagina viene importata
	function import($directory){

	}



}




?>