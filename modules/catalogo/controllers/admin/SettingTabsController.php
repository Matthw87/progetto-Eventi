<?php
use Marion\Controllers\TabsAdminModuleController;
use Marion\Core\Marion;
class SettingTabsController extends TabsAdminModuleController{
	public $_auth = 'catalog';
	public $_tab_ctrls = array('ListSettingController','ProductSettingController');
	

	function getTitle(){
		return _translate('Impostazioni catalogo');
	}

	function display(){
		
		$this->setMenu('setting_catalog');
		parent::display();
	}


}

?>