<?php
namespace Marion\Controllers;
class TabsAdminController extends Controller{
	public $_titles;
	public $_tab_ctrls;
	public $_tab_index;
	public $_active_cltr;
	public $_tabs_container_template = '@core/layouts/tabs.htm';
	

	function getTitle(){
		//titolo della tabs
		return '';
	}

	//prendo i titoli della tabs
	function getTabTitles(){
		foreach($this->_tab_ctrls as $ctrl){
			$this->_titles[] = $ctrl::getTitleTab();
		}
	}
	

	//function load controller children
	function loadCtrlChildren(){

		if( okArray($this->_tab_ctrls) ){
			foreach($this->_tab_ctrls as $class){
				$file = _MARION_ROOT_DIR_."backend/controllers/".$class.".php";
				if( file_exists($file) ){
					require_once($file);
				}
				
			}
			
		}
	}

	function init($options=array()){
		parent::init($options);
		$this->loadCtrlChildren(); //carico i controller figli
		$this->getTabTitles();
		$this->setVar('titles',$this->_titles);
		$this->_tab_index = _var('tabIndex');
		if(!$this->_tab_index ) $this->_tab_index = 0;
		$this->_active_cltr = $this->_tab_ctrls[$this->_tab_index];
		

		
	}

	function importMediaChild($ctrl){
		// importo i JS e i css nel controller padre
		
		if( okArray($ctrl->_javascript) ){
			foreach($ctrl->_javascript as $pos => $list){
				
				foreach($list as $priority => $files){
					foreach($files as $v){
						$this->registerJS($v['url'],$pos,$priority,$v['options']);
					}
				}
			}
		}
		if( okArray($ctrl->_css) ){
			foreach($ctrl->_css as $v){
				$this->registercSS($v['url'],$v['options']);
			}
		}
	}

	function loadCurrentTab(){
		$this->setVar('tabIndex',$this->_tab_index);
		
		$child_options = array(
			'ctrl'=>$this->_ctrl,
			'url_script'=> $this->getUrlScript()."&tabIndex="._var('tabIndex'),
			'twig_values' => array(
				'tabIndex' => $this->_tab_index
			)
		);

		
		
		ob_start();
		$ctrl = new $this->_active_cltr($child_options);
		$html = ob_get_contents();
		ob_end_clean();


		$this->importMediaChild($ctrl);
		
		$this->setVar('title',$this->getTitle());
		$this->setVar('tab',$html);
	}

	function display(){
		
		$action = $this->getAction();

		if( $action == 'delete'){
			
			$this->delete();
			
		}

		$this->loadCurrentTab();
		
		$this->output($this->_tabs_container_template);

	}


	function ajax(){
		$child_options = array(
			'ctrl'=>$this->_ctrl,
			'url_script'=> $this->getUrlScript()."&tabIndex="._var('tabIndex')
		);
		$ctrl = new $this->_active_cltr($child_options);
	}


	function delete(){
		if (is_a($this->_active_cltr, 'delete')) {
			$child_options = array(
				'ctrl'=>$this->_ctrl,
				'url_script'=> $this->getUrlScript()."&tabIndex="._var('tabIndex')
			);
			$ctrl = new $this->_active_cltr($child_options);
		}
		
	}




}
?>