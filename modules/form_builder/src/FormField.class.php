<?php
namespace FormBuilder;
use Marion\Core\Base;
class FormField extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'form_field'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'codice'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'form_field_lang'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'id_form_field';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	
	

	function afterLoad(){
		parent::afterLoad();
		$this->loadTypes();
	}

	function beforeSave(){
		parent::beforeSave();
		if( $this->hasOptions()){
			$this->tipo_valori = 1;
		}else{
			$this->tipo_valori = 0;
		}
	}

	function loadTypes(){
		if( !okArray($GLOBALS['widget_developer_form_type']) ){
			$database = _obj('Database');
			$tipi  = $database->select('*','form_type');
			if( okArray($tipi) ){
				foreach($tipi as $v){
					$GLOBALS['widget_developer_form_type'][$v['codice']] = $v['etichetta'];
				}
			}
		}
		$this->list_types = $GLOBALS['widget_developer_form_type'];
	}

	function getType(){
		return $this->list_types[$this->type];
	}

	function getOptions(){
		$type = $this->getType();
		
		if( $this->getType() == 'select'){
			$list[0] = '--select--';
		}
		$options = $this->get('options');


		$textAr = explode("\n", $options);
		$textAr = array_filter($textAr, 'trim'); // remove any extra \r characters left behind
		
		
		foreach ($textAr as $line) {

			if( in_array($type,array('select','multiselect')) ){
				$list[trim($line)] = trim($line);
			}else{
				$list[] = trim($line);
			}
		}

		return $list;
	}

	function getHtml(){
		$type = $this->getType();
		$html_el = "<div>\r\n<label>".$this->get('etichetta')."</label>\r\n";
		switch($type){
			case 'multiselect':
				$html_el .= "<select type='{$type}' name='{$this->campo}[]' multiple></select>\r\n";
				break;
			case 'select':
				$html_el .= "<select type='{$type}' name='{$this->campo}'></select>\r\n";
				break;
			case 'text':
				$html_el .= "<input name='{$this->campo}' type='{$type}'>\r\n";
				break;
			case 'radio':
				$options = $this->getOptions();
				
				foreach($options as $v1){
					$html_el .= "<input name='{$this->campo}' type='{$type}' value='{$v1}' id='{$this->campo}_{$v1}'>{$v1}\r\n";
				}
				break;
			case 'checkbox':
				$options = $this->getOptions();
				
				foreach($options as $v1){
					$html_el .= "<input name='{$this->campo}' type='{$type}' value='{$v1}' id='{$this->campo}_{$v1}'>{$v1}\r\n";
				}
				
				break;
		}
		$html_el .= '</div>';
		return $html_el;
	}
	


	/*function getOptions(){
		
		if( $this->getType() == 'select'){
			$list[] = '--select--';
		}
		$options = $this->get('options');
		$textAr = explode("\n", $options);
		$textAr = array_filter($textAr, 'trim'); // remove any extra \r characters left behind
		
		
		foreach ($textAr as $line) {
			$list[] = trim($line);
		}

		return $list;
	}*/

	function hasOptions(){
		$type = $this->getType();
		$hasOptions = array('select','radio','multiselect','checkbox');
		return in_array($type,$hasOptions);
	}
	

}

?>