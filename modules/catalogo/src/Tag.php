<?php
namespace Catalogo;
use Marion\Core\{Base,Marion};
use Illuminate\Database\Capsule\Manager as DB;
class Tag extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'product_tags'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'product_tag_langs'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'product_tag_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 

	public $id;
	public $label;


	/**
	 * Get url page product by tag
	 *
	 * @return string
	 */
	function getUrl(): string{
		return _MARION_BASE_URL_."catalogo/tag/".$this->label;
	}



	function getProductIds(){
		$toreturn = array();
		$ids = DB::table('product_tag_associations')->where("product_tag_id",$this->id)->get()->toArray();
		
		if( okArray($ids) ){
			foreach($ids as $v){
				$toreturn[] = $v->product_id;
			}
		}
		return $toreturn;
	}

	function afterSave(): void{
		parent::afterSave();
		
		//prendo tutti i prodotti che hanno questo tag e svuoto la tabella di ricerca
		$list = $this->getProductIds();
		if( okArray($list) ){
			$database = Marion::getDB();
			foreach($list as $v){
				$database->insert('product_search_changed',array('id_product' => $v));
			}
		}
		
	}
		
}

?>