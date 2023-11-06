<?php
namespace Catalogo;
use Marion\Core\BaseWithImages;
use Marion\Core\Marion;
use Marion\Traits\AttachmentTrait;


class ProductOld extends BaseWithImages{
	use AttachmentTrait;
	

	const CONFIGURABLE_TYPE = 2;
	const SIMPLE_TYPE = 1;
	
	// COSTANTI DI BASE
	const TABLE = 'products'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'product_langs'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'product_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = 'parent_id'; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	const IMAGES_FIELD_TABLE = 'images';
	

	public static $_registred_classes = array();

	//ERRORI
	const ERROR_SKU_EMPTY = "sku_empty";
	const ERROR_SKU_DUPLICATE = "sku_duplicate";
	const ERROR_ATTRIBUTES_DUPLICATE = "attributes_duplicate";
	
	//atttributi che i figli ederitano dal padre di default
	protected static $_parent_fields = array('sku','section','manufacturer','offer','home','ean','virtual_product','centralized_stock');

	public $id;
	public $parent_id;
	public $product_category_id;
	public $product_template_id;
	public $manufacturer;
	public $sku;
	public $type;
	public $centralized_stock;
	public $stock;
	public $virtual_product;
	public $attributeSet;
	public $price_unit;
	public $tags = [];
	public $otherSections = [];
	public $relatedSections = [];
	public $freeShipping;
	public $weight;
	public $taxCode;

	
	
	private $_attributes = array();

	//restituisce il nome della sezione
	function getNameSection($lang=NULL): string{
		if( $this->product_category_id ){
			$section = Category::withId($this->product_category_id);
			if(is_object($section)){
				if( !$lang ) $lang = _MARION_LANG_;
				return $section->get('name',$lang);
			}
		}
		return '';

	}

	//restituisce il nome completo della sezione del prodotto
	function getFullNameSection($lang = NULL): string{
		if( $this->product_category_id ){
			$section = Category::withId($this->product_category_id);
			if(is_object($section)){
				if( !$lang ) $lang = _MARION_LANG_;
				return $section->getFullName($lang);
			}
		}
		return '';

	}
	
	//metodo che stabilisce se un prodotto è virtuale. Un prodotto è virtuale se non è un prodotto fisico e quindi non necessita della spedizione.
	function isVirtual(){
		return $this->virtual_product;
	}

	function getInventory($id_inventory=NULL){
		if( !$id_inventory ) $id_inventory = 1;
		if( $this->id ){
			$database = Marion::getDB();
			$data = $database->select('*','product_inventory',"id_product={$this->id} AND id_inventory={$id_inventory}");
			if( okArray($data) ){
				return $data[0]['quantity'];
			}
		}
		return 0;
	}

	function createInventory($id_inventory=NULL){
		if( !$id_inventory ) $id_inventory = 1;
		$toinsert = array(
			'id_inventory' => $id_inventory,
			'id_product' =>	$this->id,
			'quantity' => 0
		);
		$database = Marion::getDB();
		$database->insert('product_inventory',$toinsert);
	}

	//metodo che aggiorna la giacenzac
	function updateStock($qty=0){
		$qty = (int)$qty;
		if( $this->id ){
			$database = Marion::getDB();
			$database->update('product',"id={$this->id}",array('stock' => $qty));
			Marion::do_action('product_update_stock',array($this->id,$qty));
		}
	}

	//metodo che aggiorna la giacenzac
	function updateInventory($qty=0,$id_inventory=NULL){
		$qty = (int)$qty;
		if( !$id_inventory ) $id_inventory = 1;
		if( $this->id ){
			$database = Marion::getDB();
			$database->update('product_inventory',"id_product={$this->id} AND id_inventory={$id_inventory}",array('quantity' => $qty));
			Marion::do_action('product_update_stock',array($this->id,$qty));
		}
	}
	
	//metodo che restituisce la quantità totale dell'articolo
	function getTotalStock(){
		if( $this->isConfigurable() && !$this->centralized_stock){
			$children = $this->getChildren();
			$tot = 0;
			if( okArray($children) ){
				foreach($children as $v){
					$tot += $v->stock;
				}
			}
		}else{
			$tot = $this->stock;
		}
		return $tot;
	}


	//metodo che restituisce true quando il prodotto è disponibile
	function isAvailable(){
		$tot = $this->getTotalStock();
		if( $tot ){
			return true;
		}else{
			return false;
		}
	}

	//restituisce il nome del produttore
	public function getManufacturerName($locale = NULL){
		if( !$locale ){
			$locale = $GLOBALS['activelocale'];
		}
		
		if( $this->manufacturer ){
			$manufacturer = Manufacturer::withId($this->manufacturer);
			if( is_object($manufacturer) ){
				return $manufacturer->get('name',$locale);
			}
		}
		return '';
	}
	
	//prende i valori degli attributi se l'oggetto possiede attributi
	public function getAttributesInit(){
		
		if( $this->hasAttributes() ){
			$template = Template::withId($this->product_template_id);
			if( is_object($template) ){
				
				
				
				foreach($template->composition as $v){
					$attribute = Attribute::withId($v['product_attribute_id']);
					if($attribute){
						$this->_attributes[$attribute->id] = isset($this->_attributes[$attribute->getLabel()])?$this->_attributes[$attribute->getLabel()]:'';
					}

				}
			}
			if($this->hasId()){
				$database = Marion::getDB();
				$attributes = $database->select('*','productAttribute',"product=".$this->getId());
				if( okArray($attributes) ){
					foreach($attributes as $v){
						$this->_attributes[$v['attribute']] = $v['value'];
					}
					
				}
			}
			
		}
	}

	//prende i tag relativi al prodotto
	public function getTags(){
		if( isset($this->id) && $this->id){
			
			$database = Marion::getDB();
			$tags = $database->select('*','product_tag_associations',"product_id={$this->id}");
			
			if(okArray($tags)){
				foreach($tags as $v){
					$this->tags[] = $v['product_tag_id'];
				}
			}
		}
	}



	public function saveTags($array=array()){
		if( isset($this->id) && $this->id){
			$database = Marion::getDB();
			$database->delete('product_tag_associations',"product_id={$this->id}");
			if( okArray($array) ){
				foreach( $array as $v ){
					$toinsert = array(
						'product_tag_id' => $v,
						'product_id' => $this->id
					);
					$database->insert('product_tag_associations',$toinsert);
				}
			}
		}
	}

	
	//prende le sezioni secondarie del prodotto dal database
	public function getOtherSections(){
		if( isset($this->id) && $this->id){
			
			$database = Marion::getDB();
			$otherSections = $database->select('*','product_category_associations',"product_id={$this->id}");
			
			if(okArray($otherSections)){
				foreach($otherSections as $v){
					$this->otherSections[] = $v['product_category_id'];
				}
			}
		}
	}

	//setta le sezioni secondarie del prodotto
	public function setOtherSections($array=array()){
		$this->otherSections = $array;
	}

	//setta le sezioni secondarie del prodotto
	public function saveOtherSections($array=array()){
		if( $this->id ){
			$database = Marion::getDB();
			$database->delete('product_category_associations',"product_id={$this->id}");
			if( $this->hasChildren()){
				$id_children = $database->select('id','products',"parent_id={$this->id}");
				if( okArray($id_children) ){
					foreach($id_children as $child){
						$child_ids[] = $child['id'];
						$database->delete('product_category_associations',"product_id={$child['id']}");
					}
				}
			}
			if(okArray($this->otherSections)){
				foreach($this->otherSections as $v){
					$database->insert('product_category_associations',array('product_id'=> $this->id,'product_category_id'=>$v));
					if( okArray($child_ids) ){
						foreach($child_ids as $id){
							$database->insert('product_category_associations',array('product_id'=> $id,'product_category_id'=>$v));
						}
					}
				}
			}
		}
	}

	//prende l'insieme attributi del prodotto
	public function getAttributeSet(){
		if( $this->attributeSet ){
			$attributeSet = AttributeSet::withId($this->attributeSet);
			if( is_object($attributeSet) ){
				return $attributeSet;
			}

		}
		return false;
	}
	
	//restituisce il nome del prodotto comprensivo di variazioni se il prodotto è configurabile
	function getName($locale=NULL,$html=true,$separator="</br>"){
		if( !$locale ) $locale = $GLOBALS['activelocale'];
		$name = $this->get('name');
		
		if( $this->type == 1 && okArray($this->_attributes) ){
			
			foreach($this->_attributes as $k => $v){
				$attribute = Attribute::withLabel($k);
				if( is_object($attribute) ){
					$attributeValue = AttributeValue::withId($v);
					if( is_object($attributeValue) ){
						if( $html ){
							$name .= $separator.$attribute->get('name',$locale).": <b>".$attributeValue->get('value',$locale)."</b>";
						}else{
							$name .= " ".$attribute->get('name',$locale).": ".$attributeValue->get('value',$locale);
						}
					}
				}
			}
		}
		if( $html ){
			//$name = preg_replace("/".$separator."$/",'',$name);
		}

		return $name;
	}

	function getSKU(){
		$sku = $this->sku;
		if( $this->type == 1 && okArray($this->_attributes) ){
			foreach($this->_attributes as $k => $v){
				$sku .= "_{$v}"; 
			}
		}
		return $sku;
	}


	//restiuisce l'url del prodotto 
	function getUrl($locale=NULL){
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		if( $this->hasParent()){
			$id = $this->getParentId();
		}else{
			$id = $this->getId();
		}
		
		$prettyUrl = $this->get('prettyUrl',$locale);
		if($prettyUrl){
			$name = $prettyUrl; 	
		}else{
			$name = $this->get('name',$locale);
		}
		$name = Marion::slugify($name);
		
		
		
		return _MARION_BASE_URL_."catalog/product/".$id."/".$name;
	}
	


	

	
	//verifica se un prodotto è configurabile
	function isConfigurable(){
		if($this->type == 2){
			return true;
		}else{
			return false;
		}
	}

	//verifica se un prodotto ha attributi
	function hasAttributes(){
		if(isset($this->attributeSet) && $this->attributeSet ){
			return true;
		}else{
			return false;
		}
	}



	//verifica se il codice articolo è stato inseirto ed se è un duplicato
	public function checkSKU(){
		
		if(!$this->sku){
			return STATIC::ERROR_SKU_EMPTY;
			
		}else{
			$database = Marion::getDB();
			
			if( !$this->hasParent()){
				if($this->hasId()){
					$check = $database->select('*',STATIC::TABLE,"sku='{$this->sku}' AND (parent_id = 0 OR parent_id is NULL) AND id <> {$this->getId()} and deleted=0");
				}else{
					$check = $database->select('*',STATIC::TABLE,"sku='{$this->sku}' AND (parent_id = 0 OR parent_id is NULL) and deleted=0");
				}
				if(okArray($check)){
					return STATIC::ERROR_SKU_DUPLICATE;
				}
			}
			
			
		}
		return true;
	}

	
	//setta gli attributi di un prodotto se li possiede
	public function setAttributes($attributes){
		
		if( $this->hasAttributes() ){
			
			if(okArray($attributes)){
				foreach($attributes as $k => $v){
					if(array_key_exists($k,$this->_attributes) ){
						$this->_attributes[$k] = $v;
					}
				}
			}
		}
		
		return $this;
	}

	//salva gli attributi di un prodotto se presenti
	public function saveAttributes(){

		if( $this->parent_id && $this->hasAttributes() ){
			
			if(okArray($this->_attributes)){
				$database = Marion::getDB();
				if( is_object($this->_oldObject) ){
					$old_attributes = $this->_oldObject->getAttributes();
					$intersect = array_intersect(array_values($old_attributes),array_values($this->_attributes));
					if( count($intersect) != count($this->_attributes) ){
						$id_product = $this->getId();
						$database->delete('productAttribute',"product={$id_product}");
						foreach($this->_attributes as $k => $v){
							$toinsert = array();
							$toinsert['product'] = $id_product;
							$toinsert['attribute'] = $k;
							$toinsert['value'] = $v;
							$database->insert('productAttribute',$toinsert);

						}
					}
				}else{
					foreach($this->_attributes as $k => $v){
						$toinsert = array();
						$toinsert['product'] = $this->getId();
						$toinsert['attribute'] = $k;
						$toinsert['value'] = $v;
						$database->insert('productAttribute',$toinsert);

					}
				}				
			}
		}
		
		return $this;
	}

	function getUrlImageLabelPrice($type='original'){
		if( isset($this->price_unit) && is_object($this->price_unit) ){
			if( isset($this->price_unit->image) && $this->price_unit->image ){
				$type = parent::getTypeImageUrl($type);
				return _MARION_BASE_URL_."img/".$this->price_unit->image."/".$type."-nw/labelprie.png";
			}
		}

		return false;

	}

	function getDiscountPercentage(){
		if( is_object($this->price_unit) ){
			
			$sconto = $this->price_unit->defaultValue-$this->price_unit->value;
			$perc = (int)($sconto/$this->price_unit->defaultValue*100);
			if( $perc > 0 ){
				return $perc;
			}
		}

		return false;

	}

	//stabilisce se il prodotto ha un prezzo di listino che non è quello standard
	function hasSpecialPrice(){
		if( isset($this->price_unit) && is_object($this->price_unit) ){
			if( isset($this->price_unit->listPriceName) && $this->price_unit->listPriceName && $this->price_unit->listPriceName != 'standard' && $this->price_unit->listPriceName != 'default' ){
				return true; 
			}
		}
		return false;
	}



	

	/*
		function: getPrice()
		Descrizione: Restituisce l'oggetto prezzo per la specificata quantita e gruppo di acquisto

		INPUT::
			$qnt :: quantità di prodotti
			$group :: gruppo di acquisto

	*/

	





	/*
		function: getWeigth()
		Descrizione: Restituisce il peso del prodotto

	*/
	function getWeigth(){
		if( $this->freeShipping ){
			return 0;
		}
		return $this->weight;
	}

	//prende gli attributi del prodotto
	function getAttributes(){
		return $this->_attributes;
	}

	//restituisce gli attributi sotto forma di select a partire dal locale specificato
	public function getSelectAttributes($locale=NULL){
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		if( $this->isConfigurable() ){
			if( $this->hasAttributes() ){
				$attributeSet = AttributeSet::withId($this->attributeSet);
			
				//prendo i valori possibili per gli attibuti
				if(is_object($attributeSet)){
					$select = $attributeSet->getAttributeWithValues($locale);
				}
				if( $this->hasChildren() ){
					$database = Marion::getDB();

					$figli = $database->select('id',"product","parent={$this->id}");
					
					if( okArray($figli) ){
						
						foreach($figli as $v){
							$values = $database->select('*','productAttribute',"product = {$v['id']}");
							
							foreach($values as $key => $value){
								$options[$value['attribute']][] = $value['value'];
							}
						}
						
						foreach($options as $k => $v){
							$options[$k] = array_unique($v);
						}
						foreach($select as $attr => $values){
							foreach($values as $k => $v){
								if( !in_array($k,$options[$attr]) && $k != 0){
									unset($select[$attr][$k]);
								}
							}

						}
						return $select;
					}
				}
					
			}
		}
		return false;

	}


		//restituisce gli attributi sotto forma di select a partire dal locale specificato
	public function getAttributesView($locale=NULL){
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		if( $this->isConfigurable() ){
			if($this->hasAttributes()){
				$attributeSet = AttributeSet::withId($this->attributeSet);
				
				
				$attributes_composition = $attributeSet->getAttributes();
				if( okArray($attributes_composition) ){
					foreach($attributes_composition as $k => $v){
						$attribute_object = Attribute::withId($v['attribute']);
						if( is_object($attribute_object) ){
							$name_attributes[$attribute_object->label] = $attribute_object->get('name',$locale);
						}
					}
				}
					
				
				
				//prendo i valori possibili per gli attibuti
				if(is_object($attributeSet)){
					$select = $attributeSet->getAttributeWithValuesAndImages($locale);
					if( okArray($name_attributes) ){
						if( okArray($select) ){
							foreach($select as $k => $v){
								$select[$k]['name'] = $name_attributes[$k];
							}
						}
					}
					
				}
				
				if( $this->hasChildren() ){
					$database = Marion::getDB();

					$figli = $database->select('id',"product","parent={$this->id} AND (deleted IS NULL OR deleted = 0)");

					if( okArray($figli) ){
						
						foreach($figli as $v){
							$values = $database->select('*','productAttribute',"product = {$v['id']}");
										
							foreach($values as $key => $value){
								$options[$value['attribute']][] = $value['value'];
							}
						}
						
						foreach($options as $k => $v){
							$options[$k] = array_unique($v);
						}
											
						foreach($select as $attr => $values){
							foreach($values['values'] as $k => $v){
								//debugga($k);
								if( !in_array($k,$options[$attr]) && $k != 0){
									unset($select[$attr]['values'][$k]);

								}
							}
						
						}
										
						return $select;
					}
				}
					
			}
		}
		return false;

	}

	//metodo che prende i figli di un oggetto
	public function getChildren($where=NULL): ?array{
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if( !$where ) $where = "1=1 AND (deleted IS NULL OR deleted = 0)";
		if($this->$field_id){
			$database = Marion::getDB();
			$data = $database->select('*',STATIC::TABLE,STATIC::PARENT_FIELD_TABLE."={$this->$field_id} AND {$where}");
			
			$toreturn = array();
			if(okArray($data)){
				foreach($data as $v){
					$toreturn[] = self::withData($v);
				}
				return $toreturn;
			}else{
				return false;
			}
			
		}else{
			return false;
		}
	}


	

		
	public function init(): void
	{	
		parent::init();
		$this->getAttributesInit();
		//prendo le sezioni correlate
		//$this->getRelatedSections();
		$this->getOtherSections();
		$this->getTags();
		
		
	}


	public static function getParentFields(){
		$parent_fields = self::$_parent_fields;
		if( !Marion::getConfig('catalog','use_parent_sku') ){
			if(($key = array_search('sku', $parent_fields)) !== false) {
				unset($parent_fields[$key]);
			}
		}
		if( !Marion::getConfig('catalog','use_parent_ean') ){
			if(($key = array_search('ean', $parent_fields)) !== false) {
				unset($parent_fields[$key]);
			}
		}
		return $parent_fields;
	}
	

	

	public function updateTaxChildren(){
		if( !$this->parent ){
			$database = Marion::getDB();
			$database->update('product',"parent={$this->id} AND parentPrice=1",array('taxCode'=>$this->taxCode));
		}
	}

	public function afterSave(): void{
		
		parent::afterSave();
		
		//aggiorno i prezzi del prodotto
		if( !$this->parent_id ){
			//Catalog::loadPrices($this->id);
		}
		//$this->saveLocaleData();
		$this->saveAttributes();
		$this->saveOtherSections();
		
		
		if($this->hasChildren()){
			//aggiorno la tassa dei figli che ereditano i prezzi dal padre
			$this->updateTaxChildren();
			$children = $this->getChildren();
			$parent_fields = self::getParentFields();
			foreach($children as $v){
				if( okArray($parent_fields) ){
					foreach($parent_fields as $key){
						$v->$key = $this->$key;
					}
				}
				$v->save();
			}
			
		}

		if( $this->_type_action == 'INSERT'){
			$this->createInventory();
		}

		/*$database = Marion::getDB();
		if( $this->parent_id ){
			$database->insert('product_search_changed',array('id_product' => $this->parent_id));
		}else{
			$database->insert('product_search_changed',array('id_product' => $this->id));
		}*/


		Marion::do_action('product_after_save',array(&$this));

	}

	function afterLoad(): void{
		
		parent::afterLoad();


		Marion::do_action('product_after_load',array(&$this));
		

		
	}
	

	public function beforeSave(): void{
		parent::beforeSave();
		if( $this->parent_id ){
			$parent_fileds = self::getParentFields();
			$parent = $this->getParent();
			if( is_object($parent) ){
				if( okArray($parent_fileds) ){
					foreach($parent_fileds as $key){
						$this->$key = $parent->$key;
					}
				}
			}
		}
		
	}
	
	public function checkSave(){
		
		$check = $this->checkDuplicateAttributes();
		if( $check != 1) return $check;
		return $this->checkSku();
	}

	
	//controlla se per un prodotto configurabile almeno 2 figli hanno gli stessi attributi
	function checkDuplicateAttributes(){
		
		if( $this->isConfigurable() && !$this->attributeSet){
			return true;
		}
	

		if($this->hasAttributes()){
			if($this->hasParent()){
				$query = Product::prepareQuery()->where('parent',$this->getParentId())->whereExpression("(deleted is NULL OR deleted = 0)");
				if( $this->hasId() ){
					$query->where('id',$this->getId(),'<>');
				}

				$children = $query->getCollection();
				//debugga($query->lastquery);exit;
				$duplicates = $children->findAll(function($child){
				  $cont = count(array_intersect($child->getAttributes(), $this->getAttributes()));
				 
				  if($cont == count( $this->getAttributes() ) ){
						return true;
				  }else{
						return false;
				  }
				});
				
				if($duplicates->count() > 0) return STATIC::ERROR_ATTRIBUTES_DUPLICATE;

			}

		}
		return true;

	}

	public function set(?array $data): self
	{
		parent::set($data);
		if( !$this->hasId() ){
			$this->getAttributesInit();
		}
		return $this;
	}


	public function delete(): void{
		if($this->id){
			$database = Marion::getDB();
			//controllo se prodotto è presente negli ordini. Nel caso sia presente lo metto in stato deleted altrimenti lo elimino
		
			
			//azione da effettuare quando un prodotto viene eliminato
			Marion::do_action('action_delete_product',array($this));

			
			parent::deleteChildren();
			$database->delete('productAttribute',"product={$this->id}");
			$database->delete('product_search',"id_product={$this->id}");
			$database->delete('product_search_changed',"id_product={$this->id}");
			parent::delete();
			
			
		}

	}


	//se il prodotto è configurabile ed ha figli restituisce un array in cui le chiavi sono gli ID dei figli e i valori sono
	//gli attributi e la quantita' in magazzino

	public function getStockChildren($locale=NULL){
		
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		
		if($this->hasChildren() && $this->isConfigurable()){
			
			$attributeSet = AttributeSet::withId($this->attributeSet);
			
			if( is_object($attributeSet) ){
				$children = $this->getSortedChildrenByAttributeSet();
				
				if(okArray($children)){
					
					foreach($children as $v){
						
						$attributeChild = $v->getAttributes();
						foreach($attributeChild as $k => $value){
							$attributeValue = AttributeValue::withId($value);
							$attribute = Attribute::withLabel($k);
						
							if($attributeValue && $attribute){
								$stock[$v->getId()]['sku'] = $v->sku;
								$stock[$v->getId()]['ean'] = $v->ean;
								$stock[$v->getId()]['upc'] = $v->upc;
								$stock[$v->getId()]['weight'] = $v->weight;
								$stock[$v->getId()]['image'] = $v->images[0];
								$stock[$v->getId()]['minOrder'] = $v->minOrder;
								$stock[$v->getId()]['maxOrder'] = $v->maxOrder;
								$stock[$v->getId()]['id'] = $v->getId();
								$stock[$v->getId()]['visibility'] = $v->visibility;
								$stock[$v->getId()]['attributes'][$attribute->get('name',$locale)]=$attributeValue->get('value',$locale);
								$stock[$v->getId()]['stock']=$v->get('stock');
								
							}
						}
					}
					return $stock;

				}
			}else{
				$children = $this->getChildren();
				if(okArray($children)){
					foreach($children as $v){
						$stock[$v->getId()]['weight'] = $v->weight;
						$stock[$v->getId()]['image'] = $v->images[0];
						$stock[$v->getId()]['minOrder'] = $v->minOrder;
						$stock[$v->getId()]['maxOrder'] = $v->maxOrder;
						$stock[$v->getId()]['id'] = $v->getId();
						$stock[$v->getId()]['name'] = $v->get('name');
						$stock[$v->getId()]['stock']=$v->get('stock');
						$stock[$v->getId()]['visibility'] = $v->visibility;
					}
					return $stock;
				}
			}


		}
		return false;

	}

	public function getInventoryChildren($locale=NULL){
		
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		
		if($this->hasChildren() && $this->isConfigurable()){
			
			$attributeSet = AttributeSet::withId($this->attributeSet);
			
			if( is_object($attributeSet) ){
				$children = $this->getSortedChildrenByAttributeSet();
				
				if(okArray($children)){
					
					foreach($children as $v){
						$qnt = $v->getInventory();
						$attributeChild = $v->getAttributes();
						foreach($attributeChild as $k => $value){
							$attributeValue = AttributeValue::withId($value);
							$attribute = Attribute::withLabel($k);
						
							if($attributeValue && $attribute){
								$stock[$v->getId()]['sku'] = $v->sku;
								$stock[$v->getId()]['ean'] = $v->ean;
								$stock[$v->getId()]['upc'] = $v->upc;
								$stock[$v->getId()]['weight'] = $v->weight;
								$stock[$v->getId()]['image'] = $v->images[0];
								$stock[$v->getId()]['minOrder'] = $v->minOrder;
								$stock[$v->getId()]['maxOrder'] = $v->maxOrder;
								$stock[$v->getId()]['id'] = $v->getId();
								$stock[$v->getId()]['visibility'] = $v->visibility;
								$stock[$v->getId()]['attributes'][$attribute->get('name',$locale)]=$attributeValue->get('value',$locale);
								$stock[$v->getId()]['stock']=$qnt;
								
							}
						}
					}
					return $stock;

				}
			}else{
				$children = $this->getChildren();
				if(okArray($children)){
					foreach($children as $v){
						$qnt = $v->getInventory();
						$stock[$v->getId()]['weight'] = $v->weight;
						$stock[$v->getId()]['image'] = $v->images[0];
						$stock[$v->getId()]['minOrder'] = $v->minOrder;
						$stock[$v->getId()]['maxOrder'] = $v->maxOrder;
						$stock[$v->getId()]['id'] = $v->getId();
						$stock[$v->getId()]['name'] = $v->get('name');
						$stock[$v->getId()]['stock']=$qnt;
						$stock[$v->getId()]['visibility'] = $v->visibility;
					}
					return $stock;
				}
			}


		}
		return false;

	}


	//ordina i figli a partire dall'ordine dell'insieme di attributi
	function getSortedChildrenByAttributeSet(){
			if($this->hasChildren() && $this->isConfigurable()){
				$children = $this->getChildren();
					if(okArray($children)){
						
						//prendo l'insieme di attributi fissato per questo prodotto
						$attributeSet =  AttributeSet::withId($this->attributeSet);
						
						//prendo gli attributi con i valori
						$attributes = $attributeSet->getAttributes();
						if(okArray($attributes)){
							foreach($attributes as $v){
								$attribute = Attribute::withId($v['attribute']);
								if($attribute){
									$values = $attribute->getValues();

									if(okArray($values)){
										foreach($values as $v){
											$order[$attribute->getLabel()][$v->getId()] = $v->get('orderView');
										}
									}
								}
							}
						}
						foreach($children as $v){
							$children_tmp1[$v->getId()] = $v;
							$attributes = $v->getAttributes();
							if(okArray($attributes)){
								foreach($attributes as $k => $attr){
									$children_tmp2[$v->getId()][$k] = $order[$k][$attr];
								}
								$children_tmp2[$v->getId()]['_id'] = $v->getId();
							}

						}
						array_multisort($children_tmp2, SORT_ASC);
						
						foreach($children_tmp2 as $v){
							$toreturn[] = $children_tmp1[$v['_id']];
						}
						return $toreturn;

					}
			}
			return false;

	}

	//prende il figlio di un prodotto configurabile a partire dai suoi attributi passati sottoforma di key => value
	function getChildWithAttributes($data=array()){
			$children = $this->getChildrendWithAttributes($data);
			if(okArray($children)){
				return $children[0];
			}
			return false;

	}

	//prende i figli  di un prodotto configurabile a partire dagli attributi passati sottoforma di key => value
	function getChildrendWithAttributes($data=array()){
			if($this->isConfigurable() && $this->hasChildren()){
				$database = Marion::getDB();
				$whereCondiction ='';
				$attributes = $this->getAttributes();
				foreach($data as $k => $v){
					if( array_key_exists($k,$attributes)){
						$whereCondiction .= "id in (select product from productAttribute where attribute='{$k}' AND value={$v}) AND ";
					}
				}
				$whereCondiction = preg_replace('/AND $/','',$whereCondiction);
				
				$query = Product::prepareQuery()
						->where('parent',$this->id)
						->where('deleted',0)
						->whereExpression($whereCondiction);
				$product = $query->get();
			
				return $product;
				
			}
			return false;

	}
	

	/************************* GESTIONE PRODOTTI CORRELATI *****************************************/
	

	//imposta le sezioni correlate
	public function setRelatedSections($array){
		
		$this->relatedSections = $array;
	}

	//salva le sezioni correlati
	public function saveRelatedSections(){
		
		if( $this->hasId() ){
			$database = Marion::getDB();
			$database->delete('productRelatedSection',"product={$this->id}");
			$database->delete('productRelated',"product={$this->id}");
			
			if( okArray($this->relatedSections) ){
				foreach($this->relatedSections as $k => $v){
					if( okArray($v['products']) ){
						$products = $v['products'];
						unset($v['products']);
					}
					
					$v['product'] = $this->id;
					$res = $database->insert('productRelatedSection',$v);
					
					if( !$res ){
						unset($this->relatedSections[$k]);
					}else{
						
						if( okArray($products) ){
							foreach($products as $v1){
								$toinsert = array(
									'product' => $this->id,
									'related' => $v1,
									'section' => $v['section']
								);
								
								$res2 = $database->insert('productRelated',$toinsert);
								
							}
						}
					}	




					

				}
				
				
				//debugga($database->lastquery);exit;
			}
			
		}
		//exit;
	}

	//prende le sezioni correlate dal database
	public function getRelatedSections(){
		if( $this->hasId() ){
			$database = Marion::getDB();
			$sections = $database->select('*','productRelatedSection',"product={$this->id}");
			
			if( okArray($sections) ){
				foreach($sections as $v){
					if( $v['type'] == 'specific' ){
						$products = $database->select('related','productRelated',"product={$this->id} and section={$v['section']}");
						if( okArray($products) ){
							foreach($products as $v1){
								$v['products'][] = $v1['related'];
							}
						}
						//$v['products'] = unserialize($v['products']);
					}
					$this->relatedSections[] = $v;
				}
			}
		}
	}

	public function hasRelatedProducts(){
		if( $this->hasId() ){
			return okArray( $this->relatedSections);
		}

		return false;

	}


	public function getRelatedProducts(){
		if( $this->hasId() ){
			$database = Marion::getDB();
			$sections = $this->relatedSections;
			$products = array();
			if( okArray($sections) ){
				$ids = array();
				foreach($sections as $v){
					if( $v['type'] == 'specific' ){
						$_products = $database->select('related','productRelated',"product={$this->id} and section={$v['section']}"); 
						if( okArray($_products) ){
							$ids_add = array();
							foreach($_products as $v1){
								$ids_add[] = $v1['related'];
							}
							if( okArray($ids) ){
								$ids = array_merge($ids,$ids_add);
							}else{
								$ids = $ids_add;
							}
						}
					}else{
						if( !$v['num_products'] ) $v['num_products'] = 1;
						$query = self::prepareQuery()->where('visibility',1)->where('parent',0)->where('section',$v['section'])->where('deleted',0)->orderBy('rand()')->limit($v['num_products']);
						$products_random = $query->get();
						//debugga($query->lastquery);exit;
						if( okArray($products_random) ){
							if( okArray($products) ){
								$products = array_merge($products,$products_random);
							}else{
								$products = $products_random;
							}
						}
					}
				}
				if( okArray($ids) ){
					$where = '(id in (';
					foreach($ids as $id){
						$where .= "{$id}, ";
					}
					$where = preg_replace('/\, $/','))',$where);
					$products_from_id = self::prepareQuery()->where('visibility',1)->whereExpression($where)->get();
					if( okArray($products_from_id) ){
						if( okArray($products) ){
							$products = array_merge($products,$products_from_id);
						}else{
							$products = $products_from_id;
						}
					}
				}
			}
			return $products;
		}
	}

	/************************* FINE GESTIONE PRODOTTI CORRELATI *****************************************/

/***************************************************** OVERRIDE METODI DELLA CLASSE BaseWithImages**************************************************************/
 
 //restituisce l'immagine all'indice specificato del formato specificato
	function getUrlImage($index=0,$type='original',$watermark=true,$name_image=NULL){
		//if( !$name_image ) $name_image = $this->get('name');
		if( $this->hasImages()){
			$url = parent::getUrlImage($index,$type,$watermark,$name_image);
			
			return $url;
		}else{
			
			$parent = $this->getParent();
			
			if(is_object($parent) && $parent->hasImages() ){
				$url = $parent->getUrlImage($index,$type,$watermark,$name_image);
				return $url;
			}
		}
		return false;
		
	}





	//metodo che restituisce il percorso di un prodotto
	function breadCrumbs(){
		
		
		$options_default = array(
			"before_html" => "<span>",
			"after_html" => "</span>",
			"divider_html" => " > "
		);

		
		if( $this->section ){
			
			$section = Section::withId($this->section);
			if (is_object($section)) {
				$list[] = array(
					'name' => $section->get('name'),
					'id' => $section->id,
					'parent' => $section->parent,
					'url' => $section->getUrl()
				);
				while( $section->parent ){
					$section = $section->getParent();
					$list[] = array(
						'name' => $section->get('name'),
						'id' => $section->id,
						'parent' => $section->parent,
						'url' => $section->getUrl()
					);

				}
				
				krsort($list);
				$list = array_values($list);
				$list[0]['first'] = 1;
				$list[count($list)-1]['last'] = 1;
				$breadCrumbs = '';
				foreach($list as $v){
					$breadCrumbs .= $options_default['before_html'].$v['name'].$options_default['after_html'].$options_default['divider_html'];
				}
				$divider_html = $options_default['divider_html'];
				$breadCrumbs = preg_replace("/{$divider_html}$/",'',$breadCrumbs);
				
				
				return $breadCrumbs;
			
			}
		}
		return false;
	}


	/************************* WISHLIST ****************************************/
	function addToWishlist(){
		if( authUser() && $this->id){
			if( !$this->isInWhishlist() ){
				$user = Marion::getUser();
				
				$database = Marion::getDB();
				$toinsert = array(
					'product' => $this->id,
					'user' => $user->id
				);
				$database->insert('wishlist',$toinsert);
				return true;
			}
		}
		return false;
	}

	function removeFromWishlist(){
		if( authUser() && $this->id){
			
			if( $this->isInWhishlist() ){
				$user = Marion::getUser();
				$database = Marion::getDB();
				
				$database->delete('wishlist',"product={$this->id} AND user ={$user->id}");
				return true;
			}
		}
		return false;
	}

	function isInWhishlist(){
		if( authUser() && $this->id){
			$user = Marion::getUser();
			$database = Marion::getDB();
			$check = $database->select('*',"wishlist","product={$this->id} AND user ={$user->id}");
			return okArray($check);
		}
		return false;
	}

	/************************* WISHLIST ****************************************/


	public static function registerAdminTab($string=''){
		self::$_registred_classes[] = $string;
	}
}
?>