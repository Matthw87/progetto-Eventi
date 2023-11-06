<?php
use Marion\Core\Marion;
use Marion\Controllers\FrontendController;
use Catalogo\{Catalog,Product,Section,Manufacturer,TagProduct, Template};
class ProductController extends FrontendController{


    function setMedia(){
		parent::setMedia();	
		$this->loadJS('bxslider');
		$this->loadJS('fancybox');
		
		//$this->registerCSS('/modules/catalogo/css/metisMenu.css');
		$this->registerJS(_MARION_BASE_URL_.'modules/catalogo/assets/js/metisMenu.js','head');
		$this->registerJS(_MARION_BASE_URL_.'modules/catalogo/assets/js/catalog.js','head');
		//$this->registerJS('modules/catalogo/js/eshop.js','head');

		
		$this->registerCSS(_MARION_BASE_URL_.'modules/catalogo/assets/css/product_card.css');
		$this->registerJS(_MARION_BASE_URL_.'modules/catalogo/assets/js/product-fancybox.js','head');
	}


    function init($options = array())
    {
        parent::init($options);
        $this->addTemplateFunction(
			new \Twig\TwigFunction('url_product_edit', function ($id) {
				return 'backend/index.php?ctrl=Product&mod=catalogo&action=edit&id='.$id;
			})
		);
    }


    public function getNextAttributes(int $productId, int $attribute_selected){
      
        $formdata = _formdata();
        
        foreach($formdata as $k => $v){
            if( !$v ){
                unset($formdata[$k]);
            }
        }
        unset($formdata[$attribute_selected]);
        $product = Product::withId($productId);
        
        if(is_object($product)){
          
            //prendo l'insieme di attributi
            $template = Template::withId($product->product_template_id);
            
            
            if($template){
                $attributeSelect = $template->getAttributeWithValues(); 
            }
           
            $children = $product->getChildrendWithAttributes($formdata);
            $available_options = array();

            foreach($children as $child){
                $values = $child->getAttributes(); 
                if( $child->getInventory() > 0){
                    $available_options[] = $values[$attribute_selected];
                }
                
            }
            $available_options = array_unique($available_options);

            $options = array_values(array_filter($attributeSelect,function( $item) use ($attribute_selected){
                return $item['attribute_id'] == $attribute_selected;
            }))[0]['values'];


            $toreturn = [];
            $toreturn[0] = array_values($options)[0];
            foreach($options as $k => $v){
                if( in_array($k,$available_options)){
                    $toreturn[$k] = $v;
                }
            }
            $risposta = array(
                'result'=>'ok',
                'options' => $toreturn 
            );
            
        }
        echo json_encode($risposta);
        //}
    }

    /**
     * Mostra la pagina del prodotto
     *
     * @param integer $id
     * @return void
     */
    public function view(int $id): void{
        $qnt = (int)_var('qnt');
        if( !$qnt ) $qnt = 1;
        $this->setVar('qnt',$qnt);
        
        


        $product = Catalog::getProducts(
            array(
                'id'=>$id
            )
        );
       
      
        $prodotto = $product[0];
        if( is_object($prodotto) ){
            /*$this->breadCrumbs($prodotto->section);
            if( Marion::getConfig('catalog','enable_social_link_card_product') ){
                $this->setVar('enable_social_link_card_product',1);
                $this->buildSocialShareProduct($prodotto);
            }*/
            if( !$prodotto->online ){
                if( !Marion::auth('admin')){
                    //$this->template->errore_generico('409',__('product_not_found'));
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
              
                if( $prodotto->product_template_id ){
                    //debugga($prodotto->getAttributesView());exit;
                    $this->setVar('options_product',$prodotto->getAttributesView());
                }else{
                    $children = $prodotto->getChildren();
                    if( okArray($children) ){
                        foreach($children as $v){
                            $options_product[$v->id] = $v->get('name');
                        }
                       
                        $this->setVar('options_product_without_attributes',$options_product);
                    }
                    
                }
                
                }
        }else{
            $this->output('404.htm');
            exit;
        }
        $this->output('@catalogo/view_product.htm');
    }



 

}
?>