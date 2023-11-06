<?php
namespace Marion\Controllers;
class TabsAdminModuleController extends TabsAdminController{
	

	function init($options=array()){
		$this->_module = _var('mod');
		parent::init($options);
		$this->_url_script .= "&mod={$this->_module}";
		
	}


	function loadCtrlChildren(){
		
		if( okArray($this->_tab_ctrls) ){
			
			foreach($this->_tab_ctrls as $class){
				$files = array();
				$files[] = _MARION_MODULE_DIR_.$this->_module."/controllers/".$class.".php";
				$files[] = _MARION_MODULE_DIR_.$this->_module."/controllers/admin/".$class.".php";
				
				foreach($files as $file){
					if( file_exists($file) ){
						require_once($file);
						break;
					}
				}
				
			}
			
		}
	}

	function initTemplateDir(){	
		$this->addTwingTemplatesDir("../modules/{$this->_module}/templates/admin");
	}

	function setTemplateVariables(){
		parent::setTemplateVariables();
		$this->setVar('module',$this->_module);
	}




}
?>