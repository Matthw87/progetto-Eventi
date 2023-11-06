<?PHP
namespace Marion\Providers;
/**
* SQL Cache Library.
*
* @package sqlCache
* @author $Author: Sheiko $ 
* @version $Id: sqlCache.lib.php,v 1.0 2005/10/20 05:34:08 Sheiko Exp $
* @since sqlCache v.1.0
* @copyright (C) by Dmitry Sheiko <sheiko@cmsdevelopment.com>
*/


/**
* SQL Cache Class. Needs sqlAPI class
* Apply caching of group SQL queries.
* @package kernel
* @author $Author: Sheiko $ 
*/

define("CACHENOTFOUND", -1);
define("DATABASE_CACHE_START", 1);
define("DATABASE_CACHE_FINISH", 2);

class DatabaseCache extends Database {
	
	var $TraceInfo;
	var $StartTime;
	
	
	/*--------------------CONSTRUCT------------------------------*/
    public function __construct($options){

			parent::__construct($options);
			
			$this->cache_enabled =  $options['cache'];
			$this->cache_file =  $options['cache_file'];
			$this->cache_lifetime =  $options['lifetime'];
			if( $options['pathcache'] ){
				
					$this->pathcache = $_SERVER['DOCUMENT_ROOT']."/".$options['pathcache'];
				
			}
	}
	/*---------------------------------------------------------*/
	function sqlCache() {
		return $this;
	}

	function getCache($Query) {
		$results = array();
		
		if(!$Query OR !is_string($Query)) trigger_error("Invalid input query type", E_USER_ERROR);
		if(!$this->pathcache) trigger_error("CACHEPATH is undefined", E_USER_ERROR);
		$this->trace(DATABASE_CACHE_START);
		$Query = trim($Query);
		$FileName = $this->pathcache.md5($Query);

		
			
		//debugga($FileName);exit;
		if( file_exists($FileName) ){
			if( $this->cache_lifetime  && (int)$this->cache_lifetime > 0){
				$time = time()-filemtime($FileName);
					
				if( $time  >= (int)$this->cache_lifetime ){
					unlink($FileName);
				}
			}
		}

		if(file_exists($FileName)) {
			
			$results = $this->readFile($FileName);
			$this->trace(DATABASE_CACHE_FINISH, $Query);	
			return $results;
		} else return CACHENOTFOUND;
	}
	
	function putCache($Query, $data) {
		
		$Tables = array();
		if(!$Query OR !is_string($Query)) trigger_error("Invalid input query type", E_USER_ERROR);
		if($data AND !is_array($data) ) trigger_error("Invalid input data array type", E_USER_ERROR);
		if(!$this->pathcache) trigger_error("CACHEPATH is undefined", E_USER_ERROR);
		if(!$data) return false;
		
		$Query = trim($Query);
		if( !$Tables = $this->parseReadingQuery($Query) ) return false;
		
		//debugga($Tables);


		$this->addCacheTablesInfo($Query, $Tables);
		
		$FileName = $this->pathcache.md5($Query);
		
		$this->writeFile($FileName, $data);
		
		return true;				
	}
	
	function registerQueryMultiple($Query,$Tables){
		$QueryKey = md5(trim($Query));
		$where = "querytable IN (";
		foreach($Tables as $t){
			$where .= "'{$t}',";
			$toinsert[] = array(
				'querykey' => $QueryKey,
				'query' => $Query,
				'querytable' => $t,
			);
		}
		$where = preg_replace('/,$/',')',$where);
		$query = "DELETE FROM sqlcache WHERE querykey='".$QueryKey."' AND {$where}";
		//error_log('delete query:'.$Query);
		$this->execute($query);
		foreach($toinsert as $insert){
			//error_log('insert query:'.$Query);
			$query_insert = $this->getQueryInsert1('sqlcache',$insert);
			$this->execute($query_insert);
		}
	}

	function registerQueryMultipleInFile($Query,$Tables){
		$QueryKey = md5(trim($Query));
		
		$where = "querytable IN (";
		foreach($Tables as $t){
			if( !file_exists($this->pathcache."/{$t}") ){
				mkdir($this->pathcache."/{$t}");
			}
			$myfile = fopen($this->pathcache."/{$t}/{$QueryKey}", "w") or die("Unable to open file!");
			fclose($myfile);
			
		}
	}

	
	function registerQuery($Query,$Table){
		
		$QueryKey = md5(trim($Query));
		$this->execute("DELETE FROM sqlcache WHERE querykey='".$QueryKey."' AND querytable='".$Table."'");
		$toinsert = array(
				'querykey' => $QueryKey,
				'query' => $Query,
				'querytable' => $Table,
			);
		$query_insert = $this->getQueryInsert1('sqlcache',$toinsert);
		$this->execute($query_insert);
		
	}
	
	function parseModifyingQuery($Query) {
		
		$TablesStr = "";
		$Tables = array();
		$Table = "";
		$Key = 0;
		
		// Syntax: UPDATE table SET expression WHERE condition
		if(preg_match("/^UPDATE\s/is", $Query)) {
			if(preg_match("/(WHERE|SET)/is", $Query)) {
				
				if( !preg_match("/SET/is", $Query)) {
					$TablesStr = preg_replace("/^UPDATE\s(.*)WHERE.*$/is", "\\1", $Query); 
				}else{
					
					$TablesStr = preg_replace("/^UPDATE\s(.*)SET\s(.*)$/is", "\\1", $Query); 
					
				}

				
				
			} else $TablesStr = preg_replace("/^UPDATE\s(.*?)$/is", "\\1", $Query); 
			$Tables = explode(",", trim($TablesStr) );
			
			if($Tables) {
				foreach($Tables as $Key=>$Table) {
					$Tables[$Key] = preg_replace("/^(\w+)\s+?\w*?$/is", "\\1", trim($Table));
					$Tables[$Key] = str_replace("`","", $Tables[$Key]);
				}
			}
			
			//debugga($Tables);
			return $Tables;
		}
		// Syntax: DELETE FROM table WHERE condition
		if(preg_match("/^DELETE\s/is", $Query)) {
			if(preg_match("/(WHERE|LIMIT)/is", $Query)) {
				$TablesStr = preg_replace("/^.*?\sFROM\s(.*?)(WHERE|LIMIT).*$/is", "\\1", $Query); 
			} else $TablesStr = preg_replace("/^.*?\sFROM\s(.*?)$/is", "\\1", $Query); 
			$Tables = explode(",", trim($TablesStr) );
			if($Tables) {
				foreach($Tables as $Key=>$Table) {
					$Tables[$Key] = preg_replace("/^(\w+)\s+?\w*?$/is", "\\1", trim($Table));
					$Tables[$Key] = str_replace("`","", $Tables[$Key]);
				}
			}
			return $Tables;
		}		
	  // Syntax: INSERT INTO table () VALUES()
		if(preg_match("/^INSERT\s/is", $Query)) {		
			$TablesStr = trim(preg_replace("/^.+INTO\s(.*?)\(.*$/is", "\\1", $Query)); 
			$TablesStr=str_replace("`","",$TablesStr);
			return array($TablesStr);
		}
		
	}
	
	function createSQLCache(){
		if( !okArray($this->execute("DESCRIBE sqlcache") ) ) {
			$CreationQuery = 'CREATE TABLE IF NOT EXISTS sqlcache (
				  id int(11) NOT NULL AUTO_INCREMENT,
				  querykey varchar(255) NOT NULL,
				  querytable varchar(128) NOT NULL,
				  query tinytext NOT NULL,
				  timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (id)
				) ';
			
			$this->execute($CreationQuery);
			$check = $this->execute("DESCRIBE sqlcache");
			
			if( !okArray($check) )  trigger_error("Can not create cache table 'sqlcache'", E_USER_ERROR);
		}
	}
	
	function addCacheTablesInfo($Query, $Tables) {

		$QueryKey ='';
		$QueryTable ='';
		/*if( !okArray($this->execute("DESCRIBE sqlcache") ) ) {
			$CreationQuery = 'CREATE TABLE IF NOT EXISTS sqlcache (
				  id int(11) NOT NULL AUTO_INCREMENT,
				  querykey varchar(255) NOT NULL,
				  querytable varchar(128) NOT NULL,
				  query tinytext NOT NULL,
				  timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (id)
				) ';
			
			$this->execute($CreationQuery);
			$check = $this->execute("DESCRIBE sqlcache");
			
			if( !okArray($check) )  trigger_error("Can not create cache table 'sqlcache'", E_USER_ERROR);
		}*/
		
		/*$QueryKey = md5($Query);
		
		foreach($Tables as $QueryTable) {
			$this->execute("DELETE FROM sqlcache WHERE querykey='".$QueryKey."' AND querytable='".$QueryTable."'");
			$this->execute("INSERT INTO sqlcache ( id , querykey , querytable ) VALUES ('', '".$QueryKey."', '".$QueryTable."')");
		}*/
		if( okArray($Tables) ){


			if( $this->cache_file ){
				$this->registerQueryMultipleInFile($Query,$Tables);
			}else{
				$this->registerQueryMultiple($Query,$Tables);
			}
			
		}
		/*foreach($Tables as $QueryTable) {
			$this->registerQuery($Query,$QueryTable);
		}*/
		
		
	}
	
	
	function writeFile($FileName, $data) {
		@unlink($FileName);
		if( $handle = @fopen ($FileName, "w")) {
				if (flock($handle, LOCK_EX)) {
				@fwrite ($handle, serialize($data)); 
				flock($handle, LOCK_UN); }
				@fclose ($handle); 
		} else trigger_error("Can not write into cache file ".basename($FileName), E_USER_ERROR);
	}
	
	function readFile($FileName) {
		$results = array();
		if( $handle = @fopen ($FileName, "r")) {
		$contents = @fread ($handle, filesize ($FileName)); 
		@fclose ($handle); } else trigger_error("Can not open cache file ".basename($FileName), E_USER_ERROR);
		if($contents) $results = unserialize($contents);
		return $results;
	}

	function cleanCacheInFile($Query=false) {
		
		// Clean all cache files and DB rows
		if(!$Query) {
		   if ($dh = opendir($this->pathcache)) { 
		       while (($FileName = readdir($dh)) !== false) { 
		       		if($FileName!="." OR $FileName!="..") @unlink($this->pathcache.$FileName);
		       } 
		       closedir($dh); 
		   }	
		   return true;
		}
		// Clean the cache of certain query key
		
		$Table = "";
		$Key = 0;
	
		$Tables = $this->parseModifyingQuery($Query);
		
		if(!$Tables) return false;
		$all_key = array();
		foreach($Tables as $Key=>$Table) {
			$list = scandir($this->pathcache."/{$Table}");
			if( okArray($list) ){
				if( !okArray($all_key) ){
					$all_key = $list;
				}else{
					$all_key = array_merge($all_key,$list);
				}
				$open=readdir($this->pathcache."/{$Table}");
				while($files=readdir($open)){
					if( file_exists($this->pathcache."/{$Table}/$files") ){
						unlink($this->pathcache."/{$Table}/$files");
					}
				}
			}
		}
		$all_key = array_unique($all_key);
		
		if( okArray($all_key) ){
			foreach($all_key as $v){
				if( $v != '.' && $v != '..'){
					if( file_exists($this->pathcache.$v) ){
						
						@unlink($this->pathcache.$v);
					}
				}
			}
		}
		
		
	}
	
	function cleanCache($Query=false) {
		// Clean all cache files and DB rows
		if(!$Query) {
		   if ($dh = opendir($this->pathcache)) { 
		       while (($FileName = readdir($dh)) !== false) { 
		       		if($FileName!="." OR $FileName!="..") @unlink($this->pathcache.$FileName);
		       } 
		       closedir($dh); 
		   }
		   $this->delete("sqlcache");		
			return true;
		}
		// Clean the cache of certain query key
		
		$Table = "";
		$Key = 0;
		$Listing = array();
		$Row = array();
		$Tables = $this->parseModifyingQuery($Query);
		
		if(!$Tables) return false;
		foreach($Tables as $Key=>$Table) {$Tables[$Key]="'$Table'";}
		$Listing = $this->select("querykey","sqlcache","querytable in (".join(",", $Tables).")");
		
		if($Listing) {
			foreach($Listing as $Row) {
				@unlink($this->pathcache.$Row["querykey"]);
			}
		}
		
		$this->delete("sqlcache","querytable in (".join(",", $Tables).")");
	}

	function trace($Trigger, $Query=false) {
		if($Trigger==DATABASE_CACHE_START) {
			return $this->StartTime = $this->getmicrotime();		
		}
		if($Trigger==DATABASE_CACHE_FINISH) {
			$this->TraceInfo[] = "Time: ".sprintf('%.4f', $this->getmicrotime() - $this->StartTime)." sec \t Query: ".$Query;
			return true;		
		}
	}
		
	function getmicrotime() { 
  		list($usec, $sec) = explode(" ",microtime()); 
   		return ((float)$usec + (float)$sec); 
	}

	function parseReadingQuery ( $query ) {
		
		//rimuovo le parentesi tonde
		$query = preg_replace('/[()]/','',$query);
	   
		
		
		//Make it all lower, we ignore case
		$substr = strtolower($query);
		$substr = $query;
		
		//Remove any subselects
		$substr = preg_replace ( '/\(.*\)/','', $substr);
	  
		//Remove any special charactors
		$substr = preg_replace ( '/[^a-zA-Z0-9_,]/', ' ', $substr);
	   
		//Remove any white space
		$substr = preg_replace('/\s\s+/', ' ', $substr);
	   
		$substr_tmp = strtolower($substr);
	    $pos_from = strpos(strtolower($substr_tmp),' from ');
	  
		//Get everything after FROM
		$substr = substr($substr, $pos_from + 6);
		//$substr = strtolower(substr($substr, strpos(strtolower($substr),' from ') + 6));
		
		//Rid of any extra commands
		$substr = preg_replace(
					Array(
						'/ where .*+$/i',
						'/ group by .*+$/i',
						'/ limit .*+$/i' ,
						'/ having .*+$/i' ,
						'/ order by .*+$/i',
						'/ into .*+$/i'
					   ),'', $substr);
	   
		//Remove any JOIN modifiers
		$substr = preg_replace(
					Array(
						'/ left /i',
						'/ right /i',
						'/ inner /i',
						'/ cross /i',
						'/ outer /i',
						'/ natural /i',
						'/ as /i'
					   ), ' ', $substr);
	   
		//Replace JOIN statements with commas
		$substr = preg_replace(Array('/ join /i', '/ straight_join /'), ',', $substr);
	   

		$out_array = Array();
	   
		//Split by FROM statements
		$st_array = explode (',', $substr);
	 
		foreach ($st_array as $col) {
		 
		  $col = preg_replace(Array('/ on .*+/i'),'', $col);
		 
		  $tmp_array = explode(' ', trim($col));
		 
		  //Oh no, something is wrong, let’s just continue
		  if (!isset($tmp_array[0]))
			continue;
		   
		  $first = $tmp_array[0];
		 
		  //If the “AS” is set, lets include that, if not, well, guess this table isn’t aliased.
		  if (isset($tmp_array[1]))
			$second = $tmp_array[1];
			else 
			$second = $first;
		   
		  if (strlen($first))
		   $out_array[$second] = $first;
		 
		}
		
	   
		return array_values($out_array);
	  }

	



	 //override metodi
	public function select($values,$table,$condition='',$order='',$limit=0,$offset=0){
		
		if( !$this->cache_enabled || trim($table)=='sqlcache' ){
			return parent::select($values,$table,$condition,$order,$limit,$offset);
		}else{
		
			$query = $this->getQuerySelect($values,$table,$condition,$order,$limit,$offset);
			
			$res = $this->getCache($query);
			
			
			$this->lastquery = $query;
			
			if( $res != CACHENOTFOUND ){
				
				return $res;
			}
			
			
			
			$result = $this->conn->query($query);
			
			if(!$result) {
				$this->error = $this->conn->error;
				return false;
			}
			
			$this->error = 'Query OK';
			if($result->num_rows > 0){
				while($row=$result->fetch_assoc()){
					$toreturn[]=$row;
				}
				
				$this->putCache($query,$toreturn);
				return $toreturn;    
			}else{
				return false;    
			}
		}
        
	}


	/*--------------------UPDATE-------------------------------*/
    public function update($table,$condition,$values){
	    $res = parent::update($table,$condition,$values);
		if( $this->cache_enabled ){
			$query = $this->lastquery;

		
			if( $this->cache_file ){
				
				$this->cleanCacheInFile($query);
			}else{
				$this->cleanCache($query);
			}
			
			$this->lastquery = $query;
		}
		return $res;


    }
	/*---------------------------------------------------------*/


	/*--------------------INSERT------------------------------*/
    public function insert1($table,$array){
        $res = parent::insert1($table,$array);
        if( $this->cache_enabled ){
			$query = $this->lastquery;
			
			if( $this->cache_file ){
				$this->cleanCacheInFile($query);
			}else{
				$this->cleanCache($query);
			}
			
			$this->lastquery = $query;
		}
		return $res;

    }
	/*--------------------INSERT------------------------------*/
	


	/*--------------------DELETE------------------------------*/
	  public function delete($table,$condition=''){
		
        $res = parent::delete($table,$condition);
        if( $table == 'sqlcache') return $res;
		
		if( $this->cache_enabled ){
			$query = $this->lastquery;
			
			if( $this->cache_file ){
				$this->cleanCacheInFile($query);
			}else{
				$this->cleanCache($query);
			}
			
			$this->lastquery = $query;
		}
		return $res;
        
    }
	/*--------------------INSERT------------------------------*/

}