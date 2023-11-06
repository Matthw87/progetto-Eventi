<?php
use Marion\Core\Module;
class Filemanager extends Module{
	
	

	function install(): bool{
		$res = parent::install();

		return $res;
	}



	function uninstall(): bool{
		$res = parent::uninstall();
		
		return $res;
	}

}



?>