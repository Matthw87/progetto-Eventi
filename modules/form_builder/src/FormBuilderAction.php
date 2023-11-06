<?php
namespace FormBuilder;

class FormBuilderAction{
	

	//in questo metodo possono essere registrati i JS e CSS richiamando i metodi  Pagecomposer::registerJS Pagecomposer::registerCSS
	public static function registerMedia(){
		/*
			PageComposer::regitserJS('/path/file/js.js');
			PageComposer::regitserCSS('/path/file/css.css');


		*/
	}

	
	//Questo metodo aggiunge delle azioni da effettuare dopo il submit del form che l'utente può abilitare in fase di creazione del form. 
	public static function register(){
		
		/* se ci sono delle azioni allora occore restituire un array di tipo chiave valore dove la chiave è l'identificativo dell'azione mentre il valore è il nome dell'azione
			
			return array(
				'action1' => 'Nome azione 1',
				'action2' => 'Nome azione 2',

			);


		*/
		//se non ci sono azioni da eseguire deve restituire false
		return false;
	}


	


	//Questo metodo viene richiamato all'invio del form qualora per lo stesso sia registrata l'azione
	/*
		INPUT:
		$action: azione
		$data: dati del form sottomesso
		$params: parametri di configurazione del widget
		$ctrl: controller SubmitController presente in form_builder


	*/
	public static function execute(string $action, array $data, array $params, $ctrl){
				
		switch($action){
			case 'action1':
				//insert code action
				break;

		}

		
		/*
			// se ci sono errori occorre restituire il messaggio di errore
			return $error_string;

		*/

		// se non ci sono errori
		return true;
	}

	
	//Questo metodo restituisce il messaggio in caso di successo relativo ad un azione registrata al form
	public static function successMessage(string $action){
		$message = '';
		switch($action){
			case 'action1':
				$message = 'Ok!';
				//insert code action
				break;

		}
		return $message;
	}

}


?>