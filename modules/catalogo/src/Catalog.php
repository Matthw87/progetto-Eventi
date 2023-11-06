<?php
namespace Catalogo;
use Marion\Core\Marion;
use Illuminate\Database\Capsule\Manager as DB;
//use Shop\Eshop;
class Catalog{

	/**
	 * Search products
	 *
	 * @param string $search
	 * @param array $filters
	 * @param array $order
	 * @param  int $limit
	 * @param int $offset
	 * @return array
	 */
	public static function searchProducts(string $search, $filters=array(),$order=array(),int $limit=NULL, int $offset=NULL): array{
		$search_filter =  array(
			'search_value' => $search
		);
		$filters = array_merge($search_filter,$filters);
		return self::getProducts($filters,$order,$limit,$offset);
	}

	/**
	 * Get products
	 *
	 * @param array $filters
	 * @param array $order
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public static function getProducts($filters=array(),$order=array(),int $limit=NULL,int $offset=NULL): array{
		//filtri di default
		$default_filters = array(
			'products.online' => 1,
			'products.deleted' => 0
		);
		


		$order_default = array(
			'products.order_view' => 'ASC',
		);

		

		$in_array = array();
		if(okArray($filters)){
			foreach($filters as $k => $v){
				$default_filters[$k] = $v;
			}
			
			
			foreach($default_filters as $k => $v){
				if( okArray($v) ){
					unset($default_filters[$k]);
					$in_array[$k] = $v;
				}
			}
		}

		if(okArray($order)){
			foreach($order as $k => $v){
				$order_default_tmp[$k] = $v;
			}
			$order_default = array_merge($order_default_tmp,$order_default);
		}

		$query = DB::table('products')
			->whereNull('parent_id')
			->leftJoin('product_langs',function($join){
				$join->on('products.id','=','product_langs.product_id');
				$join->where('lang',_MARION_LANG_);
		});

		

		
		if( okArray($in_array) ){
			foreach($in_array as $field => $set){
				$query->whereIn($field,$set);
			}
		}

		if( okArray($default_filters) ){
			foreach($default_filters as $field => $set){
				switch($field){
					case 'search_value':
						
						$search_ids = DB::table('product_search_index')
							->where('lang',_MARION_LANG_)
							->where('product_value','like',"%{$set}%")
							->pluck('product_id')->toArray();
						$query->whereIn('products.id',$search_ids);
						break;
					case 'product_category_id':
						$category_ids = self::getSubcategoriesByCategoryId($set);
						$query->whereIn($field,$category_ids);
						break;
					default:
						$query->where($field,$set);
						break;
				}
				
			}
		}
		

		if( okArray($order_default) ){
			foreach($order_default as $field => $direction){
				$query->orderBy($field,$direction);
			}
		}
		$select = [
			'products.*',
			'product_langs.*',
		];
		
		$query_count = clone $query;

		Marion::do_action('catalogo_select_query_override',array(&$query,&$select));
		
		if( $limit ){
			$query->limit($limit);
		}
		if( $offset ){
			$query->offset($offset);
		}

		$_products = $query->select($select)->get()->toArray();
		$products = [];
		if( okArray($_products)){
			foreach($_products as $p){
				$product = Product::withData((array)$p);
				$products[] = $product;
			}
		}
		
		return [$products, $query_count->count()];
		
	}



	public static function getSection($filter=array(),$order=array()){
		//filtri di default
		$default = array(
			'visible' => 1,
			'parent' => 0,
			'deleted' => 0,
		);
		
		$order_default = array(
			'orderView' => 'ASC',
		);

		if(okArray($filter)){
			foreach($filter as $k => $v){
				$default[$k] = $v;
			}
		}

		if(okArray($order)){
			foreach($order as $k => $v){
				$order_default[$k] = $v;
			}
		}
		

		$query = Category::prepareQuery();
		$query->whereMore($default)->orderByMore($order_default);

		return $query->getCollection();
		
	}

	public static function getSectionTree($all=false){
		//filtri di default
		
		if( !$all ){
			$default = array(
				'online' => 1,
			);
		}
		
		$order_default = array(
			'order_view' => 'ASC',
		);

		$query = Category::prepareQuery();
		$query->whereMore($default)->orderByMore($order_default);
		$section = $query->get();
		if(okArray($section)){
			$tree = Category::buildtree($section);
			//ordino le sezioni di primo livello
			uasort($tree,function($a,$b){
				if ($a->order_view == $b->order_view) return 0;
				return ($a->order_view < $b->order_view)?-1:1;
			});
			//ordino le sezioni di secondo livello
			foreach($tree as $v){
				if( okArray($v->children) ){
					uasort($v->children,function($a,$b){
						if ($a->order_view == $b->order_view) return 0;
						return ($a->order_view < $b->order_view)?-1:1;
					});
					
					foreach($v->children as $v1){
						//ordino le sezioni di terzo livello
						if( okArray($v1->children) ){
							uasort($v1->children,function($a,$b){
								if ($a->order_view == $b->order_view) return 0;
								return ($a->order_view < $b->order_view)?-1:1;
							});
						}
					}

				}
			}
			return $tree;
		}
		return false;
		
	}

	

	public static function orderProductByPrice(&$products,$orderType='low'){
		
		if( $orderType == 'low'){

			uasort($products,function($a,$b){
				if ($a->getPriceValue() == $b->getPriceValue()) return 0;
				return ($a->getPriceValue() < $b->getPriceValue())?-1:1;
			});
			
		}else{
			
			$res = uasort($products,function($a,$b){
				if ($a->getPriceValue() == $b->getPriceValue()) return 0;
				return ($a->getPriceValue() > $b->getPriceValue())?-1:1;
			});
			
			
			

		}
	}


	public static function reset(){
		
		$products = Product::prepareQuery()->get();
		foreach($products as $prod){
			$prod->deleteChildren();
			$prod->delete();
		}
		$database = Marion::getDB();
		$database->execute("ALTER TABLE product AUTO_INCREMENT = 1");
		$database->execute("ALTER TABLE price AUTO_INCREMENT = 1");
	}



	static function getSubcategoriesByCategoryId(int $id): array{
		$categories = [$id];
		$subcategory_ids = DB::table('product_categories')->where('parent_id',$id)->pluck('id')->toArray();
		if( okArray($subcategory_ids) ){
			$categories = array_merge($subcategory_ids,$categories);
		}
		return $categories;
	}


}





?>