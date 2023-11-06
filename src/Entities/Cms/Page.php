<?php
namespace Marion\Entities\Cms;
use Marion\Core\Base;
use Marion\Core\Marion;
class Page extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'pages'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'pages_langs'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'page_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	

	public $layout;
	public $composed_page_id;
	public $advanced;
	public $id;
	public $theme;
	public $route_id;

	function setLayout($id_layout){
		$this->layout = $id_layout;
	}

	function afterSave(): void{
		parent::afterSave();
		if( $this->advanced && $this->layout){

			
			$database = Marion::getDB();
			
			$toinsert = array(
				'layout_id' => $this->layout,
			);
			if(  !$this->composed_page_id  ){
				$this->composed_page_id = $database->insert('composed_pages',$toinsert);
				$this->save();
			}else{
				$database->update('composed_pages',"id={$this->composed_page_id}",$toinsert);
			}
			
			
		}
	}
	function checkSave(){
		$res = parent::checkSave();
		

		if( $res == 1 ){
			foreach($this->_localeData as $loc => $values){
				$url = $values['url'];
				$query = self::prepareQuery()->where('url',$url)->where('theme',$this->theme)->where('locale',$loc);
				if( $this->id){
					$query->where('id',$this->id,'<>');
				}
				$check = $query->getOne();
				if( is_object($check) ){
					return "url_duplicate";
				}
			}

			return 1;
			

		}else{
			return $res;
		}
	}


	public static function getByUrl($url){
		$query = Page::prepareQuery()
			->where('url',$url)
			->where('locale',$GLOBALS['activelocale']);
		$theme = Marion::getConfig('SETTING_THEMES','theme');
		$query->whereExpression("(theme IS NULL OR theme='0' OR theme='{$theme}')");
		if( !auth('cms') ){
			$query->where('visibility',1);
		}
		$page = $query->getOne();
		return $page;
	}



	function getContent($locale=NULL){
		if( !$locale ){
			$locale = $GLOBALS['activelocale'];
		}

		$cont = $this->get('content');

		Marion::do_action('action_parse_html',array($cont));

		return $cont;


	}

	function getUrl($locale=null){
		return _MARION_BASE_URL_."p/".$this->get('url',$locale);
	}
}

?>