<?php
use Marion\Controllers\FrontendController;
use Catalogo\{Catalog, Category, Tag};
use JasonGrimes\Paginator;
use Marion\Core\Marion;

class ProductListController extends FrontendController{

    /**
     * chiave ordinamento lista
     *
     * @var string|null
     */
    protected string|null $orderKey = NULL;
	
    /**
     * tipologia ordinamento lista: DESC, ASC
     *
     * @var string|null
     */
    protected string|null $orderValue = NULL;


    protected string $pagerType = 'onScroll';

    /**
     * disposizione dei prodotti per colonna
     *
     * @var integer
     */
	protected int $columnsNumber = 3; 

    /**
     * prodotti per pagina
     *
     * @var integer
     */
	protected int $perPage = 8; 


    /**
     * campo per il quale ordinare la lista
     *
     * @var string
     */
    protected string $order_selected;

    /**
     * pagina corrente
     *
     * @var integer
     */
	protected int $currentPage; 


    public function brand($id){

    }

    public function search($search){
       
        [$limit,$offset,$orderBy] = $this->getListData();
        [$list, $count] = Catalog::searchProducts(
            $search,
            [],
            $orderBy,
            $limit,
            $offset
        );
       
        $meta_title = $search;
        $this->setVar('meta_title',$meta_title);
        $this->setVar('title',$search);
        $this->displayProductList($list,$count);
    }

    /**
     * Display tag page
     *
     * @param string $tag
     * @return void
     */
    public function tag(string $tag): void{
        [$limit,$offset,$orderBy] = $this->getListData();
        
       
        $tag = Tag::prepareQuery()->where('label',$tag)->getOne();
        if( !$tag ){
            header('Location: '._MARION_BASE_URL_.'p/404');
            exit;
        }
        $ids = $tag->getProductIds();
        
        [$list, $count] = Catalog::getProducts(
            array(
                'id'=>$ids
            ),
            $orderBy,
            $limit,
            $offset
        );

       
        
        $meta_title = $tag->get('name');
        $this->setVar('meta_title',$meta_title);
        $this->setVar('title',$tag->get('name'));
        $this->displayProductList($list,$count);
    }

    /**
     * display category page
     *
     * @param int $id
     * @return void
     */
    public function category(int $id): void{
        
        [$limit,$offset,$orderBy] = $this->getListData($id);
        $category = Category::withId($id);
        if( !$category ){
            header('Location: '._MARION_BASE_URL_.'p/404');
            exit;
        }
       
        [$list, $count] = Catalog::getProducts(
            array(
                'product_category_id' => $id
            ),
            $orderBy,
            $limit,
            $offset
        );
    
        $meta_title = $category->get('name');
        if( trim($category->get('meta_title')) ){
            $meta_title = $category->get('meta_title');
        }
        
        $this->setVar('category',$category);
        $this->setVar('meta_title',$meta_title);
        $this->setVar('meta_description',$category->get('meta_description'));
        $this->setVar('title',$category->get('name'));

        $this->displayProductList($list,$count);
		
    }


        
    function setMedia()
    {
        parent::setMedia();	
		$this->loadJS('bxslider');
		$this->loadJS('fancybox');
		//$this->registerCSS('/modules/catalogo/css/metisMenu.css');
		$this->registerJS(_MARION_BASE_URL_.'modules/catalogo/assets/js/metisMenu.js','head');
		$this->registerJS(_MARION_BASE_URL_.'modules/catalogo/assets/js/catalog.js','head');
        $this->registerJS(_MARION_BASE_URL_.'assets/plugins/lazyload/dist/lazyload.min.js','end'); // LUCA
		$this->registerJS(_MARION_BASE_URL_.'modules/catalogo/assets/js/lazyload.js','end'); //LUCA
		$this->registerJS(_MARION_BASE_URL_.'assets/plugins/jquery.inview.min.js');
       
    }


    protected function displayProductList(array $list,int $count): void{
        if( okArray($list) ){
            $this->setVar('products',$list);
            $this->setVar('prodotti',$list);
            $this->setVar('total_products',$count);
        }
        $this->getPager($count);
        if( _var('ajax_pager') ){
            ob_start();
            $this->output('@catalogo/list_ajax.htm');
            $html = ob_get_contents();
            ob_end_clean();
            $other_products = 1;
            
            if( count($list) < $this->perPage ){
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
            $this->output('@catalogo/list.htm');
        }
        
        
        
    }


    protected function getListData(int|null $category=null): array{
        if( $category ){
            $this->setVar('itemActive',$category); 
        }
        
		$tree = Catalog::getSectionTree();
		$this->setVar('menuside',$tree);


        $this->getSettingData();
		$this->getOrderBy();
		$this->getColumnsNumber();
		$this->getPerPage();
		$this->getCurrentPage();

        $this->buildSelectOrder();
        $this->buildTypeView();


        $limit = $this->perPage;
        
		$offset = 0;
		//prendo l'offset
		if( $this->currentPage ){
			  //offset sulla select dei prodotti
			  $offset = ($this->currentPage-1)*$limit;
		 }

        $orderBy = null;
		//vedo se è previsto un ordinamento
		if( $this->orderKey ){
			$orderBy = array(
				$this->orderKey => $this->orderValue,
			);
		}

        return [$limit, $offset, $orderBy];
    }


    /**
     * lettura dei parametri di ordinamento
     *
     * @return void
     */
	protected function getOrderBy(): void{
		$orderKey = _var('orderkey');
		$orderValue = _var('ordervalue');
		$this->setVar('order_selected',$orderKey);
		$this->order_selected = $orderKey;
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


    /**
     * lettura del tipo di layout della pagina: 2 colonne, 3 colonne, 4 colonne
     *
     * @return void
     */
	protected function getColumnsNumber(): void{
		$number_view = _var('number_view');
        $params_tmpl = [];
		if( $number_view ){

            if(!in_array($number_view,[2,3,4])) return;

			$this->columnsNumber = $number_view;
			$_SESSION['number_view_catalog_section'] = $number_view;
			if( $_SESSION['number_view_catalog_section'] && $_SESSION['number_view_catalog_section'] != $number_view ){
				$_SESSION['number_view_catalog_section'] = $number_view;				
				$new_url = preg_replace('/&pagenumber=([0-9]+)/','',$_SERVER['REQUEST_URI']);
				$new_url = preg_replace('/\?pagenumber=([0-9]+)/','',$new_url);

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


    /**
     * metodo che restituisce il numero di pagina corrente
     *
     * @return void
     */
	protected function getCurrentPage(): void{
		$this->currentPage = _var('page');
	}

	
	/**
     * metodo che restituisce il numero di elementi da mostrare per pagina
     *
     * @return void
     */
	protected function getPerPage(): void{
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

    /**
     * get setting data
     *
     * @return void
     */
    protected function getSettingData(): void{
		$conf_catalog = Marion::getConfig('catalogo_setting');
        if( $conf_catalog ){
            
            if( isset($conf_catalog['type_pager_section']) ){
                $this->pagerType = $conf_catalog['type_pager_section'];
            }

            if( isset($conf_catalog['type_view']) ){
                $this->columnsNumber = $conf_catalog['type_view'];
            }
            
		   
        }
		
	}


    /**
     * set Pager
     *
     * @param integer $count
     * @return void
     */
    protected function getPager(int $count = 0): void{
       
		switch($this->pagerType){
			case 'classic':
                $url = $_SERVER['REQUEST_URI'];
                $url = preg_replace('/&page=([0-9]+)/','',$url);
                $url = preg_replace('/\?page=([0-9]+)/','',$url);
                if( preg_match('/\?/',$url) ){
                    $url .= "&";
                }else{
                    $url .= "?";
                }
                $paginator = new Paginator($count, $this->perPage, $this->currentPage, $url."page=(:num)");
				
                
                $this->setVar('_paginator',$paginator);
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


    protected function buildSelectOrder(): void{
		if( Marion::getConfig('catalogo_setting','enable_select_order_product') ){
			$selected = $this->order_selected;
			$select_order_by = [];
            $select_order_by[0] = _translate('product_list.order_by','catalogo');
			//$etichette_ordine = array('sku','name','low','hight');
			$etichette_ordine = unserialize(Marion::getConfig('catalogo_setting','parameters_select_order_product'));
			
            foreach($etichette_ordine as $v){	
				$select_order_by[$v] = _translate('product_list.order_by_param.'.$v,'catalogo');

			}
			$this->setVar('select_order_by',$select_order_by);
			$this->setVar('selected_order',$selected);
			
		}
	}

    protected function buildTypeView(): void{
		$num = $this->columnsNumber;
        
		if( Marion::getConfig('catalogo_setting','enable_select_view_product') ){
          
			$this->setVar('type_view_number',$num);
			
			
            $return_location = $_SERVER['REQUEST_URI'];
           
			$this->setVar('return_location',$return_location);
			
			$return_location = preg_replace('/&number_view=([0-9]+)/','',$return_location);
            $return_location = preg_replace('/\?number_view=([0-9]+)/','',$return_location);
           
			$return_location_without_pageID = preg_replace('/&page=([0-9]+)/','',$return_location);
			
			if( preg_match('/\?/',$return_location) ){
				$return_location .="&";
				$return_location_without_pageID .= '&';
			}else{
				$return_location .="?";
				$return_location_without_pageID .= "?";
			}
            
			$this->setVar('return_location_view',$return_location);
			$this->setVar('return_location_view_without_pageID',$return_location_without_pageID);
		
		}
	}
}