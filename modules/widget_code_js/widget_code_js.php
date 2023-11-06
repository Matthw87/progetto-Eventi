<?php
use Marion\Core\Module;
use Marion\Support\Form\Form;
class WidgetCodeJS extends Module{

	

	function install(): bool{
		$res = parent::install();
		if( $res ){
			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"code_js\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"724\",\"campo\":\"js_box\",\"etichetta\":\"js_box\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"8\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}}]}");
		}


		return $res;
	}



	function uninstall(): bool{
		$res = parent::uninstall();
		if( $res ){
			Form::delete('code_js');

		}	
		return $res;
	}

}



?>