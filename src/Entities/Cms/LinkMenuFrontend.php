<?php
namespace Marion\Entities\Cms;
use Marion\Core\Base;
use Marion\Core\Marion;
use Marion\Entities\Cms\Interfaces\MenuItemFrontendInterface;
class LinkMenuFrontend extends Base{
	
	/************************************* COSTANTI ***************************************************/

	const TABLE = 'link_menu_frontends'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'link_menu_frontends_langs'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'link_menu_frontend_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = 'parent'; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	
	public $static_url;
	public $id_url_page;
	public $url_type;
	public $url;

	public static $_registred_classes = array();


	function beforeSave(): void{
		parent::beforeSave();
		

		if( !$this->static_url ){
			foreach($this->_localeData as $loc => $v){
				$this->_localeData[$loc]['url_dinamic'] = $this->getUrlDinamic($loc);
			}		
		}
		

	
	}

	function afterLoad(): void{
		parent::afterLoad();
		if( $this->id_url_page == 'menuCategories'){
			//$tree = Catalog::getSectionTree();		
			//$this->menu = $tree;
		}
	}


	function getUrl(){
		if( !$this->static_url ){
			
			if( $this->id_url_page ){
			
				return  $this->get('url_dinamic');
			}
		}else{
			return $this->url;
		}
	}

	
	function getUrlDinamic($locale=NULL){
		if( !$locale ) $locale = Marion::getConfig('locale','default');
		
		
		$url = '';
		$type = $this->url_type;
		if( class_exists($type) ){

			$url = $type::getUrl(array('locale' => $locale,'value' => $this->id_url_page));
		}

		return $url;
	}

	public static function getTree($admin=false){
		if( !$admin ){
			$default = array(
				'visibility' => 1,
			);
		}
		
		$order_default = array(
			'orderView' => 'ASC',
		);

		$query = LinkMenuFrontend::prepareQuery();
		$query->whereMore($default)->orderByMore($order_default);
		$links = $query->get();
		
		if(okArray($links)){
			$tree = LinkMenuFrontend::buildtree($links);
			uasort($tree,function($a,$b){
				if ($a->orderView == $b->orderView) return 0;
				return ($a->orderView < $b->orderView)?-1:1;	
			});
			foreach($tree as $k => $v){
				if( okArray($v->children) ){
					uasort($v->children,function($a,$b){
						if ($a->orderView == $b->orderView) return 0;
						return ($a->orderView < $b->orderView)?-1:1;	
					});
				}
			}
			return $tree;
		}
		return false;
	}

		
	public static function listGroupPages(){
		$list_group = array();
		foreach(self::$_registred_classes as $v){
			
			if(class_exists($v)){
				$list_group[$v] = $v::getGroupName();
			}
		
		}
		return $list_group;
	}



	public static function listPages($type = 'all'){
		
		$list_url = array();
		//debugga(self::$_registred_classes);exit;
		switch($type){

			case 'all':
				if( okArray(self::$_registred_classes) ){
					foreach(self::$_registred_classes as $type){
						$tmp = $type::getPages();
						if( okArray($tmp) ){
							foreach($tmp as $k => $v){
								$list_url[$k] = $v;
							}
						}
					
					}
				}

				break;
			default:
				if(class_exists($type)){
					$list_url = $type::getPages();
				}

				break;
		}
		

		
		return $list_url;
		

	}


	public static function registerItem($string=''){
		self::$_registred_classes[] = $string;
	}





	
}



?>