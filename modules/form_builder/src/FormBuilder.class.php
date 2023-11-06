<?php
namespace FormBuilder;
use Marion\Core\Base;
use Marion\Core\Marion;
class FormBuilder extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'form_builder'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'codice'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'form_builder_lang'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'codice_form';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	

	public static $actions = [];
	public static $elements = [];

	private $directory_forms = 'templates/%s/form';
	


	function getFields(){
		return FormField::prepareQuery()->where('form',$this->codice)->orderBy('ordine')->get();

	}




	function check($data){
		$campi = $this->getFields();
		
		foreach($campi as $v){
			$array[$v->campo] = $data[$v->campo];
			
			if( $v->obbligatorio ){
				if( !$data[$v->campo] ){
					$array[0] = 'nak';
					$array[1] = $this->errorMissingField($v->get('etichetta'));
				}
			}

		}
		if( !$array[0] ) $array[0] = 'ok'; 
		
		return $array;

	}


	function errorMissingField($campo){
		$string = _translate('missing field %s','widget_developer');
		return sprintf($string,$campo);
	}




	function afterSave(): void{
		parent::afterSave();
		
		$locales = Marion::getConfig('locale','supportati');
		foreach($locales as $v){
			$path = sprintf($this->directory_forms,$v);
			$content = $this->get('template',$v);
			/*$campi = $this->getFields();
			
			$matches = preg_match_all('/\[(.*)\]/',$content,$array);
			foreach($array[1] as $k => $v){
				$explode = explode(' ',$v);
				$_campo = array();
				$_campo['match'] = $v;
				$_campo['name'] = trim($explode[0]);
				
				unset($explode[0]);
				foreach($explode as $t){
					$explode2 = explode(':',$t);
					$_campo['attributes'] .= $explode2[0]."='".$explode2[1]."' ";
					
					
				}
				$_campi[$_campo['name']] = $_campo;
				
			}
			

			foreach($campi as $v){
				$c = $v->campo;
				
				$dati_campo = $_campi[$c];
				


				$type = $v->getType();
				
				switch($type){
					case 'select':
						$html_el = "<select type='{$type}' name='{$c}' {$dati_campo['attributes']}></select>";
						break;
					case 'text':
						$html_el = "<input name='{$c}' type='{$type}' {$dati_campo['attributes']}>";
						break;
					case 'radio':
						$html_el = '';
						$options = $v->getOptions();
						
						
						//$html_el .= "<input name='{$c}' type='{$type}' value='{$v1}' {$dati_campo['attributes']}>{$v1}";
						
						
						break;
				}

				$key = $dati_campo['match'];
				
				$content = preg_replace("/\[{$key}\]/",$html_el,$content);

				
			}*/

			
			
			
			$nome_file = 'form_'.$this->codice.".htm";
			if( file_exists($path) ){
				$res = file_put_contents($path."/".$nome_file,$content);
				
			}
		}
		

	}


	function getTemplateHtml(){
		$nome_file = 'form_'.$this->codice.".htm";
		return $nome_file;
	}





	function sendMail($array=array()){
		$mail = _obj('Mail');
	
		
	
		$to = $this->getMailTo();
		$subject = $this->get('subject');
		$message = $this->get('message');


		$string = 'I have a match1 and a match3, and here\'s a match2';
		foreach($array as $k => $v){
			$find[] = '/\['.$k.'\]/';
			$replace[] = $v;
		}
		
		$subject = preg_replace($find, $replace, $subject);
		$message = preg_replace($find, $replace, $message);
		
		
		$mail->setFrom($this->email_mittente);
		$mail->setToFromArray($to);

		$mail->setSubject($subject);
		$mail->message = nl2br($message);
		$mail->setTemplateHtml('mail_generica.htm');
		$mail->send();
	}


	function getMailTo(){
		$values = $this->email_destinatari;
		$textAr = explode("\n", $values);
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



	public static function registerAction($class){
		self::$actions[] = $class;
	}

	public static function registerElement($element){
		self::$elements[] = $element;
	}
}

?>