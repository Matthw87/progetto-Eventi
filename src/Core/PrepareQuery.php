<?php
namespace Marion\Core;
class PrepareQuery{
	public $obj;
	public $table;
	public $table_locale;
	public $primary_key;
	public $key_external;
	public $groupBy;
	public $offset;
	public $columns = array();
	public $columns_locale = array();
	public $join_array = array();
	public $left_join_array = array();
	public $right_join_array = array();
	public $field_select = array();


	public $condition = '';
	public $type_field = '';
	public $limit;
	public $order;
	public $customQuery = '';
	public $lastquery = '';
	public $error = '';
	


	public function init(){
		$this->obj='';
		$this->table = '';
		$this->table_locale = '';
		$this->primary_key = '';
		$this->key_external = '';
		$this->columns = array();
		$this->columns_locale = array();
		$this->join_array = array();
		$this->left_join_array = array();
		$this->right_join_array = array();
		$this->field_select = array();
	}
	
	public static function	create($array=array()){
		$query = new prepareQuery();
		foreach($array as $k => $v){
			$query->$k = $v;
		}
		$database = Marion::getDB();
		$type_table = $database->type_fields_table($query->table);
		if($query->table_locale){
			$type_table_locale = $database->type_fields_table($query->table_locale);
		
			if(okArray($type_table_locale)){
				$type_table = array_merge($type_table,$type_table_locale);
			}
		}
		$query->type_field = $type_table;
		$query->reset();
		return $query;
	}

	/**
	 * Add condition null on field
	 *
	 * @param string $key
	 * @return self
	 */
	function whereNull($key): self{
		$alias = '';
		if($key){
			if(in_array($key,$this->columns) || in_array($key,$this->columns_locale)){
				if( okArray($this->columns_locale) ){
					if( in_array($key,$this->columns) ){
						$alias = 't1.';
					}else{
						$alias = 't2.';
					}
				}
				if( $this->condition ){
					$this->condition .= " AND {$alias}{$key} IS NULL AND ";
				}else{
					$this->condition .= " {$alias}{$key} IS NULL AND ";
				}
				$this->condition = preg_replace('/AND $/','',$this->condition);
			}
		}
		return $this;
	}

	/**
	 * Add condition not null on field
	 *
	 * @param string $key
	 * @return self
	 */
	function whereNotNull($key): self{
		$alias = '';
		if($key){
			if(in_array($key,$this->columns) || in_array($key,$this->columns_locale)){
				
				if( okArray($this->columns_locale) ){
					if( in_array($key,$this->columns) ){
						$alias = 't1.';
					}else{
						$alias = 't2.';
					}
				}
				if( $this->condition ){
					$this->condition .= " AND {$alias}{$key} IS NOT NULL AND ";
				}else{
					$this->condition .= " {$alias}{$key} IS NOT NULL AND ";
				}
				$this->condition = preg_replace('/AND $/','',$this->condition);
			}
		}

		return $this;
			
	}

	/**
	 * Add contiction where on field
	 * 
	 * $operetor può assumere valore  '=','<>','IN','NOT IN','LIKE','ILIKE'
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param string $operator
	 * @return self
	 */
	function where(string $key,$value,$operator='='): self{
		$operator = trim(strtoupper($operator));
		$alias = '';
		if($key){
			if( $operator == '=' || $operator == '<>' || $operator == '>' || $operator == '<' || $operator == '>=' || $operator == '<='){
				if(in_array($key,$this->columns) || in_array($key,$this->columns_locale)){
					if( okArray($this->columns_locale) ){
						if( in_array($key,$this->columns) ){
							$alias = 't1.';
						}else{
							$alias = 't2.';
						}
					}
					
					//unset($alias);
					$database = Marion::getDB();
					$value = $database->formatta_campo($value,$this->type_field[$key]);
					if( $this->condition ){
						$this->condition .= " AND {$alias}{$key} {$operator} {$value} AND ";
					}else{
						$this->condition .= "{$alias}{$key} {$operator} {$value} AND ";
					}	
				}
			}else{
				if(in_array($key,$this->columns) || in_array($key,$this->columns_locale)){
					if( okArray($this->columns_locale) ){
						if( in_array($key,$this->columns) ){
							$alias = 't1.';
						}else{
							$alias = 't2.';
						}
					}
					//unset($alias);
					if( $this->condition ){
						$this->condition .= " AND {$alias}{$key} {$operator} {$value} AND ";
					}else{
						$this->condition .= "{$alias}{$key} {$operator} {$value} AND ";
					}
				}
			}
			$this->condition = preg_replace('/AND $/','',$this->condition);
		}
		return $this;
	}


	function whereExpression($condition=NULL){
		if($condition){
			if( $this->condition ){
				$this->condition .= " AND {$condition} ";
			}else{
				$this->condition .= "{$condition} ";
			}
				
		}
		return $this;
	}
	
	//$operetor può assumere valore  '=','<>','IN','NOT IN','LIKE','ILIKE'
	function whereMore($where=array(),$operator="="): self{
		$operator = trim(strtoupper($operator));
		if(okArray($where)){
			foreach($where as $k => $v){
				$this->where($k,$v,$operator);	
			}
		}
		return $this;
		
	}

	//$operetor può assumere valore  '=','<>','IN','NOT IN','LIKE'
	function orWhere($key,$value,$operator='='){
		$operator = trim(strtoupper($operator));
		$alias = '';
		if($key){
			if( $operator == '=' || $operator == '<>' || $operator == '>' || $operator == '<' || $operator == '>=' || $operator == '<='){
				if(in_array($key,$this->columns) || in_array($key,$this->columns_locale)){
					if( in_array($key,$this->columns) ){
						$alias = 't1.';
					}else{
						$alias = 't2.';
					}
					unset($alias);
					$database = Marion::getDB();
					$value = $database->formatta_campo($value,$this->type_field[$key]);
					if( $this->condition ){
						$this->condition .= " OR {$alias}{$key} {$operator} {$value} AND ";
					}else{
						$this->condition .= "{$alias}{$key} {$operator} {$value} AND ";
					}	
				}
			}else{
				unset($alias);
				if( $this->condition ){
					$this->condition .= " OR {$alias}{$key} {$operator} {$value} AND ";
				}else{
					$this->condition .= "{$alias}{$key} {$operator} {$value} AND ";
				}
			}
			$this->condition = preg_replace('/AND $/','',$this->condition);
		}
		return $this;
	}

	function orWhereExpression($condition=NULL){
		if($condition){
			if( $this->condition ){
				$this->condition .= " OR {$condition} ";
			}else{
				$this->condition .= "{$condition} ";
			}
				
		}
		return $this;
	}
	
	//$operetor può assumere valore  '=','<>','IN','NOT IN','LIKE','ILIKE'
	//$operetor può assumere valore  '=','<>','IN','NOT IN','LIKE','ILIKE'
	function orWhereMore($where=array(),$operator="="){
		$operator = trim(strtoupper($operator));
		if(okArray($where)){
			
			foreach($where as $k => $v){
				$this->orWhere($k,$v,$operator);
			}

		}
		return $this;
		
	}

	function orderByMore($orderBy=array(),$other_fields=array()){
		if(okArray($orderBy)){
			foreach($orderBy as $k => $v){
				if( strtolower($k) == 'rand()' ){
					$this->order .= "{$k}, ";
				}else{
					if(in_array($k,$this->columns) || in_array($k,$this->columns_locale) || in_array($k,$other_fields)){
						if( in_array($k,$this->columns_locale) || in_array($k,$other_fields) ){
							$this->field_select[] = $k;
						}
						$this->order .= "{$k} {$v}, ";
					}
				}
			}
		}
		return $this;
	}
	function groupBy($condiction = NULL){
		$this->groupBy = $condiction;
	}

	function orderBy($column,$type="ASC",$other_fields=array()){
		if( strtolower($column) == 'rand()' ){
			$this->order .= "{$column}, ";
		}else{
			if(in_array($column,$this->columns) || in_array($column,$this->columns_locale) || in_array($column,$other_fields)){
				if( in_array($column,$this->columns_locale) || in_array($column,$other_fields) ){
					$this->field_select[] = $column;
				}
				$this->order .= "{$column} {$type}, ";
			}
		}
		return $this;
	}
	
	function setFieldSelect($field){
		if( $field ){
			$this->field_select[] = $field;
		}

	}


	function join($table='',$condition=''){
		if( $table && $condition ){
			$this->join_array[] = array(
				'table' => $table,
				'condition' => $condition,
			);
		}
	}

	function leftOuterJoin($table='',$condition=''){
		if( $table && $condition ){
			$this->left_join_array[] = array(
				'table' => $table,
				'condition' => $condition,
			);
		}
	}

	function rightOuterJoin($table='',$condition=''){
		if( $table && $condition ){
			$this->right_join_array[] = array(
				'table' => $table,
				'condition' => $condition,
			);
		}
	}

	function limit($limit=0){
		$this->limit = $limit;
		return $this;
	}

	function offset($offset=0){
		$this->offset = $offset;
		return $this;
	}
	

	function setTable($table = NULL){
		$this->table = $table;
	}

	function setTableLocale($table = NULL){
		$this->table_locale = $table;
	}


	function custom($custom=NULL){
		$this->customQuery = $custom;
	}

	function getCount(){
		$database = Marion::getDB();
		$group_by = '';
		$_paramters_select = '';
		if(!$this->condition) $this->condition = "1=1";
		

		
		$campi_raggrupppamento[] = 't1.id';
		if( okArray($this->field_select) ){
			foreach( $this->field_select as $v ){
				$explode = explode(' as ',$v);
				
				if( $explode[0] ){
					$campi_raggrupppamento[] = trim($explode[0]);
				}else{
					$explode = explode(' AS ',$v);

					if( $explode[1] ){
						$campi_raggrupppamento[] = trim($explode[1]);
					}else{
						$campi_raggrupppamento[] = trim($v);
					}
				}
			}
			
		}
		if( count($campi_raggrupppamento) > 1 ){
			foreach($campi_raggrupppamento as $v){
				$group_by .=  $v.",";
			}
			$group_by = preg_replace('/\,$/','',$group_by);
			$this->groupBy = $group_by;
			
		}

		
		
		if( $this->groupBy ){
			$this->condition .= " group by ".$this->groupBy;
		}
		
		if($this->table_locale && $this->key_external){
			$table_select = "{$this->table} as t1 left outer join {$this->table_locale} as t2 on t1.{$this->primary_key} = t2.{$this->key_external}";
		}else{
			$table_select = "{$this->table}";
		}
		if( okArray($this->join_array) ){
			foreach($this->join_array as $v){
				$table_select = "(".$table_select.") JOIN {$v['table']} on {$v['condition']}";
			}
		}

		

		if( okArray($this->left_join_array) ){
			foreach($this->left_join_array as $v){
				$table_select = "(".$table_select.") LEFT OUTER JOIN {$v['table']} on {$v['condition']}";
			}
		}

		if( okArray($this->right_join_array) ){
			foreach($this->right_join_array as $v){
				$table_select = "(".$table_select.") RIGHT OUTER JOIN {$v['table']} on {$v['condition']}";
			}
		}

		if( okArray($this->field_select) ){
			foreach( $this->field_select as $v ){
				$_paramters_select .= ", ".$v;
			}
			
		}

		
		if( $this->groupBy ){
			$_paramters_select = 'count(*)';
		}else{
		
		
			if($this->table_locale && $this->key_external){
				$_paramters_select = 'count(distinct t1.id)';
			}else{
				$_paramters_select = 'count(*)';
			}
		}
		
		$cont = $database->select("{$_paramters_select} as cont",$table_select,$this->condition);
		
		if( $this->groupBy ){
			
			
			$cont = count($cont);
		}else{
			$cont = $cont[0]['cont'];
		}
		
		
		return $cont;
	}
	
	/*function getCount(){
		$toreturn = array();
		$database = Marion::getDB();
		
		if(!$this->condition) $this->condition = "1=1";

		if( $this->groupBy ){
			$this->condition .= " group by ".$this->groupBy;
		}

		if($this->order){ 
			$this->order = preg_replace('/, $/','',$this->order);
			$this->condition .= " order by ".$this->order;
		}
		if($this->limit){ 
			$this->condition .= " limit ".$this->limit;
		}

		if($this->offset){ 
			$this->condition .= " offset ".$this->offset;
		}
		
		if($this->table_locale && $this->key_external){
			$table_select = "{$this->table} as t1 left outer join {$this->table_locale} as t2 on t1.{$this->primary_key} = t2.{$this->key_external}";
		}else{
			$table_select = "{$this->table}";
		}
		if( okArray($this->join_array) ){
			foreach($this->join_array as $v){
				$table_select = "(".$table_select.") JOIN {$v['table']} on {$v['condition']}";
			}
		}

		

		if( okArray($this->left_join_array) ){
			foreach($this->left_join_array as $v){
				$table_select = "(".$table_select.") LEFT OUTER JOIN {$v['table']} on {$v['condition']}";
			}
		}

		if( okArray($this->right_join_array) ){
			foreach($this->right_join_array as $v){
				$table_select = "(".$table_select.") RIGHT OUTER JOIN {$v['table']} on {$v['condition']}";
			}
		}
		

		if($this->table_locale && $this->key_external){
			$_paramters_select = 'count(DISTINCTROW t1.id)';
		}else{
			$_paramters_select = 'count(*)';
		}
		
		
		$cont = $database->select("{$_paramters_select} as cont",$table_select,$this->condition);
	
		
		return $cont[0]['cont'];
	}*/


	function get(){
		$toreturn = array();
		$database = Marion::getDB();
		
		if(!$this->condition) $this->condition = "1=1";

		if( $this->groupBy ){
			$this->condition .= " group by ".$this->groupBy;
		}

		if($this->order){ 
			$this->order = preg_replace('/, $/','',$this->order);
			$this->condition .= " order by ".$this->order;
		}
		if($this->limit){ 
			$this->condition .= " limit ".$this->limit;
		}

		if($this->offset){ 
			$this->condition .= " offset ".$this->offset;
		}
		
		if($this->table_locale && $this->key_external){
			$table_select = "{$this->table} as t1 left outer join {$this->table_locale} as t2 on t1.{$this->primary_key} = t2.{$this->key_external}";
		}else{
			$table_select = "{$this->table}";
		}
		if( okArray($this->join_array) ){
			foreach($this->join_array as $v){
				$table_select = "(".$table_select.") JOIN {$v['table']} on {$v['condition']}";
			}
		}

		

		if( okArray($this->left_join_array) ){
			foreach($this->left_join_array as $v){
				$table_select = "(".$table_select.") LEFT OUTER JOIN {$v['table']} on {$v['condition']}";
			}
		}

		if( okArray($this->right_join_array) ){
			foreach($this->right_join_array as $v){
				$table_select = "(".$table_select.") RIGHT OUTER JOIN {$v['table']} on {$v['condition']}";
			}
		}
		if($this->table_locale && $this->key_external){
			$_paramters_select = 'DISTINCTROW t1.*';
		}else{
			$_paramters_select = '*';
		}
		
		if( okArray($this->field_select) ){
			foreach( $this->field_select as $v ){
				$_paramters_select .= ", ".$v;
			}
			
		}

		
		
		$data = $database->select($_paramters_select,$table_select,$this->condition);
	
		$this->lastquery = $database->lastquery;
		$this->error = $database->error;
		
		if( okArray($data) ){
			foreach($data as $v){
				
				if(okArray($v)){
					$class_name = $this->obj;
					$object = new $class_name();
					
					$object->getColumns();
					$object->set($v);
					if(method_exists($object,'init')){
						$object->init();
					}

					if(method_exists($object,'afterLoad')){
						$object->afterLoad();
						$object->setOldObject($object->copyWithId()); 
					}
					
					$toreturn[] = $object;
				}
			}
		}
		
		return $toreturn;
	}

	function getCollection(){
		$data = $this->get();
		$collection = Collection::fromIterable($data);
				
		return $collection;

	}

	function getOne(){
		$this->limit = 1;
		$result = $this->get();
		if(okArray($result)){
			return $result[0];
		}
	}
	
	

	function reset(){
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		$this->lastquery = '';
		$this->error = '';
	}



	/*public function __call($name, $arguments)
	{
		$class_name = strtolower(preg_replace('/\\\/','_',get_class($this)));
		Marion::do_action('action_add_method_'.$class_name,array($this,$name,$arguments));
		return Marion::do_action('action_add_entity_method',array(
			static::class,
			$this,
			$name,
			$arguments)
		);	
	}*/


}

?>