<?php
namespace Marion\Core;

class Base{
	
	/************************************* COSTANTI ***************************************************/

	const TABLE = 'tabella'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = '';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = 'parent'; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	/*************************************************************************************************/

	/********************************* VARIABILI DI CLASSE ********************************************/

	protected static $_tableColumns; //contiene i campi della tabella a cui si riferisce l'oggetto
	protected static $_tableColumnsLocale; //contiene i campi della tabella che contiene i dati locali

	protected $_columns = array(); //contiene i campi della tabella a cui si riferisce l'oggetto
	protected $_columnsLocale = array(); //contiene i campi della tabella che contiene i dati locali
	
	protected $_oldObject;
	/*************************************************************************************************/
	
	public $_localeData;

	public $_other_data = [];
	public $last_query;
	public $error_query;


	public string $_type_action='';


	/**
	 * Stack in cui vengono memorizzate le estensioni delle classi
	 *
	 * @var array
	 */
	private static $class_overrides = [];
	private static $class_extensions = [];

	
	
	function showConstants(){
		$array = array(
			"Tabella" => static::TABLE,
			"Identificativo della Tabella" => static::TABLE_PRIMARY_KEY,
			"Campo padre della Tabella" => static::PARENT_FIELD_TABLE,
			"Tabella dati locali" => static::TABLE_LOCALE_DATA,
			"Chiave esterna Tabella dati locali" => static::TABLE_EXTERNAL_KEY,
			"Campo conente il locale" => static::LOCALE_FIELD_TABLE
		);
		debugga($array);
	}
	
	
	/**
	 * metodo che avvia i metodi di inizializzazione dell'oggetto dopo che i dati sono stati settati
	 *
	 * @return void
	 */
	public function init(): void
	{
		$this->getDataInit();
		
	}


	


	public static function addOverride($class){
		$class_name = get_called_class();
		self::$class_extensions[$class] = $class_name;
		self::$class_overrides[$class_name] = $class;
	}



	/**
	 * metodo richiamato quando l'oggetto viene creato per la prima volta
	 *
	 * @return void
	 */
	public function afterLoad(): void
	{


		$class_name = strtolower(preg_replace('/\\\/','_',get_class($this)));
		Marion::do_action('action_after_load_'.$class_name,array($this));
		Marion::do_action('action_entity_after_load',array(self::class,$this));	
	}

	/**
	 * funzione richiamata all'inizio di ogni metodo di tipo factory per la creazione di un nuovo oggetto. In questa funzione viene fatto un controllo
	 * sulla configurazione dell'oggetto stesso
	 *
	 * @return void
	 */
	protected static function initClass(): void{
		self::checkTable();
	}
	

	/**
	 * crea un nuovo oggetto
	 *
	 * @return static
	 */
	public static function create(): static{
		self::initClass();
		$clas_name = get_called_class();
		$object = new $clas_name();
		$object->getColumns();
		$object->init();
		
		return $object;
	}

	/**
	 * metodo che inizializza un oggetto a partire dal suo identificativo (ID)
	 *
	 * @param mixed $id
	 * @return static|null
	 */
	public static function withId($id): ?static
	{
		
		self::initClass();
		if($id){
			$database = Marion::getDB();

			$query = $database->getQuerySelect('*',static::TABLE,static::TABLE_PRIMARY_KEY."=?");
			$data = $database->prepare($query)
					->setParam($id,'int')
					->execute();
			if(okArray($data)){
				return static::withData($data[0]);
			}else{
				return null;
			}

		}else{
			return null;
		}


	}

	/**
	 * inizializza un oggetto da un array
	 *
	 * @param array $data
	 * @return static|null
	 */
	public static function withData($data): ?static
	{


		self::initClass();
		if(okArray($data)){
			
			$class_name = get_called_class();
			if( isset(self::$class_overrides[$class_name]) ){
				$class_name = self::$class_overrides[$class_name];
				while(isset(self::$class_overrides[$class_name]) ){
					$class_name = self::$class_overrides[$class_name];
				}
			}
			

			$object = new $class_name();
			$object->getColumns();
			$object->set($data);
			
			$object->init();
			$object->afterLoad();
			//memorizzo i dati vecchi
			
			$object->setOldObject($object->copyWithId());
			//debugga($object);exit;
			
			return $object;
	
		}else{
			return null;
		}
	}
	

	/**
	 * controlla se un dato è serializzato
	 *
	 * @param mixed $data
	 * @return boolean
	 */
	public static function is_serialized($data): bool
    {
		if( !is_string($data) ) return false;
        return (@unserialize($data) !== false);
    }


	/**
	 * controlla se esiste una colonna nel db
	 *
	 * @param string $field
	 * @return boolean
	 */
	protected function existsColumn(string $field): bool{
		if( !$field ) return false;
		return in_array($field,$this->_columns);
	}

	/**
	 * controlla se esiste una colonna nella tabella multilingua
	 *
	 * @param string $field
	 * @return boolean
	 */
	protected function existsColumnLocale(string $field): bool{
		if( !$field ) return false;
		return in_array($field,$this->_columnsLocale);
	}

	
	function getOldObject(){
		if( is_object($this->_oldObject) ){
			return $this->_oldObject;
		}

		return false;
		
	}

	function setOldObject($obj){
		if( is_object($obj) ){
			$this->_oldObject = $obj;
		}
		
	}

	/**
	 * controlla se siste un'id
	 *
	 * @return boolean
	 */
	function hasId(): bool{
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if( isset($this->$field_id) && $this->$field_id ){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * restituisce l'id
	 *
	 * @return mixed
	 */
	function getId(){
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		
		if( isset($this->$field_id) ){
			return $this->$field_id;
		}else{
			return false;
		}
	}

	/**
	 * restituisce l'id del padre
	 *
	 * @return mixed
	 */
	function getParentId(): mixed{
		$parent_id = STATIC::PARENT_FIELD_TABLE;
		if($this->$parent_id){
			return $this->$parent_id;
		}else{
			return false;
		}
	}

	/**
	 * restituisce l'oggetto padre
	 *
	 * @return static|null
	 */
	function getParent(): ?self{
		$parent = $this->getParentId();
		if($parent){
			return self::withId($parent);
		}else{
			return null;
		}

	}

	
	/**
	 * verifica se l'oggetto ha un padre
	 *
	 * @return boolean
	 */
	public function hasParent(): bool{
		$parent_id = STATIC::PARENT_FIELD_TABLE;
		if($this->$parent_id){
			return true;
		}else{
			return false;
		}
	}

	
	/**
	 * setta i valori non locali di un oggetto
	 *
	 * @param array $data
	 * @return self
	 */
	public function set(?array $data): self
	{
		
		if(okArray($data)){
			foreach($data as $k => $v){
				
				if($this->existsColumn($k)){
					
					if( static::is_serialized($v) ){
						$this->$k = unserialize($v);
					}else{
						$this->$k = $v;
					}
				}elseif($this->existsColumnLocale($k)){
					if(okArray($v)){
						foreach($v as $loc => $v){
							$data['_locale_data'][$loc][$k] = $v;
						}
					}
				}else{
					if( static::is_serialized($v) ){
						$this->_other_data[$k] = unserialize($v);
					}else{
						$this->_other_data[$k] = $v;
					}
				}
			}

		}
		
		if( array_key_exists('_locale_data',$data) && okArray($data['_locale_data']) ){
			$this->setDataFromArray($data['_locale_data']);
		}else{
			$this->setData($data,getConfig('locale','default'));
		}
		
		return $this;
	}

	/**
	 * setta i dati locali dell'oggetto specidicando il locale
	 *
	 * @param array $data
	 * @param string|null $locale
	 * @return self
	 */
	public function setData(array $data,?string $lang = NULL): self
	{	
		
		if(!$lang){ 
			if( defined('_MARION_LANG_') ){
				$lang = _MARION_LANG_;
			}
			if(!$lang){ 
				$lang = STATIC::LOCALE_DEFAULT;	
			}
		}
		if(okArray($data)){
			foreach($data as $k => $v)
			{
				if( $this->existsColumnLocale($k) ){
					
					if(is_array($v)){
						$this->_localeData[$lang][$k] = serialize($v);
					}else{
						$this->_localeData[$lang][$k] = $v;
					}
				}

			}
		}
		
		return $this;
		
	}

	/**
	 * setta i dati locali dell'oggetto da un array
	 *
	 * @param array|null $dataArray
	 * @return self
	 */
	public function setDataFromArray(?array $dataArray): self
	{	
		if( okArray($dataArray) ){
			foreach($dataArray as $locale => $data){
				if(okArray($data)){
					foreach($data as $k => $v)
					{
						if( $this->existsColumnLocale($k) ){
							if(is_array($v)){
								$this->_localeData[$locale][$k] = serialize($v);
							}else{
								$this->_localeData[$locale][$k] = $v;
							}
						}

					}
				}
			}
		}
		return $this;
		
	}
	

	/**
	 * salva i dati locali di un oggetto
	 *
	 * @return void
	 */
	protected function saveLocaleData(): void{
		
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		$locale_old = [];
		if($this->$field_id){
			$database = Marion::getDB();
			
			$locale_select = $database->select(STATIC::LOCALE_FIELD_TABLE,static::TABLE_LOCALE_DATA,static::TABLE_EXTERNAL_KEY."={$this->$field_id}");
			if(okArray($locale_select)){
				foreach($locale_select as $v){
					$locale_old[$v[STATIC::LOCALE_FIELD_TABLE]] = $v[STATIC::LOCALE_FIELD_TABLE];
				}
			}

			
			if( okArray($this->_localeData) ){
				foreach($this->_localeData as $locale => $data){
					$toinsert = $data;
					$toinsert[STATIC::LOCALE_FIELD_TABLE] = $locale;
					$toinsert[STATIC::TABLE_EXTERNAL_KEY] = $this->$field_id;
					
					if(!in_array($locale,$locale_old)){
						$res = $database->insert(static::TABLE_LOCALE_DATA,$toinsert);

						if( !$res ){
							
							throw new \Exception($database->error);
						}
					}else{
						unset($locale_old[$locale]);
						$database->update(static::TABLE_LOCALE_DATA,static::TABLE_EXTERNAL_KEY."={$this->$field_id} AND ".STATIC::LOCALE_FIELD_TABLE."='{$locale}'",$toinsert);
						
					}
					
					
				}
			}
			if(okArray($locale_old)){
				foreach($locale_old as $v){
					$database->delete(static::TABLE_LOCALE_DATA,STATIC::LOCALE_FIELD_TABLE."='{$v}' AND ".static::TABLE_EXTERNAL_KEY."={$this->$field_id}");
				}
			}

		}
	}
	/**
	 * rimuove i dati locali per un fissato locale
	 *
	 * @param string $locale
	 * @return void
	 */
	public function removeData(string $locale): void{
		if(!$locale){ 
			$locale = _MARION_LANG_;
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		unset($this->_localeData[$locale]);
	}

	/**
	 * metodo che verifica se l'oggetto ha figli
	 *
	 * @return boolean
	 */
	public function hasChildren(): bool{
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if($this->$field_id){
			$database = Marion::getDB();
			$check = $database->select(STATIC::TABLE_PRIMARY_KEY,STATIC::TABLE,STATIC::PARENT_FIELD_TABLE."={$this->$field_id}");
			return okArray($check);
		}else{
			return false;
		}
	}
	/**
	 * metodo che prende i figli di un oggetto
	 *
	 * @param string $where
	 * @return array|null
	 */
	public function getChildren($where=NULL): ?array{
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if( !$where ) $where = "1=1";
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
	
	

	/**
	 *  Salva l'oggetto nel database
	 *	se $force_id è true allora è possibile salvare un oggetto specificando in fase di creazione il suo id
	 * @param boolean $force_id
	 * @return static|string|null
	 */
	public function save($force_id=false){
		
		$this->beforeSave();
		
		$check = $this->checkSave();
		
		
		

		$flag = (int)$check;
		if($check == 1){
			$database = Marion::getDB();
		
			foreach($this as $k => $v){
				if( $this->existsColumn($k) ){
					if( is_array($v) ){
						$data[$k] = serialize($v);
					}else{
						$data[$k] = $v;
					}
				}
			}
	
			
			$field_id = STATIC::TABLE_PRIMARY_KEY;
			
			if(isset($this->$field_id) && $this->$field_id){
				if( in_array('updated_at',$this->_columns) ){
					$data['updated_at'] = date('Y-m-d H:i:s');
				}
				
				if( $force_id ){
					$res = $database->insert(STATIC::TABLE,$data);
				}else{
					$res = $database->update(STATIC::TABLE,STATIC::TABLE_PRIMARY_KEY."={$this->$field_id}",$data);
				}
				$this->last_query = $database->lastquery;
				
				if( !$res ){
					throw new \Exception($database->lastquery);
					$this->error_query = $database->error;
				}else{
					unset($this->error_query);
				}
				
			}else{
				if( in_array('created_at',$this->_columns) ){
					$data['created_at'] = date('Y-m-d H:i:s');
				}
				$res = $database->insert(STATIC::TABLE,$data);
				
				$this->last_query = $database->lastquery;
				if( !$res ){
					throw new \Exception($database->lastquery);
					$this->error_query = $database->error;
				}else{
					$this->$field_id = $res;
					unset($this->error_query);
				}		
			}
			
			$this->afterSave();
			return $this;
		}else{
			return $check;
		}

	}
	
	//funzione chiamata prima del salvataggio dell'oggetto. In questa funzione avviene il controllo dei dati (dopo beforeSave()).
	//restituisce true (oppure 1) se va tutto bene altrimenti una stringa contentente l'eticehtta dell'errore
	protected function checkSave(){
		$check = true;
		$class_name = strtolower(preg_replace('/\\\/','_',get_class($this)));
		Marion::do_action('action_check_save_'.$class_name,array($this,&$check));
		Marion::do_action('action_entity_check_save',array(self::class,$this,&$check));		
		return $check;
	}


	//funzione chiamata dopo il salvataggio dell'oggetto
	protected function afterSave(): void{
		
		$this->saveLocaleData();
		//eseguo le eventuali azioni
		$class_name = strtolower(preg_replace('/\\\/','_',get_class($this)));
		Marion::do_action('action_after_save_'.$class_name,array($this));
		Marion::do_action('action_entity_after_save',array(self::class,$this));		
	}

	//funzione chiamata prima del salvataggio dell'oggetto. In questa funzione si effettuano delle operazioni preliminari prima del salvataggio
	public function beforeSave(): void{
		if( $this->getId() ){
			$this->_type_action = 'UPDATE';
		}else{
			$this->_type_action = 'INSERT';
		}
	}


	//metodo che viene richiamato prima di eliminare l'oggetto dal database
	function beforeDelete(): void{
		//Marion::do_action('before_delete_'.strtolower(get_class($this)),array($this));
		$class_name = strtolower(preg_replace('/\\\/','_',get_class($this)));
		Marion::do_action('action_before_delete_'.$class_name,array($this));
		Marion::do_action('action_entity_before_delete',array(self::class,$this));	
	}


	//metodo che elimina dal database l'oggetto
	public function delete(): void{
		$this->beforeDelete();
		if($this->getId()){
			$database = Marion::getDB();
			$field_id = STATIC::TABLE_PRIMARY_KEY;
			if( static::TABLE_LOCALE_DATA ){
				$database->delete(static::TABLE_LOCALE_DATA,static::TABLE_EXTERNAL_KEY."={$this->$field_id}");
			}
			$database->delete(static::TABLE,static::TABLE_PRIMARY_KEY."={$this->$field_id}");
		}
		$class_name = strtolower(preg_replace('/\\\/','_',get_class($this)));
		Marion::do_action('action_after_delete_'.$class_name,array($this));
		Marion::do_action('action_entity_after_delete',array(self::class,$this));	
	}
	

	/**
	 * rimuove tutti i figli (a tutti i livelli) di un oggetto
	 *
	 * @return void
	 */
	public function deleteChildren(): void{
		$parent_field = STATIC::PARENT_FIELD_TABLE;
		$list = [];
		if($parent_field && $this->getId()){
			$allChildren = self::prepareQuery()->where($parent_field,$this->getId())->get();
			if( okArray($allChildren) ){
				foreach($allChildren as $v){
					$list[$v->id] = $v; 	
				}
			}
			$cont_old = 0;

			while( count($list) != $cont_old  ){
				foreach($list as $val){	
					$cont_old = count($list);
					if($val->hasChildren()){
						
						$figli = $val->getChildren();
						
						foreach($figli as $f){
							$list[$f->id] = $f;	
						}
					}	
				}	
			}
			foreach($list as $v){
				$v->delete();	
			}
		}
	}
	
	

	/**
	 * restituisce il valore di un attributo. Se il valore è locale specificando il locale restituisce il valore apportuno altrimenti restituisce quello relativo al locale di default
		$field : campo da mostrare
		$locale: lingua in cui si vuole visualizzare il valore
		$truncate; lunghezza massima del valore da mostrare
	 *
	 * @param string $field
	 * @param string|null $locale
	 * @param int|null $truncate
	 * @return mixed|null
	 */
	function get(string $field,?string $lang=NULL,?int $truncate=NULL){
		$value = null;
		if(!$lang){ 
			$lang = _MARION_LANG_;
		}
		if( property_exists($this,$field) ){
			$value =  $this->$field;
		}else{
			if(isset($this->_localeData[$lang]) && okArray($this->_localeData[$lang]) && array_key_exists($field,$this->_localeData[$lang])){
				$value =  $this->_localeData[$lang][$field];
			}
		}
		if( $truncate ){
			$value = strip_tags($value);

			if (strlen($value) > $truncate) {

				// truncate string
				$value = substr($value, 0, $truncate);

				// make sure it ends in a word so assassinate doesn't become ass...
				$value = substr($value, 0, strrpos($value, ' ')).'...'; 
			}
		}
		return $value;
	}
	

	//metodo che restituisce i valori locali dell'oggetto
	function valuesData($locale='all'){
		if(okArray($this->_localeData)){
			if($locale != 'all'){
				if(array_key_exists($locale,$this->_localeData)){
					return $this->_localeData[$locale];
				}else{
					return false;
				}
				
			}else{
				return $this->_localeData;
			}
		}else{
			return false;
		}
	}


	/**
	 * effettua la copia di un oggetto
	 *
	 * @return self
	 */
	public function copyWithId(): self{
		$obj = clone $this;
		return $obj;
	}
	
	/**
	 * effettua la copia di un oggetto
	 *
	 * @return self
	 */
	public function copy(): self{
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		$obj = clone $this;
		unset($obj->$field_id);
		return $obj;
	}

	/**
	 * metodo che inizializza l'array dei dati locali della tabella
	 *
	 * @return void
	 */
	protected function getDataInit(): void{
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if(isset($this->$field_id) && $this->$field_id ){
			
			if(static::TABLE_LOCALE_DATA){
				$database = Marion::getDB();
				$data = $database->select('*',static::TABLE_LOCALE_DATA,static::TABLE_EXTERNAL_KEY."={$this->$field_id}");
				if( okArray($data) ){
					foreach($data as $v){
						if(okArray($v)){
							unset($v[static::TABLE_EXTERNAL_KEY]);
							$app = $v[STATIC::LOCALE_FIELD_TABLE];
							unset($v[STATIC::LOCALE_FIELD_TABLE]);
							$this->_localeData[$app] = $v;
						}
					}
				}
			}
			

		}
	}


	/**
	 * prende le colonne delle tabelle interessate nella classe e le memorizza in variabili di classe
	 *
	 * @return void
	 */
	function getColumns(): void{
		$database = Marion::getDB();
		$columns = $database->fields_table(STATIC::TABLE);

		$this->_columns = $columns;
		
		if(STATIC::TABLE_LOCALE_DATA){
			$columns = $database->fields_table(STATIC::TABLE_LOCALE_DATA);
			$this->_columnsLocale = $columns;
		}	
	}

	/**
	 * Undocumented function
	 *
	 * @param string $tipo
	 * @return mixed
	 */
	 function getColumnsArray(?string $tipo=NULL): ?array{
		if($tipo == 'locale'){
			return $this->_columnsLocale;
		}else{
			return $this->_columns;
		}	
	}


	
	/**
	 * controlla se esistono le tabelle relative all'oggetto 
	 *
	 * @return void
	 */
	protected static function checkTable(): void{
		
		$database = Marion::getDB();
		$db = $GLOBALS['setting']['default']['DATABASE']['options']['nome'];
		
		$table = STATIC::TABLE;
		$check = $database->select('*','information_schema.tables',"table_schema = '{$db}' AND table_name = '{$table}'");
		
		if(!okArray($check)){
			throw new \Exception("{$table}'s table not exists in {$db}'database");
		}
		
		if(STATIC::TABLE_LOCALE_DATA){
			$table = STATIC::TABLE_LOCALE_DATA;
			$check = $database->select('*','information_schema.tables',"table_schema = '{$db}' AND table_name = '{$table}'");
			
			if(!okArray($check)){
				throw new \Exception("{$table}'s table not exists in {$db}'database");
				
			}
		}
		
	}

	/**
	 * get data object
	 *
	 * @return mixed
	 */
	function getDataForm(): mixed{
		$locales = Marion::getConfig('locale','supportati');
		foreach($this as $k => $v){
			if($k == '_localeData'){
				if(okArray($v)){
						foreach($locales as $lo){
							if( array_key_exists($lo,$this->_localeData) && !$this->_localeData[$lo]){
								
								foreach($this->_columnsLocale as $field){
									if($field != STATIC::LOCALE_FIELD_TABLE && $field != STATIC::TABLE_EXTERNAL_KEY){
										$this->_localeData[$lo][$field] = null;
									}
								}
							}
						}
						
						foreach($this->_localeData as $loc => $values){
							foreach($values as $k1 => $v1){
								if($k1 != STATIC::LOCALE_FIELD_TABLE){
									$data[$k1][$loc] = $v1;
								}
							}
						}
				}

			}else{
				$data[$k] = $v;
				
			}
		}
		unset($data['_columns']);
		unset($data['_columnsLocale']);
		unset($data['_oldObject']);
		
		return $data;
	}
	

	function prepareForm2(){
		trigger_error("Base::prepareForm2 Deprecated function called. Use Base::getDataForm", E_USER_DEPRECATED);
		$locales = Marion::getConfig('locale','supportati');
		foreach($this as $k => $v){
			if($k == '_localeData'){
				if(okArray($v)){
						//debugga($this->_localeData);exit;
						foreach($locales as $lo){
							if( !$this->_localeData[$lo]){
								
								foreach($this->_columnsLocale as $field){
									if($field != STATIC::LOCALE_FIELD_TABLE && $field != STATIC::TABLE_EXTERNAL_KEY){
										$this->_localeData[$lo][$field] = null;
									}
								}
							}
						}
						
						foreach($this->_localeData as $loc => $values){
							foreach($values as $k1 => $v1){
								if($k1 != STATIC::LOCALE_FIELD_TABLE){
									$data[$k1][$loc] = $v1;
								}
							}
						}
				}

			}else{
				$data[$k] = $v;
				
			}
		}
		unset($data['_columns']);
		unset($data['_columnsLocale']);
		unset($data['_oldObject']);
		
		return $data;
	}


	//prepara i dati di un form. Riceve in input un parametro che se è uguale ad 'all' prende tutti i valori locali altrimenti solo quelli di un locale assegnato
	function prepareForm($locale='all'){
		trigger_error("Base::prepareForm2 Deprecated function called. Use Base::getDataForm", E_USER_DEPRECATED);
		foreach($this as $k => $v){
			if($k == '_localeData'){
				if(okArray($v)){
					if($locale == 'all'){
						foreach($this->_localeData as $loc => $values){
							foreach($values as $k1 => $v1){
								if($k1 != STATIC::LOCALE_FIELD_TABLE){
									$data[$k1."_{$loc}"] = $v1;
								}
							}
						}
					}else{
						foreach($this->_localeData as $loc => $values){
							foreach($values as $k1 => $v1){
								if( $locale == $loc){
									if($k1 != STATIC::LOCALE_FIELD_TABLE){
										$data[$k1] = $v1;
									}
								}
							}
						}
					}

				}

			}else{
				$data[$k] = $v;
				
			}
		}
		return $data;
	}

	
	//crea l'albero dei figli a partire da un array
	public static function buildtree($src_arr, $parent_id = 0, $tree = array())
	{
		if(STATIC::PARENT_FIELD_TABLE){
			$parent = STATIC::PARENT_FIELD_TABLE;
			$id = STATIC::TABLE_PRIMARY_KEY;
			
			foreach($src_arr as $idx => $row)
			{
				$parent = STATIC::PARENT_FIELD_TABLE;
				if($row->$parent == $parent_id)
				{
					$tree[$row->$id] = $row;
					unset($src_arr[$idx]);
					$tree[$row->id]->children = self::buildtree($src_arr, $row->$id);
				}
			}
			return $tree;
		}else{
			return false;
		}
	}
	
	
	
	public static function prepareQuery(){
		self::initClass();
		$object = self::create();
		$object->getColumns();
		
		$data = array(
			'table' => STATIC::TABLE,
			'table_locale' => STATIC::TABLE_LOCALE_DATA,
			'primary_key' => STATIC::TABLE_PRIMARY_KEY,
			'key_external' => STATIC::TABLE_EXTERNAL_KEY,
			'parent' => STATIC::PARENT_FIELD_TABLE,
			//'locale_column'=> STATIC::LOCALE_FIELD_TABLE,
			'columns' => $object->getColumnsArray(),
			'columns_locale' => $object->getColumnsArray('locale'),
			'obj' => get_called_class()
		);
		
		$query = PrepareQuery::create($data);
		return $query;
	}

	public function __call($name, $arguments)
	{

		//$class_name = strtolower(preg_replace('/\\\/','_',get_class($this)));
		//Marion::do_action('action_add_method_'.$class_name,array($this));
		//debugga($name);exit;
		$class = static::class;
		if( $name == 'getFormattedPriceValueWithoutTax'){
			if( array_key_exists($class, self::$class_extensions) ){
				$class = self::$class_extensions[$class];
			}
		}

		return Marion::do_action('action_add_entity_method',array(
			$class,
			$this,
			$name,
			$arguments),
			true
		);

	}

	
	
	

}
?>