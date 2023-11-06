<?php
use Marion\Controllers\Controller;
class FMResponsiveAdminController extends Controller{
	public $_auth = 'cms';
	
	
	function display(){
		$this->setMenu('filemanager');
		

		
		$this->output('@filemanager/admin/filemanager.htm');
	}

	


}



?>