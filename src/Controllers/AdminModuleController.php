<?php
namespace Marion\Controllers;
use Marion\Controllers\Elements\UrlButton;
use Marion\Controllers\AdminController;
class AdminModuleController extends AdminController{
		

		function setListToolButtons(){
			if($this->getAction() == 'list'){
				
				$this->addToolButton(
					(new UrlButton('add'))
					->setText(_translate('add'))
					->setUrl($this->getUrlAdd())
					->setIconType('icon')
					->setClass('btn btn-principale')
					->setIcon('fa fa-plus')
				);
			}
			if(in_array($this->getAction(),array('edit','add'))){
				
				$this->addToolButton(
					(new UrlButton('back'))
					->setText(_translate('back'))
					->setUrl($this->getUrlList())
					->setIconType('icon')
					->setClass('btn btn-secondario')
					->setIcon('fa fa-arrow-left')
				);
			}
		}

		function init($options=array()){
			
			$this->_module = _var('mod');
			$this->loadModuleClasses();
			parent::init($options);
			$this->_url_script .= "&mod={$this->_module}";
			$this->tool_buttons = [];
			$this->resetToolButtons();
			$this->setListToolButtons();
			
			
		}

	
		function initTemplateDir(){
			$this->addTwingTemplatesDir("../modules/{$this->_module}/templates/admin",$this->_module);
		}



		function loadModuleClasses(){
			$directory = "../modules/{$this->_module}/classes";
			if( is_dir($directory) ){
				$classes = scandir($directory);
				foreach($classes as $v){
					$file = $directory."/".$v;
					if( is_file($file) ){
						require_once($file);
					}
				}
			}
			
		}


		function setTemplateVariables(){
			parent::setTemplateVariables();
			$this->setVar('module',$this->_module);
		}

	
}


?>