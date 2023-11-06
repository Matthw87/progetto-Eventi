<?php
namespace Catalogo;
use Marion\Core\{Base,Marion};
class AttributeSet{
	
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	const LOCALE_DEFAULT = 'it';
	
	//ERRORI
	const ERROR_LABEL_DUPLICATE = "label_duplicate";
	const ERROR_LABEL_EMPTY = "label_empty";

	
	public static $_tableColumns;
	public static $_tableColumnsComposition;

	private $_attributesArray = array(); //array contenente gli ID degli attributi
	
	public $id;
	public $label;
	

	// metodo che restituisce le colonne della tabella
	private static function getTableColumns(){
		$database = Marion::getDB();
		$columns = $database->fields_table('attributeSet');
		self::$_tableColumns = $columns;
		
		$columns = $database->fields_table('attributeSetComposition');
		self::$_tableColumnsComposition = $columns;
	}
	
	//restituisce l'id dell'oggetto
	public function getId(){
		if($this->id){
			return $this->id;
		}else{
			return false;
		}

	}

	//restituisce la label dell'oggetto
	public function getLabel(){
		return $this->label;
		

	}

	//assegna la label all'oggetto
	public function setLabel($label){
		$this->label = $label;
		return $this;
	}





	//aggiunge un attributo all'insieme a partire dal suo ID
	function addAttributeWithId($id){
		if($id){
			$this->_attributesArray[$id]['attribute'] = $id;
		}
		return $this;
	}

	//aggiunge un attributo all'insieme a partire dalla sua LABEL
	function addAttributeWithLabel($label){
		if($label){
			$id = $this->getAttributeIdFromLabel($label);
			if($id){
				$this->addAttributeWithId($id);
			}
		}
		return $this;
	}

	//rimuove un attributo dall'insieme a partire dal suo ID
	function removeAttributeWithId($id){
		unset($this->_attributesArray[$id]);
		if($id){
			unset($this->_attributesArray[$id]);
		}
		return $this;
	}
	
	//rimuove un attributo dall'insieme a partire dalla sua LABEL
	function removeAttributeWithLabel($label){
		if($label){
			$id = $this->getAttributeIdFromLabel($label);
			if($id){
				unset($this->_attributesArray[$id]);
			}
		}
		
		return $this;
	}
	
	//svuota la lista di attributi precedntemente memorizzati dell'oggetto
	public function clear(){
		$this->_attributesArray = array();
	}



	//imposta l'ordine di visualizzazione di un attributo specificando l'id e l'ordine
	function setOrderForAttributeWithId($id,$order=1){
		if($id && array_key_exists($id,$this->_attributesArray)){
			$this->_attributesArray[$id]['orderView'] = $order;
		}
		return $this;
	}

	//imposta l'ordine di visualizzazione di un attributo specificando la label e l'ordine
	function setOrderForAttributeWithLabel($label,$order=1){
		if($label){
			$id = $this->getAttributeIdFromLabel($label);
			if($id && array_key_exists($id,$this->_attributesArray)){
				$this->setOrderForAttributeWithId($id,$order);
			}
		}
		return $this;
	}

	//imposta il tipo di visualizzazione di un attributo specificando l'id
	function setTypeForAttributeWithId($id,$type='select'){
		if($id && array_key_exists($id,$this->_attributesArray)){
			$this->_attributesArray[$id]['type'] = $type;
		}
		return $this;
	}

	//imposta il tipo di visualizzazione di un attributo specificando la label
	function setTypeForAttributeWithLabel($label,$type='select'){
		if($label){
			$id = $this->getAttributeIdFromLabel($label);
			if($id && array_key_exists($id,$this->_attributesArray)){
				$this->setTypeForAttributeWithId($id,$type);
			}
		}
		return $this;
	}

	//imposta se visualizzare l'immagine per un attributo specificando l'id
	function setImgForAttributeWithId($id,$img=0){
		if($id && array_key_exists($id,$this->_attributesArray)){
			$this->_attributesArray[$id]['img'] = $img;
		}
		return $this;
	}

	//imposta se visualizzare l'immagine per un attributo specificando la label
	function setImgForAttributeWithLabel($label,$img=0){
		if($label){
			$id = $this->getAttributeIdFromLabel($label);
			if($id && array_key_exists($id,$this->_attributesArray)){
				$this->setImgForAttributeWithId($id,$img);
			}
		}
		return $this;
	}

	//instanza un nuovo oggetto
	public static function create(){
		self::checkTable();
		$object = _obj(get_called_class());
		return $object;
	}
	
	
	//instanzia un oggetto esistente a partire dal suo ID
	public static function withId($id){
		
		if( $id ){
			$database = Marion::getDB();
			$data = $database->select('*','attributeSet',"id={$id}");

			if( okArray($data) ){
				$data = $data[0];
				$object = self::create();
				$object->id = $data['id'];
				$object->init();
				$object->setLabel($data['label']);
				
				return $object;
			}else{
				return false;
			}
		}else{
			return false;
		}
		
	}

	//instanzia un oggetto esistente a partire dalla sua LABEL
	public static function withLabel($label){
		if($label){
			$database = Marion::getDB();
			$data = $database->select('id','attributeSet',"label='{$label}' AND (deleted IS NULL OR deleted=0)");
			if(okArray($data)){
				$id = $data[0]['id'];
				return self::withId($id);
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	//ricrea la composizione dell'insieme memorizzato nel database
	public function getStoredAttributes(){
		if($this->getId()){
			$database = Marion::getDB();
			$attributes = $database->select('*','attributeSetComposition',"attributeSet=".$this->getId(),"orderView ASC");
			
			if(okArray($attributes)){
				foreach($attributes as $v){
					$this->addAttributeWithId($v['attribute']);
					$this->setOrderForAttributeWithId($v['attribute'],$v['orderView']);
					$this->setTypeForAttributeWithId($v['attribute'],$v['type']);
					$this->setImgForAttributeWithId($v['attribute'],$v['img']);
				}
			}
		}

		
	}
	//funzione che inizializza l'oggetto
	public function init(){
		$this->getStoredAttributes();
	}

	//salva la configurazione dell'oggetto nel database
	function save(){
		$check = $this->checkSave();
		if($check == 1){
			if($this->getLabel()){
				$database = Marion::getDB();
				$toinsert = array(
					'label' => $this->getLabel(),
					//'withAttributes' => $this->withAttributes,
				);
				if($this->getId()){
					//update
					$database->update('attributeSet',"id=".$this->getId(),$toinsert);
				}else{
					$this->id= $database->insert('attributeSet',$toinsert);
				}
				//elimino la composizione precedente ed inserisco la nuova
				$database->delete('attributeSetComposition',"attributeSet=".$this->getId());
				if(okArray($this->_attributesArray)){
					
					foreach($this->_attributesArray as $k => $v){
						if( $this->checkAttributeExistsWithId($k)){
							$toinsert_attribute = $v;
							if(!$toinsert_attribute['orderView']){
								$toinsert_attribute['orderView'] = 1;
							}
							$toinsert_attribute['attributeSet'] = $this->getId();
							//debugga($toinsert_attribute);exit;
							$database->insert('attributeSetComposition',$toinsert_attribute);
						
						}else{
							unset($this->_attributesArray[$k]);
						}
					}
				}
			}else{
				//scrivi errore nei log
				return false;
			}
			return $this;
		}else{
			return $check;
		}
	}

	//restituisce la composizione dell'insieme
	function getComposition(){
		return $this->_attributesArray;
	}

	//restituisce la composizione dell'insieme
	function getAttributes(){
		return $this->_attributesArray;
	}



	//restituisce la lista degli attributi con i valori sottoforma di select
	function getAttributeWithValues($locale=NULL){
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		$composition = $this->getComposition();
		
		if(okArray($composition)){
			
			foreach($composition as $k => $v){
				$attr = Attribute::withId($k);
				
				if($attr){
					$toreturn[$attr->getLabel()] = $attr->getSelectValues($locale);
				}
				
			}
			return $toreturn;
		}
		return false;
	}

	//restituisce la lista degli attributi con i valori sottoforma di select con i valori
	function getAttributeWithValuesAndImages($locale=NULL){
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		$composition = $this->getComposition();
		
		if(okArray($composition)){
			foreach($composition as $k => $v){
				$attr = Attribute::withId($k);
				
				if($attr){
					$toreturn[$attr->getLabel()]['values'] = $attr->getSelectValuesWithImages($locale);
					$toreturn[$attr->getLabel()]['type'] = $v['type'];
					$toreturn[$attr->getLabel()]['img'] = $v['img'];
				}
				
			}
			return $toreturn;
		}
		return false;
	}
	
	//elimina l'oggetto
	function delete(){
		
		if($this->getId()){
			$database = Marion::getDB();
			
			$check = $database->select('*','product',"attributeSet={$this->id}");
			if( !okArray($check) ){
				$database->delete('attributeSet',"id=".$this->getId());
				$database->delete('attributeSetComposition',"attributeSet=".$this->getId());
			}else{
				$database->update('attributeSet',"id={$this->id}",array("deleted" => 1));
			}
		}
	}


	
	//controlla se l'attributo esiste a partire dal suo ID
	private function checkAttributeExistsWithId($id){
		$check = Attribute::prepareQuery()->where('id',$id)->get();
		return okArray($check);

	}
	
	//prende l'id di un attributo a partire dalla sua etichetta
	private function getAttributeIdFromLabel($label){
		$attribute = Attribute::withLabel($label);
		if($attribute) return $attribute->getId();
		else return false;
	}

	//prende la lista degli insiemi
	public static function getlist(){
		
		$database = Marion::getDB();
		$select = $database->select('id','attributeSet',"deleted <> 1");
		
		$toreturn = array();
		if(okArray($select)){
			foreach($select as $v){
				$toreturn[]= self::withId($v['id']);
			}
		}
		return $toreturn;

	}
	
	//controllo dei dati prima del salvataggio
	public function checkSave(){
		
		//controllo se la label è settata e che non ci siano duplicati
		if( $this->label ){
			
			$database = Marion::getDB();
			$where = "label = '{$this->label}' and deleted = 0";

			if($this->id) $where .= " AND id <> {$this->id}";

			$check = $database->select('*','attributeSet',$where);
			
			if( okArray($check) ) return STATIC::ERROR_LABEL_DUPLICATE;
			
			return true;
		}else{
			return STATIC::ERROR_LABEL_EMPTY;	
		}
	} 


	//controlla se esistono le tabelle relative all'oggetto 
	public static function checkTable(){
		$database = Marion::getDB();
		$db = $GLOBALS['setting']['default']['DATABASE']['options']['nome'];
		
		$table = 'attributeSet';
		$check = $database->select('*','information_schema.tables',"table_schema = '{$db}' AND table_name = '{$table}'");
		
		if(!okArray($check)){
			self::writeLog("Tabella {$table} non presente nel database {$db}");
			exit;
		}
		
		$table = 'attributeSetComposition';
		$check = $database->select('*','information_schema.tables',"table_schema = '{$db}' AND table_name = '{$table}'");
		
		if(!okArray($check)){
			self::writeLog("Tabella {$table} non presente nel database {$db}");
			exit;
		}
		
		
	}

	//metodo che che scrive nei log e/o invia messaggi all'admin
	public static function writeLog($message,$type="ERROR")
	{
		
		$class_name = get_called_class();
		$message_log = "{$type} ({$class_name}): {$message}";
		
		if( static::LOG_ENABLED ){
			if(static::PATH_LOG){
				error_log($message_log,0,static::PATH_LOG);
			}else{
				error_log($message_log,0);
			}

		}
		if( static::NOTIFY_ENABLED &&  static::NOTIFY_ADMIN_EMAIL){
			
			if(filter_var(static::NOTIFY_ADMIN_EMAIL, FILTER_VALIDATE_EMAIL)){
				error_log($message_log,1,static::NOTIFY_ADMIN_EMAIL);
			}	
		}
	}

	
	//restituisce la combinazione di attributi
	function getCombinationsValues(){
		$variations = self::getAttributeWithValues();
		foreach($variations as $k => $v){
			$attribute = Attribute::withLabel($k);
			$values = $attribute->getValues(); 
			foreach($values as $value){
				$attributes[$k][] = $value->id; 
			}			
		}
		
		$array = array();
		foreach($attributes as $k => $v){
			$array[] = $v;
		}
		if (isCiro()) {
			debugga($array);exit;
		}
		$res = Marion::combinations($array);
		if( count($array) == 1 ){
			foreach($res as $v){
				$toreturn[] = array($v);
			}
			return $toreturn;
		}

		return $res;

	}


	
	

	


}


?>