<?php
namespace Catalogo;
use Marion\Core\{Base,Marion};
class Manufacturer extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'product_manufacturers'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'product_manufacturer_langs'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'product_manufacturer_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 
	public $image;
	public $id;

	//restituisce l'immagine all'indice specificato del formato specificato
	function getUrlImage($type='original',$watermark=true,$name_image=NULL){
		if( $this->image ){
			$database = Marion::getDB();
		
			$img = $database->select('i.*',"image as i join imageComposed as c on c.{$type}=i.id","c.id={$this->image}");
			if(okArray($img) ){
				if( $name_image ){
					$name = $name_image;
				}else{
					$name = $img[0]['filename_original'];
					$name = explode('.',$name);
					$name = Marion::slugify($name[0]);
					$name = $name[0].".".$img[0]['ext'];
				}
				
				$type_short = $this->getTypeImageUrl($type);
				
				if( !$watermark ){
					return "/img/{$this->image}/{$type_short}-nw/{$name}";
				}else{
					return "/img/{$this->image}/{$type_short}/{$name}";
				}
			}
		}
		return '';
	}

	function getTypeImageUrl($type){
		switch( $type ){
			case 'thumbnail':
				$type = 'th';
				break;
			case 'small':
				$type = 'sm';
				break;
			case 'medium':
				$type = 'md';
				break;
			case 'large':
				$type = 'lg';
				break;
			case 'original':
				$type = 'or';
				break;
			default:
				$type='or';

		}
		return $type;

	}



	function getUrl(){
		if( isMultilocale()){
			return _MARION_BASE_URL_.$GLOBALS['activelocale']."/brand/".Marion::slugify($this->get('name'))."_".$this->id.".htm";
		}else{
			return _MARION_BASE_URL_."brand/".Marion::slugify($this->get('name'))."_".$this->id.".htm";
		}
		
	}


	function afterSave(): void{
		parent::afterSave();
		
		//prendo tutti i prodotti che hanno questo manufacurer e svuoto la tabella di ricerca
		$database = Marion::getDB();
		$list = $database->select('id','products',"parent_id=0 AND deleted = 0 AND product_manufacturer_id = {$this->id}");

		
		if( okArray($list) ){
			foreach($list as $v){
				$database->insert('product_search_changed',array('id_product' => $v['id']));
			}
		}
		
	}
		
}

?>