<?php
namespace Marion\Providers;
/*********************************************************************
       Costruttore della classe database:

       INPUT: array $options

       $options=array(
            'host'=>'name_host',
            'database'=>'name_database',
            'password'=>'password_database',
            'user'=>'user',
            );

       N.B. l'ordine dei valori nell'array $options non � importante

       EXAMPLE: $my_db=new database($options);

**********************************************************************/

class Database {
public $error;
public $enable_log;
public $lastquery;
public $lastid;
private $host = '';
private $user = '';
private $password = '';
private $port = '';
private $db = '';
public $conn;
protected $injectionPrevent = true;
public $connected = false;
/*--------------------CONSTRUCT------------------------------*/
    public function __construct($options){
        foreach ($options as $k => $v) {
            if($k=='host') $this->host=$v;
            if($k=='user') $this->user=$v;
            if($k=='password') $this->password=$v;
            if($k=='nome') $this->db=$v;
			if($k=='log') $this->enable_log=$v;
			if($k=='port') $this->port=$v;
			
        }
        //debugga($this->db);exit;
        $this->connect();
      }
/*---------------------------------------------------------*/

	public function connect(){
		// connessione a MySQL con l'estensione MySQLi
		if( $this->port ){
			$this->conn = new \mysqli($this->host,$this->user,$this->password,$this->db,$this->port);
		} else {
			$this->conn = new \mysqli($this->host,$this->user,$this->password,$this->db);
		}
		global $_MARION_ENV;
		
		$query_set_character = "SET CHARACTER SET '".$_MARION_ENV["DATABASE"]['options']["charset"]."'";
		mysqli_query($this->conn,$query_set_character);
		$query_set_connection = "SET SESSION collation_connection ='".$_MARION_ENV["DATABASE"]['options']["collation"]."'";
		mysqli_query($this->conn,$query_set_connection);
        if (mysqli_connect_errno()) {
           // notifica in caso di errore
            throw new \Exception("Errore in connessione al DBMS: ".mysqli_connect_error());
        	exit();
 
		}else{
			$this->connected = true;
		}

	}

/*--------------------QUERY CUSTOM------------------------------*/
	public function execute($query){
		 //$this->lastquery = $query;
         
		$result = $this->conn->query($query);
         
		if(!$result) {
			$this->error = $this->conn->error;

			$error = "{$this->lastquery} ($this->error)";
			throw new \Exception($error);
			if( $this->enable_log && $this->error ){
				$this->show_error();
			}
		    return false;
		}
		$this->error = 'Query OK';
		 //se effettuo una select
		 if( is_object ($result) ){
			if($result->num_rows > 0){
			   	while($row=$result->fetch_assoc()){
					$toreturn[]=$row;
				 }
			    return $toreturn;    
			}else{
			    return false;    
			}	 
			 
		 //se effettuto un update, insert, delete
		 }else{
			 return $result;
		 }
		 
		 
	}


/*--------------------QUERY CUSTOM------------------------------*/	

/*--------------------PREPARE INSERT------------------------------*/
	public function getQueryInsert1($table,$array){
        $query="insert into ".$table;
        $key="(";
        $values="(";
        $tipi = $this->type_fields_table($table);
		if( okArray($array) ){

			foreach ($array as $k => $v) {
				$key=$key."`".$k."`, ";
				if( empty($v) ){
					if( $tipi[$k] == 'tinyint' &&  is_bool($v) ){
						if($v){
							$values=$values."true, ";
						}else{
							$values=$values."false, ";
						}
					}else{
						if( is_numeric($v) && intval($v) === 0 ){
							if( $this->injectionPrevent){
								$values=$values.$this->formatta_campo($this->injectionPrevent($v),$tipi[$k]).", ";
							}else{
								$values=$values.$this->formatta_campo($v,$tipi[$k]).", ";
							}
						}else{
							$values=$values."null, ";
						}
					}
				}else{
					if( $this->injectionPrevent){
						$values=$values.$this->formatta_campo($this->injectionPrevent($v),$tipi[$k]).", ";
					}else{
						$values=$values.$this->formatta_campo($v,$tipi[$k]).", ";
					}
				}
				
			}

		}
        
        $key = trim($key,', ');
        $values = trim($values,', ');
        $key=$key.")";
        $values=$values.")";
        $query=$query." ".$key." values ".$values;
       
        return $query;
        

    }

/*--------------------PREPARE INSERT------------------------------*/

/*--------------------INSERT------------------------------*/
    public function insert1($table,$array){
        $query = $this->getQueryInsert1($table,$array);
        
        $this->lastquery = $query;
        $result = $this->conn->query($query);
        if(!$result) {
		    $this->error = $this->conn->error;

			$error = "{$this->lastquery} ($this->error)";

			throw new \Exception($error);
			if( $this->enable_log && $this->error ){
				$this->show_error();
			}
		    return false;
		}else{
			$this->error = 'Query OK';
			$this->lastid = $this->conn->insert_id;
			if($this->conn->insert_id){
				return $this->lastid;
			}else{
				return $result;
			}
			
		}
        
        

    }

    public function insert ($table,$insert){
        $flag=0;
       	$result = array();
		if( okArray($insert) ){
			foreach ($insert as $k => $v){
				if(is_array($v)){
					$result[] = $this->insert1($table,$v);
					$flag=1;
				}else{
					break;
				}
			}
		}
        
        
        
        if($flag==0){ 
	        return $this->insert1($table,$insert);
        }else{
	        return $result;
        }
    }
/*---------------------------------------------------------*/

/*--------------------DELETE-------------------------------*/
    public function delete($table,$condition=''){
        $query = $this->getQueryDelete($table,$condition);
        $this->lastquery = $query;
        $result = $this->conn->query($query);
        if(!$result) {
		    $this->error = $this->conn->error;
			$this->error = $this->conn->error;

			$error = "{$this->lastquery} ($this->error)";
			throw new \Exception($error);
			if( $this->enable_log && $this->error ){
				$this->show_error();
			}
		    return false;
		}else{
			$this->error = 'Query OK';
			return $result;
		}
    }

	public function getQueryDelete($table,$condition=''){
		 if(is_null ($condition) || !$condition){
            $query="delete from ".$table;
        }else{
	        $query="delete from ".$table." where ".$condition;
        }
		return $query;

	}
    
/*---------------------------------------------------------*/

/*--------------------UPDATE-------------------------------*/
    public function update($table,$condition,$values){
	    
		$query = $this->getQueryUpdate($table,$condition,$values);
        
        $this->lastquery = $query;
        
		$result = $this->conn->query($query);
        if(!$result) {
		    $this->error = $this->conn->error;
			$this->error = $this->conn->error;

			$error = "{$this->lastquery} ($this->error)";
			throw new \Exception($error);
			if( $this->enable_log && $this->error ){
				$this->show_error();
			}
		    return false;
		}else{
			$this->error = 'Query OK';
			return $result;
		}
    }

	public function getQueryUpdate($table,$condition,$values){

        $set=" set";
        $campi_tabella = $this->fields_table($table);
        $tipi = $this->type_fields_table($table);
        foreach($values as $k=>$v){
            if(in_array($k, $campi_tabella)){
	            	if( empty($v) ) {

						if( $tipi[$k] == 'tinyint' &&  is_bool($v) ){
							if($v){
								$set=$set." ".$k."=true,";
							}else{
								$set=$set." ".$k."=false,";
							}
						}else{
							if( is_numeric($v) && intval($v) === 0 ){
								$set=$set." ".$k."={$this->formatta_campo($this->injectionPrevent($v),$tipi[$k])},";
							}else{
								$set=$set." ".$k."=null,";
							}
						}
		            	
	            	}else{
		            	$set=$set." ".$k."={$this->formatta_campo($this->injectionPrevent($v),$tipi[$k])},";
	            	}
	            	
            }
        }
		
        $len_set=strlen($set)-1;
        $set = substr($set, 0, $len_set);
        if(is_null ($condition)){
            $query="update ".$table.$set;
        }else{
            $query="update ".$table.$set." where ".$condition;
        }

		return $query;
	}
/*---------------------------------------------------------*/

/*--------------------SELECT-------------------------------*/
    public function select($values,$table,$condition='',$order='',$limit=0,$offset=0){
        if( !$table ) return false;

		$query = $this->getQuerySelect($values,$table,$condition,$order,$limit,$offset);
		
		$this->lastquery = $query;
		
	    $result = $this->conn->query($query);
		if(!$result) {
		    $this->error = $this->conn->error;
			$this->error = $this->conn->error;

			$error = "{$this->lastquery} ($this->error)";
			throw new \Exception($error);
			if( $this->enable_log && $this->error ){
				$this->show_error();
			}
		    return false;
		}
		$this->error = 'Query OK';
		if($result->num_rows > 0){
		    while($row=$result->fetch_assoc()){
				$toreturn[]=$row;
		    }
		    return $toreturn;    
		}else{
		    return false;    
		}
        
    }
/*---------------------------------------------------------*/

/*--------------------PREPARE SELECT-------------------------------*/
    public function getQuerySelect($values,$table,$condition='',$order='',$limit=0,$offset=0){
        if ($condition == '')
            $query="SELECT ".$values." FROM ".$table;
        else{
	    //$condition = $this->injectionPrevent($condition);
            $query="SELECT ".$values." FROM ".$table." WHERE {$condition}";
        	
        }if($order ==! '')
            $query=$query." ORDER BY ".$order;
        if($limit )
			$query=$query." LIMIT ".$limit;
		if( $offset )
            $query=$query." OFFSET ".$offset;
        
		return $query;
    }
/*---------------------------------------------------------*/



public function prepare(string $query):DatabaseQuery{
	return new DatabaseQuery($this,$query);
}

/*--------------------CLOSE DB-------------------------------*/
    public function close(){
        $this->conn->close();
        $this->connected = false;
        unset($GLOBALS['Database']);
    }
/*---------------------------------------------------------*/

/*--------------------FIELDS TABLE-------------------------------*/
// Restituisce il nome dei campi di una tabella
    public function fields_table($table){
        $result = $this->conn->query("SHOW COLUMNS FROM {$table}");
        $array=array();
		if( !$result ){
			$this->error = $this->conn->error;
			$error = "{$this->lastquery} ($this->error)";
			throw new \Exception($error);
			return false;
		}
		
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $array[]=$row['Field'];
            }
        }
        return $array;
    }
/*---------------------------------------------------------*/

/*--------------------CAMPI E TIPI DI UNA TABELLA-------------------------------*/
// Restituisce il nome dei campi di una tabella
    public function type_fields_table($table){
        $result = $this->execute("select column_name, data_type FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name ='{$table}'");
        $toreturn = array();
        if($result){
	        foreach($result as $v){
		        $toreturn[$v['column_name']] = $v['data_type'];
	        }
	        return $toreturn;
        }
        return false;
    }
/*---------------------------------------------------------*/

	/* funzione che controlla se nella stringa ci sia un'injection */
	public function injectionPrevent($string) {
		return $this->conn->real_escape_string($string);
	}

	function myaddslashes($stringa) {
		$stringa = preg_replace("/\\\\$/", '\\\\\\', $stringa);
		$stringa = str_replace("\'", "'", $stringa);
		$stringa = str_replace("'", "\'", $stringa);
		return $stringa;
	}


	function formatta_campo($campo,$tipo){
		if( $tipo == 'tinyint' || $tipo == 'smallint' || $tipo == 'mediumint' || $tipo == 'int' || $tipo == 'bigint' || $tipo == 'decimal' || $tipo == 'float' || $tipo == 'double' || $tipo == 'real' || $tipo == 'bit' || $tipo == 'boolean' || $tipo == 'serial'){
			$campo = $campo;
		}else{
			$campo = "'".$campo."'";
		}
		return $campo;
	}

	function show_error(){
		error_log('ERROR DB:'.$this->error." IN QUERY: ".$this->lastquery,0);
		
	}



	function importFromFile($file){
		

		if( file_exists($file)){
		
			$templine = '';
			// Read in entire file
			$fp = fopen($file, 'r');
			// Loop through each line
			while (($line = fgets($fp)) !== false) {
				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || $line == '')
					continue;
				// Add this line to the current segment
				$templine .= $line;
				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';') {
					// Perform the query
					if(!mysqli_query($this->conn, $templine)){
						error_log('Error performing query' . $templine . ':' . mysqli_error($this->conn) );
					}
					// Reset temp variable to empty
					$templine = '';
				}
			}
			mysqli_close($this->conn);
			fclose($fp);
		}


		
	}



}

class DatabaseQuery{
	
	private $db;
	private $stmt;
	private $query;
	private $vars = array();
	public function __construct(Database $db, string $query){
			$this->db = $db;
			$this->query = $query;
 			$this->stmt = $this->db->conn->prepare($query);
	}


	function setParams(array $list){
		foreach($list as $v){
			$v = array_values($v);
			if( array_key_exists(1,$v)){
				$this->setParam($v[0],$v[1]);
			}else{
				$this->setParam($v[0],$v[1]);
			}
			
		}
	}

	function setParam($value,string $type){
		$normalized_type = 's';
		
			
		switch(strtolower($type)){
			case 'string':
			case 's':
				$normalized_type = 's';
				break;
			case 'i';
			case 'integer':
			case 'int':
			case 'boolean':
			case 'bool':
				$normalized_type = 'i';
				break;
			case 'double':
			case 'd':
				$normalized_type = 'd';
				break;
			case 'blob':
			case 'b':
			case 'binary':
				$normalized_type = 'b';
				break;
				
		}
		
		
		$this->vars[] = array(
			'type' => $normalized_type,
			'value' => $value,
		);
		return $this;
	}

	private function refValues($arr){
		if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
		{
			$refs = array();
			foreach($arr as $key => $value)
				$refs[$key] = &$arr[$key];
			return $refs;
		}
		return $arr;
	}

	function execute(){
		
		$params = array(
			0 => ''
		);
	
		if( okArray($this->vars) ){
			foreach( $this->vars as $k => $v){
				$params[0] .= $v['type'];
				$params[$k+1] = $v['value'];
				
			}
		}

		
		call_user_func_array(array($this->stmt,'bind_param'),$this->refValues($params));

		$this->stmt->execute();
		
		$result = $this->stmt->get_result();
		$this->db->lastquery = $this->replacePlaceHolders($this->query,$params);
		if(!$result) {

		    $this->db->error = $this->db->conn->error;
			if( $this->db->enable_log && $this->db->error ){
				$this->db->show_error();
			}
			$this->stmt->close();
		    return false;
		}else{
			$this->db->error = 'Query OK';
			$list = array();
			while ($row = $result->fetch_assoc())
			{
				$list[] = $row;
			}
			$this->stmt->close();
			return $list;

		}
		
	}

	function replacePlaceHolders($str, $vals)
    {
		$types = $vals[0];
        $i = 1;
        $newStr = "";

        if (empty($vals)) {
            return $str;
        }

        while ($pos = strpos($str, "?")) {
			$type = $types[$i-1];
			
			$val = $vals[$i++];
			
            if (is_object($val)) {
                $val = '[object]';
            }
            if ($val === null) {
                $val = 'NULL';
			}
			if( $type == 's' || $type == 'b'){
				$newStr .= substr($str, 0, $pos) . "'" . $val . "'";
			}else{
				 $newStr .= substr($str, 0, $pos) . $val;
			}
           
            $str = substr($str, $pos + 1);
        }
        $newStr .= $str;
        return $newStr;
    }
}


?>
