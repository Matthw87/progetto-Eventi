<?php
namespace FormBuilder;
class FormBuilderElement
{
	
	//identificativo univoco del Form Builder Element. Non deve coincidere con quello degli altri elementi
	public static function getID():string{
			return 'id_field';
	}

	
	//Restituisce il nome del campo
	public static function getName():string{
			return 'nome elemento';
	}


	//crea l'html del campo
	public static function buildHtml(array $field,array $params):string{
		
		return "";
	}
	
	//in questo metodo possono essere registrati i JS e CSS richiamando i metodi  Pagecomposer::registerJS Pagecomposer::registerCSS
	public static function registerMedia(){
		/*
			PageComposer::regitserJS('/path/file/js.js');
			PageComposer::regitserCSS('/path/file/css.css');


		*/
	}
	
	//in questo metodo viene effettuato il controllo sul campo
	public static function check($value=null){
		
		/*
			// se ci sono errori occorre restituire il messaggio di errore
			return $error_string;

		*/
			
		// se non ci sono errori
		return true;
	}
	





}

?>