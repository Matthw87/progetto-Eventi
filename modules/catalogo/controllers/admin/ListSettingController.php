<?php
use Marion\Controllers\TabsAdminModuleController;
use Marion\Core\Marion;
use Marion\Support\Form\Traits\FormHelper;

class ListSettingController extends TabsAdminModuleController{
	use FormHelper;
	public $_auth = 'catalog';
	
	public static function getTitleTab(){
		return _translate('Navigazione');
	}


	function display(){
		if( $this->isSubmitted()){
				
			
			

			$dati = $this->getFormdata();
			
			if( $dati['enable_select_order_product'] ){
				$campi_aggiuntivi['parameters_select_order_product']['obbligatorio'] = 1;
			}

			$array = $this->checkDataForm('setting_catalog',$dati,$campi_aggiuntivi);
			if( $array[0] == 'ok'){
				unset($_SESSION['number_view_catalog_section']);
				unset($array[0]);
				foreach($array as $k => $v){
					Marion::setConfig('catalog',$k,$v);
				}
				Marion::refresh_config();
				$this->displayMessage('Configurazione salvata con successo');
				$dati = $array;
			}else{
				$this->error[] = $array[1];
				
			}
			



		}else{
			$dati = Marion::getConfig('catalog');
		}
		
		

		
		
		

		$dataform = $this->getDataForm('setting_catalog',$dati);
		$this->setVar('dataform',$dataform);

		
		
		$this->output('@catalogo/setting_tabs/list.htm');
	}


}

?>