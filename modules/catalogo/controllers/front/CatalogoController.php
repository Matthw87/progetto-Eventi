<?php
use Marion\Core\marion;
use Marion\Controllers\FrontendController;
use Catalogo\{Catalog,Product,Section,Manufacturer,TagProduct};
use Marion\Support\Form\Traits\FormHelper;

class CatalogoController extends FrontendController{
	use FormHelper;
	
		
	private $orderKey = NULL;
	private $orderValue = NULL;

	private $columnsNumber = 3; //disposizione dei prodotti per colonna
	private $perPage = 8; //prodotti da prendere
	private $perPageMobile = 6;
	private $currentPage; //pagina corrente
	
	private $template; //contiene l'oggetto Template
	
	private $pagerTypeList = array(
		'classic',
		'showMoreButton',
		'onScroll' // da fare
	);

	private $pagerType = 'classic';
	private $products_list;



	//OVERRIDE

	public function init($options = Array()){

		parent::init($options);
		
		
		$this->getSettingData();


		$this->getOrderBy();
		$this->getColumnsNumber();
		$this->getPerPage();
		$this->getCurrentPage();
		$this->redirect();


	}
	

	function setMedia(){
		parent::setMedia();	
		$this->loadJS('bxslider');
		$this->loadJS('fancybox');
		//$this->registerCSS('/modules/catalogo/css/metisMenu.css');
		$this->registerJS('modules/catalogo/js/metisMenu.js','head');
		$this->registerJS('modules/catalogo/js/catalog.js','head');
		//$this->registerJS('modules/catalogo/js/eshop.js','head');

		if( $this->getAction() == 'product' ){
			$this->registerCSS('modules/catalogo/css/product_card.css');
			$this->registerJS('modules/catalogo/js/product-fancybox.js','head');
		}else{
			$this->registerJS('plugins/lazyload/dist/lazyload.min.js','end'); // LUCA
			$this->registerJS('modules/catalogo/js/lazyload.js','end'); //LUCA
			$this->registerJS('plugins/jquery.inview.min.js');
		}

	}



	function display(){


		
		$this->addTemplateFunction(
			new \Twig\TwigFunction('url_product_edit', function ($id) {
				return 'backend/index.php?ctrl=ProductAdmin&mod=catalogo&action=edit&id='.$id;
			})
		);

		$this->loadCategories();
		$this->buildTypeView();
		$this->buildSelectOrder();
		$this->buildSelectPerPage();
		$this->loadCssProductBox();
		

		//debugga($_GET);exit;
		
		$this->getData();
		$action = $this->getAction();
		switch($action){
			case 'product':
				$this->output('view_product.htm');
				break;
			case 'brands':
				$this->output('brands.htm');
				break;
			default:
				if( _var('ajax_pager') ){
					ob_start();
					$this->output('list_ajax.htm');
					$html = ob_get_contents();
					ob_end_clean();
					$other_products = 1;
					
					if( count($this->products_list) < $this->perPage ){
						$other_products = 0;
					}

					$risposta = array(
						'result' => 'ok',
						'html' => $html,
						'other_products' => $other_products
					);
					echo json_encode($risposta);
					exit;
				}else{
					$this->output('list.htm');
				}
				break;
		}
		
		
	}



	//OVERRIDE FINE



	

	public function redirect(){
		$url = $_SERVER['REQUEST_URI'];
		$pos1 = strpos($url,'pageID');

		$pos2 = strpos($url,'pagenumber');
		if($pos1 && $pos2 && ($pos2 > $pos1) ){
			if( !preg_match('/pageID=1/',$url) ){
				
				$url = preg_replace('/&pageID=([0-9]+)/','',$url)."&pageID=1";
				header('Location: '.$url);
			}
		}
	}
	

	

	//lettura dei parametri di ordinamento
	public function getOrderBy(){
		$orderKey = _var('orderkey');
		$orderValue = _var('ordervalue');
		$this->setVar('order_selected',$orderValue);
		$this->order_selected = $orderValue;
		if( $orderKey == 'price'){
			if( $orderValue == 'low' ){
				$orderValue = 'ASC';
			}else{
				$orderValue = 'DESC';
			}
		}
		$this->orderKey= $orderKey;
		$this->orderValue = $orderValue;
	}

	
	//lettura del tipo di layout della pagina: 2 colonne, 3 colonne, 4 colonne
	public function getColumnsNumber(){
		$number_view = _var('number_view');
		if( $number_view ){
			$this->columnsNumber = $number_view;
			$_SESSION['number_view_catalog_section'] = $number_view;
			if( $_SESSION['number_view_catalog_section'] && $_SESSION['number_view_catalog_section'] != $number_view ){

				$_SESSION['number_view_catalog_section'] = $number_view;

				
				$new_url = preg_replace('/&pagenumber=([0-9]+)/','',$_SERVER['REQUEST_URI']);
				$new_url = preg_replace('/\?pagenumber=([0-9]+)/','',$new_url);
				//debugga($new_url);exit;
				/*if( !$this->isAjaxRequest() ){
					header('Location:'.$new_url);
				}*/
				
			}
		}else{
			if( isset($_SESSION['number_view_catalog_section']) && $_SESSION['number_view_catalog_section'] ){
				$this->columnsNumber = $_SESSION['number_view_catalog_section'];
			}
		}

		switch($this->columnsNumber){
			case 2:
				$params_tmpl['number_view'] = 2;
				$params_tmpl['type_img'] = 'small';
				$params_tmpl['class_row'] = 'adue';
				break;
			case 3:
				$params_tmpl['number_view'] = 3;
				$params_tmpl['type_img'] = 'small';
				$params_tmpl['class_row'] = 'atre';

				break;
			case 4:
				$params_tmpl['number_view'] = 4;
				$params_tmpl['type_img'] = 'small';
				$params_tmpl['class_row'] = 'aquattro';

				break;
		}
		if( okArray($params_tmpl) ){
			foreach($params_tmpl as $k => $v){
				$this->setVar($k,$v);
			}
		}
		

	}



	
	//metodo che restituisce il numero di pagina corrente
	public function getCurrentPage(){
		$this->currentPage = _var('pageID');
	}

	
	//metodo che restituisce il numero di elementi da mostrare per pagina
	public function getPerPage(){
		$pagenumber = _var('pagenumber');
		if( $pagenumber ){
			$this->perPage = $pagenumber;
		}else{
			 if( !$pagenumber ){
				if( $this->columnsNumber ){
					$this->perPage = $this->columnsNumber*4;
				}

			}
		}

		$this->setVar('page_number_selected',$this->perPage);
	}

	function getSettingData(){
		$conf_catalog = Marion::getConfig('catalog');
		$this->pagerType = $conf_catalog['type_pager_section'];
		$this->columnsNumber = $conf_catalog['type_view'];
	}

	
	



	public function getData(){
		
	
		//prendo il numero di prodotti da mostrare per pagina
		$limit = $this->perPage;

		$offset = 0;
		//prendo l'offset
		if( $this->currentPage ){
			  //offset sulla select dei prodotti
			  $offset = ($this->currentPage-1)*$limit;
		 }


		//vedo se è previsto un ordinamento
		if( $this->orderKey ){
			$orderBy = array(
				$this->orderKey => $this->orderValue,
			);
		}
		
		$list = array();
		$count = 0;

		$formdata = $this->getFormdata();
		
		if( okArray($formdata) ){
			$this->setVar('filtri_attivi',1);
		}


		switch($this->getAction()){
			case 'section':
				//prendo l'id della sezione
				$section_id = _var('section');

				$sezione = Section::withId($section_id);
				
				if( !is_object($sezione) ){
					$this->output('404.htm');
					exit;
					//$this->showError(102);
				}
				
				$this->setVar('section',$section_id);
				$this->setVar('sezione',$sezione);
				$this->breadCrumbs($section_id);


				//verifico se la sezione ha delle sezioni figlie
				$section_children = $this->getSectionChildren($section_id);
				if( count($section_children) > 1 ){
					$section_id = $section_children;
				}
				
				if (Marion::exists_action('acion_filtri_ricerca')){
						
						Marion::do_action('acion_filtri_ricerca',array(&$list,&$count,$limit,$offset,$this->orderKey,$this->orderValue));
				 }else{
				
					$list = Catalog::getProduct(
						array(
							'section'=>$section_id
						),
						$orderBy,
						$limit,
						$offset
					)->toArray();
					$count = Catalog::getCountProducts(
						array(
							'section'=>$section_id
						)
					);
					
				}
				$meta_title = $sezione->get('name');
				if( trim($sezione->get('metaTitle')) ){
					$meta_title = $sezione->get('metaTitle');
				}

				$this->setVar('meta_title',$meta_title);
				$this->setVar('meta_description',$sezione->get('metaDescription'));
				$this->setVar('title',$sezione->get('name'));
				break;
			case 'search':
				//parole di ricerca
				$words = trim(_var('value'));
				$list = Catalog::searchProducts($words,$orderBy,$limit,$offset)->toArray();
				$count = Catalog::getCountSearchProducts($words);
				
				$this->setVar('title',$words);
				break;
			case 'tag':

				$tag = _var('tag');
				$tag = TagProduct::prepareQuery()->where('label',$tag)->getOne();
				if( !is_object($tag) ){
					$this->output('404.htm');
					exit;
				}
				if (Marion::exists_action('acion_filtri_ricerca')){
						
						Marion::do_action('acion_filtri_ricerca',array(&$list,&$count,$limit,$offset,$this->orderKey,$this->orderValue));
				}else{
					//prendo gli id dei prodotti con questo tag
					$ids = $tag->getProductIds();
					

					$list = Catalog::getProduct(
						array(
							'id'=>$ids
						),
						$orderBy,
						$limit,
						$offset
					)->toArray();

					$count = Catalog::getCountProducts(
						array(
								'id'=>$ids
							)
					);
				}
				$meta_title = $tag->get('name');
				$this->setVar('meta_title',$meta_title);
				$this->setVar('title',$tag->get('name'));
				break;
			case 'product':
				//prendo l'id del prodotto
				$qnt = (int)_var('qnt');
				if( !$qnt ) $qnt = 1;
				$this->setVar('qnt',$qnt);
				$product_id = _var('product');


				$product = Catalog::getProduct(
					array(
						'id'=>$product_id
					)
				)->toArray();
				$prodotto = $product[0];
				
				if( is_object($prodotto) ){
					$this->breadCrumbs($prodotto->section);
					if( Marion::getConfig('catalog','enable_social_link_card_product') ){
						$this->setVar('enable_social_link_card_product',1);
						$this->buildSocialShareProduct($prodotto);
					}
					if( !$prodotto->visibility ){
						if( !Marion::auth('admin')){
							$this->template->errore_generico('409',__('product_not_found'));
						}
					}
					$qnt = _var('qnt');
					if( !(int)$qnt ){
						$qnt = 1;
					}
					$this->setVar('qnt',$qnt);
					$this->setVar('prodotto',$prodotto);
					

					if($prodotto->isConfigurable()){
	 
						$attributeSet = $prodotto->getAttributeSet();
						if( is_object($attributeSet) ){
							$this->setVar('options_product',$prodotto->getAttributesView());
						}else{
							$children = $prodotto->getChildren();
							$options_product[0] = __('seleziona');
							foreach($children as $v){
								$options_product[$v->id] = $v->get('name');
							}
							$this->setVar('options_product_without_attributes',$options_product);
						}
						
					  }
				}else{
					$this->output('404.htm');
					exit;
				}

				$show_popup = _var('ajax');
				
				if( $show_popup ){
					$this->setVar('prodotto_popup',1);
					//$this->settingActions[$this->action]['template'] = 'vedi_prodotto_popup.htm';
				}
				break;
			case 'brand':
				//prendo l'id del brand
				$brand_id = _var('id');
				$brand = Manufacturer::withId($brand_id);
				if( !is_object($brand) ){
					//GESTISCO L'ERRORE
					//$this->showError(104);
				}
				$this->template->brand = $brand;
				

				if (Marion::exists_action('acion_filtri_ricerca')){
						
						Marion::do_action('acion_filtri_ricerca',array(&$list,&$count,$limit,$offset,$this->orderKey,$this->orderValue));
				}else{
				
				
					$list = Catalog::getProduct(
						array(
							'manufacturer'=>$brand_id
						),
						$orderBy,
						$limit,
						$offset
					)->toArray();
					$count = Catalog::getCountProducts(
						array(
								'manufacturer'=>$brand_id
							)
					);
				}
				$this->setVar('title',$brand->get('name'));
				break;
			case 'catalog':
				

				$list = Catalog::getProduct(
					array(
						'offer'=>1
					),
					$orderBy,
					$limit,
					$offset
				)->toArray();
				$count = Catalog::getCountProducts(
					array(
							'offer'=>1
						)
				);
				
				
				$this->setVar('title','IN EVIDENZA');
				break;
			case 'brands':
				$brands = Manufacturer::prepareQuery()->where('visibility',1)->get();

				
				$this->setVar('brands',$brands);
				break;
			

		}

		
		$this->products_list = $list;
		if( okArray($list) ){
			$this->setVar('products',$list);
			$this->setVar('prodotti',$list);
			$this->setVar('total_products',$count);
			$this->getPager($count);
		}
		

	}

	public function getPager($count){
		

		

		switch($this->pagerType){
			case 'classic':

				$params = PagerConfig::withLabel('section')->getParams();
				$params['totalItems'] = $count;
				$params['perPage'] = $this->perPage;
				$params['curPageLinkClassName'] = 'pager_selected';
				require_once 'Pager.php';
				$pager = &Pager::factory($params);
				
				
							
				//prendo i link del pager
				$links = $pager->getLinks();
				
				$this->setVar('links',$links);
			break;
			case 'onScroll':
				$this->setVar('load_on_scroll',1);
			case 'showMoreButton':
				if( $count > $this->perPage*($this->currentPage +1) ){
					$this->setVar('other_products_pager',true);
				}
				
				break;
		}
			
		
		
	}


	function getSectionChildren($id_section){
		if( $id_section ){
			$database = _obj('Database');
			$list[$id_section] = $id_section;
			$list_view = $list;
			$tot_children = 0;
			
			while(count($list_view) != 0 ){
				$temp_array = $list_view;
				foreach($temp_array as $k => $v){
					
					$children = $database->select('id','section',"parent={$v}");
					if( okArray($children) ){
						foreach($children as $t){
							$list_view[$t['id']] = $t['id'];
							$list[$t['id']] = $t['id'];
						}
					}
					unset($list_view[$k]);
					
				}
			}
		}
		
		return $list;
	}




	function loadCategories(){
	
		$this->setVar('itemActive',_var('section')); 
		$tree = Catalog::getSectionTree();
		$this->setVar('menuside',$tree);
		
	
	}

	function buildTypeView(){
		$num = $this->columnsNumber;
		if( Marion::getConfig('catalog','enable_select_view_product') ){
			$this->setVar('type_view_number',$num);
			
			if( $_SERVER['REDIRECT_QUERY_STRING'] ){
				$return_location = $_SERVER['SCRIPT_NAME']."?".$_SERVER['REDIRECT_QUERY_STRING'];
			}elseif( $_SERVER['QUERY_STRING']){
				$return_location = $_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING'];
			}else{
				$return_location = $_SERVER['SCRIPT_NAME'];
			}
			$this->setVar('return_location',$return_location);
			
			$return_location = preg_replace('/&number_view=([0-9]+)/','',$return_location);
			$return_location_without_pageID = preg_replace('/&pageID=([0-9]+)/','',$return_location);
			
			if( preg_match('/\?/',$return_location) ){
				$return_location .="&";
				$return_location_without_pageID .= '&';
			}else{
				$return_location .="?";
				$return_location_without_pageID .= "?";
			}
			$this->setVar('return_locarion_view',$return_location);
			$this->setVar('return_locarion_view_without_pageID',$return_location_without_pageID);
		
		}
	}


	

	function buildSelectOrder(){
		if( Marion::getConfig('catalog','enable_select_order_product') ){
			$selected = $this->order_selected;
			$select_ordina_per[0] = _translate('order_by','catalogo');
			//$etichette_ordine = array('sku','name','low','hight');
			$etichette_ordine = unserialize(Marion::getConfig('catalog','parameters_select_order_product'));
			foreach($etichette_ordine as $v){

				
				$select_ordina_per[$v] = _translate('order_by_'.$v,'catalogo');

			}
			
			$this->setVar('select_ordina_per',$select_ordina_per);

			$this->setVar('selected_order',$selected);
			
		}

	}
	

	function buildSelectPerPage(){
		if( Marion::getConfig('catalog','enable_select_number_product_page') ){
			
			$multiple = 3;
			if( $_SESSION['number_view_catalog_section'] ){
				$multiple = $_SESSION['number_view_catalog_section'];
			}
			for( $k=2;$k<=5;$k++){
				$etichette_ordine[$k-1] = $k*$multiple;
			}

			//$etichette_ordine = array('9','12','18','30');
			foreach($etichette_ordine as $v){
				$select_ordina_per[$v] = $v;

			}
			
			
			$this->setVar('select_num_page',$select_ordina_per);
			$this->setVar('selected_num_page',$selected);
			
		}

	}

	//metodo che restituisce il percorso di un prodotto
	function breadCrumbs($section){
		
		$options_default = array(
			"before_html" => "<span>",
			"after_html" => "</span>",
			"divider_html" => " > ",

		);

		/*foreach($options as $k => $v){
			if( $options_default[$k] ){
				$options_default[$k] = $v;
			}
		}*/
		if( $section ){
			
			$section = Section::withId($section);
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
				
				$this->setVar('breadCrumbs_list',$list);
				return true;
			} else {
				return false;
			}
		}
		return false;
	}

	



	function buildSocialShareProduct($product){
		if( is_object($product) ){
			$title = $product->get('name');
		}
		
		$return_location = urlencode("http://" . $_SERVER['SERVER_NAME'] .$this->getUrlCurrent());
		
		$title = urlencode($title);
		$this->setVar('share_url_mail',"mailto:?subject={$title}&body={$return_location}");
		$this->setVar('share_url_facebook',"http://www.facebook.com/sharer/sharer.php?u={$return_location}&title={$title}");
		$this->setVar('share_url_pinterest',"http://pinterest.com/pin/create/button/?url={$return_location}&description={$title}");
		$this->setVar('share_url_google_plus',"https://plus.google.com/share?url={$return_location}");
		$this->setVar('share_url_twitter',"http://twitter.com/share?text={$title}&url={$return_location}");
		$this->setVar('share_url_linkedin',"https://www.linkedin.com/cws/share?url={$return_location}&title={$title}");
	}

	function getUrlsSocial($title,$oggetto=NULL,$link=NULL){
		
		

	}




	function loadCssProductBox(){
		

		$add_wish = Marion::getConfig('catalog','enable_add_wishlist');
		$add_cart = Marion::getConfig('catalog','enable_add_cart_button');
		$qty = Marion::getConfig('catalog','enable_quantity_add_cart');
		
		
		$this->setVar('enable_quantity_add_cart',Marion::getConfig('catalog','enable_quantity_add_cart'));
		$this->setVar('enable_add_cart_button',Marion::getConfig('catalog','enable_add_cart_button'));
		$this->setVar('enable_wishlist',Marion::getConfig('catalog','enable_add_wishlist'));
		
		$class_qty = '';
		$class_add_wish = '';
		$class_add_cart = '';
		if( $add_wish && $add_cart && $qty ){
			$class_add_cart = 'addcart';
			$class_add_wish = 'addwish';
			$class_qty = 'qty_prod';

		}elseif( $add_wish && $add_cart ){
			$class_add_cart = 'cart-wish';
			$class_add_wish = 'wish-cart';
		}elseif( $qty && $add_cart ){
			$class_add_cart = 'cart-qty';
			$class_qty = 'qty-cart';

		}elseif( $add_wish){
			$class_add_wish = 'wish-full';
		}elseif( $add_cart){
			$class_add_cart = 'cart-full';
		}

		
		$this->setVar('class_add_cart',$class_add_cart);
		$this->setVar('class_add_wish',$class_add_wish);
		$this->setVar('class_qty',$class_qty);
		
	}



	public function ajax(){
		
		//prendo il numero di prodotti da mostrare per pagina
		$limit = $this->perPage;
		

		//prendo l'offset
		if( $this->currentPage ){
			  //offset sulla select dei prodotti
			  $offset = ($this->currentPage-1)*$limit;
		  }

		//vedo se è previsto un ordinamento
		if( $this->orderKey ){
			$orderBy = array(
				$this->orderKey => $this->orderValue,
			);
		}
	
		switch($this->getAction()){
			case 'getImagesProduct':

				$formdata = $this->getFormdata();
				
				$popup = (int)_var('prodotto_popup');
				$product = Product::withId($formdata['product']);
				if( is_object($product) ){
					unset($formdata['product']);
					unset($formdata['quantity']);
					$children = $product->getChildrendWithAttributes($formdata);
					
					if( okArray($children) ){
						$child= $children[0];
						
						$images = $child->images;
						
						if( okArray($images) ){
							if( $popup ){
								$this->setVar('popup',$popup);
							}
							$this->setVar('prodotto',$child);
							$this->setVar('parent',$product);
							ob_start();
							$this->output('partials/gallery_product.htm');
							$html = ob_get_contents();
							ob_end_clean();
							
							$risposta = array(
								'result' => 'ok',
								'html' => $html
							);

							if( count($children) == 1 ){
								ob_start();
								$this->output('partials/product_card_price.htm');
								$html = ob_get_contents();
								ob_end_clean();
								
								$risposta['price_box'] = $html;
							}
							echo json_encode($risposta);
							exit;
							
						}
						
					}
				}
				$risposta = array(
					'result' => 'nak'
				);
				break;
			case 'getNextAttributeValues':

				$label = _var('attribute');
				$formdata = _formdata();
				foreach($formdata as $k => $v){
					if( !$v ){
						unset($formdata[$k]);
					}
				}
				unset($formdata[$label]);
				$productId = $formdata['product'];
				

				$product = Product::withId($productId);
				
				if(is_object($product)){

					//prendo l'insieme di attributi
					$attributeSet = AttributeSet::withId($product->attributeSet);
					if($attributeSet){
						$attributeSelect = $attributeSet->getAttributeWithValues(); 
					}

					if( !$label ){
						
						$attributes = array_values($attributeSet->getAttributes());
						
						if( $attributes[0]['attribute'] ){
							$type = $attributes[0]['type'];
							$attribute = Attribute::withId($attributes[0]['attribute']);
							if( is_object($attribute) ){
								

								$children = $product->getChildren();
								foreach($children as $v){
									$tmp_attr = $v->getAttributes();
									if( $product->centralized_stock ){

										$stock_attr[$tmp_attr[$attribute->label]] = $product->getInventory();
									}else{
										$stock_attr[$tmp_attr[$attribute->label]] += $v->getInventory();
									}
									
								}
							}
							
							if( okArray($stock_attr) ){
								foreach( $stock_attr as $k => $v ){
									if( $v > 0 ){
										$toreturn[$k] = $k;
									}
								}
							}
							$risposta = array(
									'result'=>'ok',
									'options' => $toreturn,
									'attribute' => $attribute->label,
									'type' => $type
							);
						}
						
						
					}else{
						$children = $product->getChildrendWithAttributes($formdata);

						
						$selected = array();
						if( $product->centralized_stock ){
							$parent_stock = $product->getInventory();
						}


						foreach($children as $child){
							$values = $child->getAttributes();
							if( $product->centralized_stock ){
								if( $parent_stock > 0){
									$selected[] = $values[$label];
								}
							}else{
								
								if( $child->getInventory() > 0){
									$selected[] = $values[$label];
								}
							}
						}
						$selected = array_unique($selected);

						$toreturn[0] = $attributeSelect[$label][0];
						foreach($attributeSelect[$label] as $k => $v){
							if( in_array($k,$selected)){
								$toreturn[$k] = $v;
							}
						}
						$risposta = array(
								'result'=>'ok',
								'options' => $toreturn 
						);
					}
				}
				break;
			
		}

		if( isset($list) ){
			
			$page = "product_list_sidebar_ajax.htm";
			$this->template->prodotti = $list;
			//debugga($data);exit;
			ob_start();
			$this->template->output($page);
			$html = ob_get_contents();
			ob_end_clean();
			
			$risposta = array(
				'result' => 'ok',
				'html' => utf8_encode($html),

			);

			$other_products = $count - $this->perPage*$this->currentPage;
			

			//LATO DESKTOP
			if( $other_products > 0 ){
				$risposta['other_products'] = 1;	
			}else{
				$risposta['other_products'] = 0;
			}
			//LATO MOBILE
			if( count($list) < $this->perPageMobile ){
				$risposta['other'] = 0;	
			}else{
				$risposta['other'] = 1;	
			}
			
			
		}
		echo json_encode($risposta);
		$this->closeDB();
		exit;

	}
	
}


?>