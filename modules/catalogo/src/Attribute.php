<?php
namespace Catalogo;
use Marion\Core\Base;
class Attribute extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'product_attributes'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'product_attribute_langs'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'product_attribute_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica

	public $id;
	

	/**
	 * restituisce i valori dell'attributo
	 *
	 * @return array
	 */
	function getValues(): array{
		return AttributeValue::prepareQuery()
			->where("product_attribute_id",$this->id)
			->orderBy('order_view','ASC'
			)->get();
		
	}


	/**
	 * restituisce i valori dell'attributo in forma chiave => valore
	 *
	 * @param string $lang
	 * @return array
	 */
	function getSelectValues(string $lang = NULL ): array{		
		if(!$lang) $lang = _MARION_LANG_;
		$name = $this->get('name',$lang);
		$salectValues = [
			null => $name
		];
		$values = $this->getValues();
		
		$values = $this->getValues();
		if(okArray($values)){
			foreach($values as $v){
				$salectValues[$v->getId()] = $v->get('value',$lang);
			}
		}
		return $salectValues;

	}

	/**
	 * restituisce i valori dell'attributo in forma chiave => valore con l'img
	 *
	 * @param string $lang
	 * @return array
	 */
	function getSelectValuesWithImages(string $lang = NULL ): array{		
		if(!$lang) $lang = _MARION_LANG_;
		$name = $this->get('name',$lang);
		$salectValues = [
			null => ['value' => $name]
		];

		$values = $this->getValues();
		
		if(okArray($values)){
			foreach($values as $v){
				
				$salectValues[$v->getId()]['value'] = $v->get('value',$lang);
				$salectValues[$v->getId()]['img'] = $v->image;
			}
		}
		return $salectValues;

	}


	
	public function delete(): void{
		$attributeValues = AttributeValue::prepareQuery()->where('product_attribute_id',$this->id)->get();
		if( okArray($attributeValues) ){
			foreach($attributeValues as $v){
				$v->delete();
			}	
		}
		parent::delete();
	}

}
?>