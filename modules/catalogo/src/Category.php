<?php
namespace Catalogo;
use Marion\Core\{BaseWithImages,Marion};
use Illuminate\Database\Capsule\Manager as DB;
class Category extends BaseWithImages{
	
	// COSTANTI DI BASE
	const TABLE = 'product_categories'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'product_category_langs'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'product_category_id';// nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = 'parent_id'; //nome del campo padre 
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	

	// COSTANTI RELATIVE ALLA CLASSE SEZIONE 
	const NAME_FIELD_TABLE = 'name'; //campo contenete il nome della sezione 

	
	public $product_category_related = [];
	public $id;
	public $parent_id;

	
	/**
	 * restituisce l'url della categoria
	 *
	 * @param [type] $locale
	 * @return string
	 */
	function getUrl($locale=NULL): string{
		
		
		$id = $this->getId();
		
		$prettyUrl = $this->get('slug',$locale);
		if($prettyUrl){
			$name = $prettyUrl; 	
		}else{
			$name = $this->get('name',$locale);
		}
		$name = $name?Marion::slugify($name):'';
		return _MARION_BASE_URL_."catalogo/category/".$this->id."/".$name;

	}



	public static function getAll($locale='it'){
		$database = Marion::getDB();
		//$sezioni = $database->select('*',STATIC::TABLE.' as s join '.STATIC::TABLE_LOCALE_DATA.' as l on s.'.STATIC::TABLE_PRIMARY_KEY.'=l.'.STATIC::TABLE_EXTERNAL_KEY,"locale='{$locale}'");
		//if( isDev() ){
		$sezioni = self::prepareQuery()->get();
		$tree = self::buildTree($sezioni);
		
		$toreturn = [];
		foreach($tree as $level1){
			$toreturn[$level1->id] = $level1->get('name');
			if( okArray($level1->children ) ){
				foreach($level1->children as $level2){
					$toreturn[$level2->id] = $level1->get('name')." / ".$level2->get('name');
					if( okArray($level2->children ) ){
						foreach($level2->children as $level3){
							$toreturn[$level3->id] = $level1->get('name')." / ".$level2->get('name')." / ".$level3->get('name');
							if( okArray($level3->children ) ){
								foreach($level3->children as $level4){
									$toreturn[$level4->id] = $level1->get('name')." / ".$level2->get('name')." / ".$level3->get('name')." / ".$level4->get('name');
									if( okArray($level4->children ) ){
										foreach($level4->children as $level5){
											$toreturn[$level5->id] = $level1->get('name')." / ".$level2->get('name')." / ".$level3->get('name')." / ".$level4->get('name')." / ".$level5->get('name');
												
											if( okArray($level5->children ) ){
												foreach($level5->children as $level6){
													$toreturn[$level6->id] = $level1->get('name')." / ".$level2->get('name')." / ".$level3->get('name')." / ".$level4->get('name')." / ".$level5->get('name')." / ".$level6->get('name');
												}
											}
										}
									}
								}
							}
						}
					}
				}

			}
		}
		if( okArray($toreturn) ){
			uasort($toreturn,function($a,$b){
				if ($a == $b) {
				   return 0;
			   }
			   return ($a < $b) ? -1 : 1;
		   });
		}
		
			
		return $toreturn;
	
	}


	public function getFullName($locale='it'){
		
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if($this->$field_id){
			
			$database = Marion::getDB();
			$filed_id = STATIC::TABLE_EXTERNAL_KEY;
			$sezione = $database->select('*',STATIC::TABLE.' as s join '.STATIC::TABLE_LOCALE_DATA.' as l on s.'.STATIC::TABLE_PRIMARY_KEY.'=l.'.STATIC::TABLE_EXTERNAL_KEY,"locale='{$locale}' and ".STATIC::TABLE_PRIMARY_KEY."={$this->$field_id}");
			
			if(okArray($sezione)){
				$sezione = $sezione[0];
				$array_name = array($sezione[STATIC::NAME_FIELD_TABLE]);
				$current = $sezione;
				while(okArray($current) && $current[STATIC::PARENT_FIELD_TABLE] != 0){
					
					$current = $database->select('*',STATIC::TABLE.' as s join '.STATIC::TABLE_LOCALE_DATA.' as l on s.'.STATIC::TABLE_PRIMARY_KEY.'=l.'.STATIC::TABLE_EXTERNAL_KEY,STATIC::TABLE_PRIMARY_KEY."=".$current[STATIC::PARENT_FIELD_TABLE]." AND locale ='{$locale}'");
					if(okArray($current)){ 
						$current = $current[0]; 
						$array_name[] = $current[STATIC::NAME_FIELD_TABLE];
					}
					
					
				}
				
				$array_name = array_reverse($array_name);
				$name = '';
				foreach($array_name as $v1){
					$name .= "{$v1} / ";
				}
				$name = trim(preg_replace('/\/ $/','',$name));
				return $name;
				
			}
		}
		return false;
	}

	/**
	 * restituisce il numero di prodotti nella sezione
	 *
	 * @return integer
	 */
	public function getCountProduct(): int{
		$product = Product::prepareQuery()
			->where("product_category_id",$this->id)
			->where('online',1)
			->where('parent_id',null)
			->where('deleted',0)
			->get();
		return count($product);
	}
	


	//metodo che restituisce il percorso di un prodotto
	function breadCrumbs($options = []): string{
		$options_default = array(
			"before_html" => "<span>",
			"after_html" => "</span>",
			"divider_html" => " > ",
		);
		if( okArray($options) ){
			foreach($options as $k => $v){
				$options_default[$k] = $v;
			}
		}
		if( $this->id ){
			$category = self::withId($this->id);
			
			$list[] = array(
				'name' => $category->get('name'),
				'id' => $category->id,
				'parent' => $category->parent_id,
				'url' => $category->getUrl()
			);
			while( $category->parent_id ){
				$category = $category->getParent();
				$list[] = array(
					'name' => $category->get('name'),
					'id' => $category->id,
					'parent' => $category->parent_id,
					'url' => $category->getUrl()
				);

			}
			
			krsort($list);
			$list = array_values($list);
			$list[0]['first'] = 1;
			$list[count($list)-1]['last'] = 1;
			$breadCrumbs = '';
			foreach($list as $v){
				$class = isset($v['last'])?'last':'';
				$class .= isset($v['first'])?' first':'';
				$breadCrumbs .= $options_default['before_html']."<a class='{$class}' href='".$v['url']."'>".$v['name']."</a>".$options_default['after_html'].$options_default['divider_html'];
			}
			$divider_html = $options_default['divider_html'];
			
			$breadCrumbs = preg_replace("/{$divider_html}$/",'',$breadCrumbs);
			
			
			return $breadCrumbs;
		}
		return '';
	}



	function setRelatedCategories($array){
		$this->product_category_related = $array;

	}



	function afterSave(): void{
		parent::afterSave();
		DB::table('product_category_related')->where('product_category_id',$this->id)->delete();
		
		
		if( okarray($this->product_category_related) ){
			foreach($this->product_category_related as $v){
				$toinsert = array(
					'product_category_id' => $this->id,
					'product_category_related' => $v,
				);
				DB::table('product_category_related')->insert($toinsert);
				

			}

		}
		$list = DB::table('products')->whereNull('parent_id')->where('deleted',0)->where('product_category_id',$this->id)->get();
		

		
		if( okArray($list) ){
			foreach($list as $v){
				//$database->insert('product_search_changed',array('id_product' => $v['id']));
			}
		}

	}

	function afterLoad(): void{
		parent::afterLoad();
		$database = Marion::getDB();
		$sections = DB::table('product_category_related')->where('product_category_id',$this->id)->get();
		//$sections = $database->select('*','product_category_related',"product_category_id={$this->id}");
		if( okArray($sections) ){
			foreach($sections as $v){
				$this->product_category_related[] = $v->product_category_related;
			}
		}
	}



	function delete(): void{
		parent::delete();
		DB::table('product_category_related')->where('product_category_id',$this->id)->delete();
		
	}


	
	function getRelatedProducts($limit = 6){
		if( okarray($this->product_category_related) ){
			$where = "(";
			foreach($this->product_category_related as $v ){
				$where .= "{$v},";
			}
			$where = preg_replace('/\,/',')',$where);
			$query = Product::prepareQuery()
				->where('online',1)
				->where('deleted',0)
				->whereExpression("parent_id IS NULL")
				->where('product_category_id',$where,"IN")
				->limit($limit);
			$products = $query->get();
			
			return $products;
		}

	}

	

}





?>