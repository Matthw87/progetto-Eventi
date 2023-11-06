<?php
namespace Catalogo;
use Marion\Core\Base;
use Illuminate\Database\Capsule\Manager as DB;
class Template extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'product_templates'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = '';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = ''; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 


	public $composition = [];

	/**
	 * Imposta la composizione del Template
	 *
	 * @param array $composition
	 * @return self
	 */
	public function setComposition($composition = []): self{
		$this->composition = $composition;
		return $this;
	}

	function afterSave(): void
	{
		
		parent::afterSave();
		DB::table('product_template_compositions')->where('product_template_id',$this->id)->delete();
		
		foreach($this->composition as $v){
			$data = $v;
			$data['product_template_id'] = $this->id;
			DB::table('product_template_compositions')->insert(
				$data
			);
		}
	}


	function afterLoad(): void
	{
		parent::afterLoad();
		$list = DB::table('product_template_compositions')
			->where('product_template_id',$this->id)
			->orderBy('order_view')
			->get()->toArray();
		foreach($list as $v){
			$this->composition[] = [
				'product_attribute_id' => $v->product_attribute_id,
				'type' => $v->type,
				'order_view' => $v->order_view,
				'show_image' => $v->show_image,
			];
		}
	}

	/**
	 * Reurn attribute list with values
	 *
	 * @param string $lang
	 * @return array
	 */
	function getAttributeWithValues(string $lang=NULL): array{
		if(!$lang){ 
			$lang = _MARION_LANG_;
		}
		
		if(okArray($this->composition)){
			
			foreach($this->composition as $k => $v){
				$attr = Attribute::withId($v['product_attribute_id']);
				
				if($attr){
					$toreturn[] = [
						'attribute_id' => $v['product_attribute_id'],
						'attribute_name' => $attr->get('name',$lang),
						'values' => $attr->getSelectValues($lang)
						
					];
				}
				
			}
			return $toreturn;
		}
		return [];
	}

	/**
	 * Reurn attribute list with values and images
	 *
	 * @param string $lang
	 * @return array
	 */
	function getAttributeWithValuesAndImages(string $lang = NULL): array{
		if(!$lang) $lang = _MARION_LANG_;
		
		
		if(okArray($this->composition)){
			foreach($this->composition as $k => $v){
				
				$attr = Attribute::withId($v['product_attribute_id']);
				
				if($attr){
					$toreturn[$attr->id]['values'] = $attr->getSelectValuesWithImages($lang);
					$toreturn[$attr->id]['type'] = $v['type'];
					$toreturn[$attr->id]['img'] = $v['show_image'];
				}
				
			}
			return $toreturn;
		}
		return [];
	}



	
}

?>