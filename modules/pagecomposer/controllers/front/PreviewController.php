<?php
use Marion\Controllers\FrontendController;
use Marion\Entities\Cms\PageComposer;
class PreviewController extends FrontendController{
	
	function setMedia(){

			parent::setMedia();

			$this->registerCSS('assets/plugins/codemirror/lib/codemirror.css');
			$this->registerCSS('assets/plugins/codemirror/addon/display/fullscreen.css');
			$this->registerCSS('assets/plugins/codemirror/theme/night.css');


			$this->registerJS('assets/plugins/codemirror/lib/codemirror.js','head');
			$this->registerJS('assets/plugins/codemirror/mode/javascript/javascript.js','head');
			$this->registerJS('assets/plugins/codemirror/mode/css/css.js','head');
			$this->registerJS('assets/plugins/codemirror/addon/selection/active-line.js','head');
			$this->registerJS('assets/plugins/codemirror/addon/selection/matchbrackets.js','head');
			$this->registerJS('assets/plugins/codemirror/addon/display/fullscreen.js','head');
			
			
			
			$this->registerJS('modules/pagecomposer/js/pagecomposer_editor_css.js','end');

			

			
	}

	function preview($id){
		
		
		
		$composer = new PageComposer($id,true);
		$composer->addDataToCtrl($this);
		
		//$this->setVar('pagina',$_page_obj);
		//$this->setVar('id_pagecomposer',$_page_obj->id_adv_page);
		
		//$this->setVar('layout','layouts/composer/base.htm');
		$this->setVar('disabled_editing_tab',true);
		$this->setVar('layout','layouts/composer/'.$composer->template_page);

		//debugga('layouts/composer/'.$composer->template_page);exit;
		$this->setVar('preview_page',1);
		$this->output('pagecomposer.htm');
	}


}