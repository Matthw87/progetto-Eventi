<?php
namespace Marion\Entities;
use Marion\Core\Marion;
// i dati sono presi da https://github.com/umpirsky/country-list
class Country{
	
	public $id;
	public $continent;
	public $_localeData;

	//prende tutti gli oggetti di tipo nazione
	public static function getAll(){
		$database = Marion::getDB();
		$list = $database->select('id,continent','country');
		if( okArray($list) ){
			foreach($list as $v){
				$temp = self::withId($v['id']);
				$temp->continent = $v['continent'];
				$toreturn[] = $temp;
			}
		}

		return $toreturn;

	}


	//restituisce l'oggetto a partire dal suo ID
	public static function withId($id=null){
		if( !$id ) return false;
		$id = strtoupper($id);
		$obj = new Country();
		$obj->id = $id;
		$obj->getLocaleData();
		return $obj;
		
	}

	
	
	//prende i dati locali in fase di inizializzazionde dell'oggetto
	function getLocaleData(){
		if( $this->id){
			$database = Marion::getDB();
			$countryData = $database->select('*','countryLocale',"country='{$this->id}'");
			foreach($countryData as $v){
				$this->_localeData[$v['locale']] = $v['name'];
			}
		}
		return $this;
	}

	

	//restituisce un valore della nazione
	function get($val='name',$locale=NULL){
		if( !$locale ) $locale = $GLOBALS['activelocale'];
		switch( $val ){
			case 'id':
				$val = $this->$val;
				break;
			case 'name':
				$val = $this->_localeData[$locale];
				break;
		}
		
		return $val;

	}

	function getId(){
		return $this->id;
		
	}

}

?>