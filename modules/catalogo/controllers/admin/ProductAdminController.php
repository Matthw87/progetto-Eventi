<?php
use Marion\Core\Marion;
use Marion\Entities\UserCategory;

use Marion\Support\ListWrapper\DataSource;
use Marion\Support\ListWrapper\ListHelper;
use Marion\Controllers\ListAdminController;
use Marion\Controllers\AdminModuleController;
use Marion\Support\ListWrapper\ListActionRowButton;
use Catalogo\{Product,AttributeSet, Category, Section,Manufacturer, Tag, TagProduct, Template};
use Marion\Support\Form\FormHelper;
use Marion\Support\Form\Fragment;
use Illuminate\Database\Capsule\Manager as DB;

class ProductAdminController extends ListAdminController{
	public $_auth = 'catalog';
	public $_module_ctrls = array();




	public $categories = [];

	// CARICAMENTO DEI JS E CSS
	function setMedia(){
		
		//if( $this->getAction() != 'list'){
			
			$this->registerJS($this->getBaseUrl().'plugins/jnotify/jNotify.jquery.min.js','end');
			$this->registerJS($this->getBaseUrlBackend().'js/function.js','end');
			$this->registerJS('../modules/catalogo/js/admin/product.js','end');
			$this->registerJS('../modules/catalogo/js/admin/product_related.js','end');
			$this->registerJS($this->getBaseUrl().'plugins/inputmask/dist/jquery.inputmask.bundle.js','head');
			
			$js_head_files = array();
			$js_end_files = array();
			$css_files = array();
			Marion::do_action('product_form_javascript_head',array(&$js_head_files));
			Marion::do_action('product_form_javascript_end',array(&$js_end_files));
			Marion::do_action('product_form_css',array(&$css_files));
			
			if( okArray($js_end_files) ){
				foreach($js_end_files as $v){
					$this->registerJS($v,'end');
				}
			}

			
			if( okArray($js_head_files) ){
				foreach($js_head_files as $v){
					$this->registerJS($v,'head');
				}
			}

			if( okArray($css_files) ){
				foreach($css_files as $v){
					$this->registerCSS($v);
				}
			}
		//}

		$this->setMediaModules();
	}

	//OVERIDE	
	function init($options = Array()){
		parent::init();
		$this->loadModuleControllers();
	}


	function displayContent()
	{
		$this->displayVariationForm();
	}

	function displayVariationForm(){
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'parent_id' => [
				'type' => 'hidden'
			],
			'sku' => [
				'type' => 'text',
                'label' => _translate('products.form.fields.sku','catalogo'),
				'validation'=> 'required|max:100'
			],
			'ean' => [
				'type' => 'text',
                'label' => _translate('products.form.fields.ean','catalogo'),
			],
			'upc' => [
				'type' => 'text',
                'label' => _translate('products.form.fields.upc','catalogo'),
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('products.form.fields.name','catalogo'),
				'validation'=> 'required|max:100',
				'multilang' => true
			],
            'description' => [
				'type' => 'editor',
                'label' => _translate('products.form.fields.description','catalogo'),
				'validation'=> 'max:500',
				'multilang' => true
			],
          
            'online' => [
				'type' => 'switch',
                'label' => _translate('products.form.fields.online','catalogo')
			],
			'orderView' => [
				'type' => 'number',
                'label' => _translate('products.form.fields.order_view','catalogo')
			],
			'images' => [
				'type' => 'images',
                'label' => _translate('products.form.fields.images','catalogo')
			],
			'attachments' => [
				'type' => 'files',
                'label' => _translate('products.form.fields.attachments','catalogo')
			]
		];

		
		
		$parent = _var('parent');
		$child_product = null;
		if( !$parent ){
			$id = _var('id');
			if( $id ){
				$child_product = Product::withId($id);
				$parent = $child_product->parent_id;
			}
		}
		
		

		
       
        $form = FormHelper::create('catalogo_product_variation',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'catalogo/templates/admin/forms/product-variation.xml')
			->init(function(FormHelper $form) use ($parent,$child_product){
				//controllo se il form è stato sottomesso
				
				if( !$form->isSubmitted() ){
					$product_parent = Product::withId($parent);
					if( $product_parent ){
						$fragment = $this->createFragmentAttributesProduct($product_parent,$child_product);
						$form->addFragment('attributes',$fragment);
						if( $child_product ){
							$data = $child_product->getDataForm();
						}else{
							$data = $product_parent->getDataForm();
							unset($data['id']);
							unset($data['images']);
							unset($data['attachments']);
							$data['parent_id'] = $product_parent->id;
						}
						$form->formData->data = $data;

						
					}
				}
				
				
            })->process(function(FormHelper $form){
				$data = $form->getValidatedData();
				$attributes = [];
				foreach($data as $k => $v){
					 if( preg_match('/attribute_/',$k)){
						 $attribute_id = preg_replace('/attribute_/','',$k);
						 $attributes[$attribute_id] = $v;
					 }
				}
				$parent = Product::withId($data['parent_id']);
				
				$child = $parent->copy();
				
				$child->set($data);
				$child->setDataFromArray($data);
				$child->setAttributes($attributes);
				
				
				$res = $child->save();
				
				
				
				if( is_object($res) ){
					$form->closePopup();
					$form->triggerEvent('load_variation',$child->id,true);					
				}else{
					$this->errors[] = $res;
				}
				
				

            })->setFields($fields);

		$form->displayPopup();
	}

	/**
	 * Display Form
	 *
	 * @return void
	 */
	function displayForm(){
		$this->setMenu('manage_products');
		$this->setTitle(_translate('products.form.title','catalogo'));

		if( $message = _var('message') ){
			
			$this->displayMessage(_translate('products.messages.'.$message,'catalogo'));
		}
		

		$this->setVar('url_add_variation','/backend/index.php?ctrl=ProductAdmin&mod=catalogo&mod=catalogo&action=add_variation&parent='._var('id'));

		$categories = Category::getAll();
		$select_categories = [
			null => _translate('general.select..')
		];
		$multiselect_categories = $categories;

		foreach($categories as $k => $v){
			$select_categories[$k] = $v;
		}
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'sku' => [
				'type' => 'text',
                'label' => _translate('products.form.fields.sku','catalogo'),
				'validation'=> 'required|max:100'
			],
			'type' => [
				'type' => 'select',
                'label' => _translate('products.form.fields.type','catalogo'),
				'validation' => ['required'],
				'options'=> [
					Product::SIMPLE_TYPE => _translate('products.form.fields.simple','catalogo'),
					Product::CONFIGURABLE_TYPE => _translate('products.form.fields.configurable','catalogo'),
				]
			],
			'product_template_id' => [
				'type' => 'select',
                'label' => _translate('products.form.fields.template','catalogo'),
				'options'=> function(){
					$templates = Template::prepareQuery()->get();
					$toreturn = [];
					$toreturn[null] = _translate('general.select..');
					foreach($templates as $t){
						$toreturn[$t->id] = $t->name;
					}
					return $toreturn;
				}
			],
			'product_manufacturer_id' => [
				'type' => 'select',
                'label' => _translate('products.form.fields.manufacturer','catalogo'),
				'options' => function(){
					$manufacturers = Manufacturer::prepareQuery()->get();
					$select = [
						null => _translate('general.select..')
					];
					foreach($manufacturers as $m){
						$select[$m->id] = $m->get('name');
					}
					return $select;
				}
			],
			'product_category_id' => [
				'type' => 'select',
                'label' => _translate('products.form.fields.main_category','catalogo'),
				'options' => $select_categories
			],
			'secondary_categories' => [
				'type' => 'multiselect',
                'label' => _translate('products.form.fields.secondary_categories','catalogo'),
				'options' => $multiselect_categories
			],
			'ean' => [
				'type' => 'text',
                'label' => _translate('products.form.fields.ean','catalogo'),
			],
			'upc' => [
				'type' => 'text',
                'label' => _translate('products.form.fields.upc','catalogo'),
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('products.form.fields.name','catalogo'),
				'validation'=> 'required|max:100',
				'multilang' => true
			],
            'description' => [
				'type' => 'editor',
                'label' => _translate('products.form.fields.description','catalogo'),
				'validation'=> 'max:500',
				'multilang' => true
			],
          
            'online' => [
				'type' => 'switch',
                'label' => _translate('products.form.fields.online','catalogo')
			],
			'orderView' => [
				'type' => 'number',
                'label' => _translate('products.form.fields.order_view','catalogo')
			],
			'images' => [
				'type' => 'images',
                'label' => _translate('products.form.fields.images','catalogo')
			],
			'attachments' => [
				'type' => 'files',
                'label' => _translate('products.form.fields.attachments','catalogo')
			],
			'slug' => [
				'type' => 'text',
                'label' => _translate('products.form.fields.slug','catalogo'),
				'validation'=> 'max:100',
				'multilang' => true
			],
			'meta_title' => [
				'type' => 'text',
                'label' => _translate('products.form.fields.meta_title','catalogo'),
				'validation'=> 'max:100',
				'multilang' => true
			],
			'meta_description' => [
				'type' => 'textarea',
                'label' => _translate('products.form.fields.meta_description','catalogo'),
				'validation'=> 'max:160',
				'multilang' => true
			],
			'centralized_stock' => [
				'type' => 'switch',
                'label' => _translate('products.form.fields.centralized_stock','catalogo')
			],
			'is_virtual' => [
				'type' => 'switch',
                'label' => _translate('products.form.fields.is_virtual','catalogo')
			],
			'tags' => [
				'type' => 'multiselect',
                'label' => _translate('products.form.fields.tags','catalogo'),
				'options' => function(){
					$tags = Tag::prepareQuery()->get();
					$select = [
						null => _translate('general.select..')
					];
					foreach($tags as $m){
						$select[$m->id] = $m->get('name');
					}
					return $select;
				}
			],
		];




		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('catalogo_product',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'catalogo/templates/admin/forms/product.xml')
			->onDeleteVariation(function(FormHelper $form, $params){
				$id = $params[0];
				$form->removeFragmentById('variation_product_'.$id);
			})
			->onLoadVariation(function(FormHelper $form, $params){
				$new_variation_id = $params[0];
				$data = DB::table('products','p')->leftJoin('product_langs as l',function($join){
					$join->on('l.product_id','=','p.id');
					$join->where('lang',_MARION_LANG_);
				})
				->where('id',$new_variation_id)
				->select(['p.id','p.sku','p.ean','p.upc','l.name','p.parent_id'])->get();


				
				foreach($data as $row){
					$parent = Product::withId($row->parent_id);
					$attributes = $parent->template()->getAttributeWithValues();
			
					$attribute_names = [];
					foreach($attributes as $a){
						foreach($a['values'] as $id => $name){
							$attribute_names[$a['attribute_id']]['name'] = $a['attribute_name'];
							$attribute_names[$a['attribute_id']]['values'][$id] = $name;
						}
						
					}
					$values = DB::table('product_combinations','c')->where("product_id",$row->id)
						->join('product_attribute_value_langs as l',function($join){
							$join->on('l.product_attribute_value_id','=','c.product_attribute_value_id');
							$join->where('l.lang',_MARION_LANG_);
						})
						->select('l.*','c.product_attribute_id')
						->get()->toArray();
					$combiantion_name = '';
					foreach( $values as $v){
						
						if( isset($attribute_names[$v->product_attribute_id]['name']) ){
							$combiantion_name .= "<b>".$attribute_names[$v->product_attribute_id]['name']."</b>: ";
							if( isset($attribute_names[$v->product_attribute_id]['values'][$v->product_attribute_value_id])){
								$combiantion_name .= $attribute_names[$v->product_attribute_id]['values'][$v->product_attribute_value_id];
							}
							$combiantion_name .= "<br/>";
						}
						
					}
					$row->combination_name = $combiantion_name;
					$this->createtableRowAttribute($form,$row);
				}
			})
			->onDeleteAllVariation(function(FormHelper $form, $params){
					$parent_id = $params[0];
					$children = DB::table('products')->where('parent_id',$parent_id)->select(['id'])->get();
					foreach($children as $child){
						$id = $child->id;
						$form->removeFragmentById('variation_product_'.$id);
					}
					$form->triggerEvent('ciaone',['1' => 'ciao']);
			})->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso
				$id = _var('id');
				if( $id ){
					$obj = Product::withId(_var('id'));
					if( is_object($obj) ){
						$this->setVar('product',$obj);
					}
					
				}
				if( !$form->isSubmitted() ){
					if($action != 'add'){
						if( isset($obj) && is_object($obj)){
							$data = $obj->getDataForm();
                            if( $action == 'duplicate' ){
                                unset($data['id']);
                            }
							$form->formData->data = $data;
							$this->createTableAttributes($form,$obj);
							$this->setVar('product',$obj);
						}
						
					}
				}else{
					$submitted_data = $form->getSubmittedData();
					
					if( isset($submitted_data['type']) && $submitted_data['type'] == 2 ){
						if( isset($form->fields['product_template_id']) ){
							$form->fields['product_template_id']['validation'] = ['required'];
						}
						
					}
				}
				if( $action != 'add' ){
					unset($form->fields['type']);
					unset($form->fields['product_template_id']);
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				
                if( $action == 'edit' ){
					$obj = Product::withId($data['id']);
				}else{
					$obj = Product::create();
				}
				
				
				$obj->set($data);
				$obj->tags = isset($data['tags'])?$data['tags']:[];
				$obj->secondary_categories = isset($data['secondary_categories'])?$data['secondary_categories']:[];
				
				$res = $obj->save();
				
				if( is_object($res) ){
					
					$old_children = DB::table('products')->where("parent_id",$res->id)->get()->pluck('id')->toArray();
					//debugga($data);exit;

					$combinations = [];
					foreach($data as $k => $v){
						if( preg_match('/product_child_/',$k)){
							$combination_id = preg_replace('/product_child_([0-9]+)_(.*)/','$1',$k);
							$field = preg_replace('/product_child_([0-9]+)_(.*)/','$2',$k);
							$combinations[$combination_id][$field] = $v;
						}
					}
					foreach($combinations as $child_id => $data){
						if (($key = array_search($child_id, $old_children)) !== false) {
							
							unset($old_children[$key]);
						}
					}

					foreach($old_children as $old_child_id){
						$product = Product::withId($old_child_id);
						$product->delete();
					}


					$params = [];
					if( $action == 'edit' ){
						$params['updated'] = 1;
					}else{
						$params['created'] = 1;
						if( $res->isConfigurable() ){
							header('Location: index.php?ctrl=ProductAdmin&mod=catalogo&mod=catalogo&action=edit&id='.$res->id."&message=add_variations");
							exit;
						}
					}
					if( $form->ctrl instanceof ListAdminController ){
						$form->ctrl->redirectTolist($params);
					}
					
					
				}else{
					$form->ctrl->errors[] = $res;
				}

            })->setFields($fields);

		$form->display();
        
	}
	

	function displayFormOld(){
		$this->setMenu('manage_products');

		
		/****************INIZIO **************************/
			
		
		//prendo l'action
		$action = $this->getAction();
		
		//prendo l'id se specificato
		$id = $this->getID();
		
		
		
		//prendo la lista delle categorie
		$this->getUserCategories();

		
		
		

		//di deault mostro la tab delle informazioni generali
		$this->setVar('tabActive','general');
		$tab = _var('tab');
		$new = _var('new');
		$add_children_message = _var('add_children_message'); // se valorizzata a 1 vuol dire che ho appena creato un prodotto padre 

		
		if( $add_children_message ){
			$this->displayMessage('Hai creato un prodotto <b>configurabile</b>! Ora non ti resta che creare le variazioni.');
		}

		$id = _var('id');
		if( $tab ){
			$this->setVar('tabActive',$tab);
		}
		
		
		// se il form non è sottomesso
		if( !$this->isSubmitted() || $new ){
			
			if( _var('new') ){
				$this->setVar('new_product_with_child',true);
			}
		
			$add_child = false;
			if( $action == 'add' && $id){
				$add_child = true;
			}
			
			if( $action == 'add' && !$add_child ){
				createIDform();
			
				if( $new ){
					$dati = $this->getFormdata();
					if( $dati['type'] == 2 ){
						$dati['parent'] = 0;
					}
				}else{
					$dati = array();
				
				}
				/** PARAMATRI DI DEFAULT */
				if(!isset($dati['weight'])) $dati['weight'] = 1000;
				if(!isset($dati['stock'])) $dati['stock'] = 1;
				if(!isset($dati['orderView'])) $dati['orderView'] = 10;
				if(!isset($dati['urlType'])) $dati['urlType'] = 1;
				

				
				
				
				/*$attributeSet = _var('attributeSet');
				$type = _var('type');
				$dati['attributeSet'] = $attributeSet;*/
				
				if( !$dati['type']  ){
					$dataform = $this->getDataForm('nuovo_prodotto',$dati,$this);
					$this->setVar('dataform',$dataform);
					$this->output('@catalogo/product/new_product.htm');
					exit;
				}
				//$dati['type'] = $type;

				
				

			}else{
				/*if(!$id){
					//l'id non è stato specificato
					$template->errore_generico(256);
				}*/
				//prendo l'oggetto prodotto
				$prodotto = Product::withId($id);

				/*if(!$prodotto){ 
					//il prodotto non esiste
					$template->errore_generico(255);
				}*/
				
				
				
				//prendo i prodotti correlati
				$this->getRelatesProducts($prodotto);
				
				//prendo i dati del form
				$dati =  $prodotto->getDataForm();
				
				//prendo la quantità
				$dati['stock'] = $prodotto->getInventory();
				
				//controlo se il prodotto ha un insieme di attributi
				if($dati['attributeSet']){
					$attributeSet = $dati['attributeSet'];
				}
				
				

				switch( $action ){
					case 'duplicate':
						//$this->getPrices($prodotto,$dati);
					case 'add':
						$action = 'add';
						unset($dati['images']);
						unset($dati['id']);
						break;
					case 'edit';
						// se il prodotto è configurabile
						if($prodotto->isConfigurable()){
							
							//prendo le quantità dei figli
							$form_stock = $prodotto->getInventoryChildren();
							if(okArray($form_stock)){
								$this->setVar('form_veloce_stock',$form_stock);
								
							}
							

						}
						break;
				}
			
				

			}	
			if( $add_child ){
				$dati['parent'] = $id;
				$dati['type'] = 1;
				$dati['parentPrice'] = 1;
				unset($dati['images']);
				unset($dati['id']);
			}
			
			if( $action == 'add' ){
				if( $dati['type'] == 2 && !$attributeSet ){
					
					$this->setVar('no_button_variations',true);
				}
			}else{
				if( $prodotto->isConfigurable() && !$attributeSet ){
					$this->setVar('no_button_variations',true);
				}
			}
			
			
			

			$this->getAttributesInput($dati,$prodotto);
			
			$dati['redirect'] = _var('redirect');
			
			
			
			
			
			
			if($action == 'add_child'){
				
				//$elements['formdata[sku]']->attributes['readonly'] = 'readonly';
				
			}

		}else{
			$dati = $this->getFormdata();

			
			$this->process($dati);
			//form sottomesso



		}
		

		if( $dati['parent'] || $action == 'add_child' ){
			
			//prendo le informazioni del prodotto padre
			$parent_product = Product::withId($dati['parent']);
			if( is_object($parent_product) ){
				$this->setVar('parent_product',$parent_product);
			}

			$parent_attributes = Product::getParentFields();
			$this->setVar('parent_attributes',$parent_attributes);
		}
		
			
		$this->addTemplateFunction(
			new \Twig\TwigFunction('tabActive', function ($val1,$val2) {
				if( $val1 == $val2){
					return "active in";
				}
				return '';
			})
		);
		
		
		$dataform = $this->getDataForm('product',$dati);

		$dataform['section']['other'] = array(
			'data-live-search' => "true"
		);
		$dataform['otherSections']['other'] = array(
			'data-live-search' => "true"
		);

		$dataform['related']['other']['onchange'] = 'add_section_related($(this).val()); return false;';
		




		$this->setVar('dataform',$dataform);
		
		



		//RICHIAMO TUTTE LE TAB AGGIUNTE DA MODULI
		$this->getTabModules();
		
		
		
		$this->output('@catalogo/product/form.htm');

	}

	


	function process($formdata){
		

		$ajax = _var('ajax_request');
		
		
		

		$action = $this->getAction();
		
		
		
		
		if( okArray($formdata['stock_children']) ){
			foreach($formdata['stock_children'] as $k  => $v){
				if( $v['attributes'] ){
					$formdata['stock_children'][$k]['attributes'] = unserialize($v['attributes']);
				}else{
					$formdata['stock_children'][$k]['name'] = $v['name'];
				}
				
			}
		}
		$this->setVar('form_veloce_stock',$formdata['stock_children']);
		
		
		if( $formdata['type'] == 2 &&  !$formdata['attributeSet']){
			$this->setVar('no_button_variations',true);
		}

		

		
		$this->getAttributesInput($formdata,null,$campi_aggiuntivi);
		
		//boh??
		if( $formdata['type'] == 2){
			if( !$formdata['centralized_stock'] ){
				$campi_aggiuntivi['stock']['obbligatorio'] = 0;
			}
		}
		
		

		//se il prodotto è un prodotto figlio allora rendo non obbligatori dei campi
		if($formdata['parent']){
			$campi_aggiuntivi['offer']['obbligatorio'] = 0;
			$campi_aggiuntivi['sku']['obbligatorio'] = 0;
			$campi_aggiuntivi['ean']['obbligatorio'] = 0;
			$campi_aggiuntivi['section']['obbligatorio'] = 0;
			$campi_aggiuntivi['home']['obbligatorio'] = 0;
			$campi_aggiuntivi['orderView']['obbligatorio'] = 0;
		}else{
			$campi_aggiuntivi['parentPrice']['obbligatorio'] = 0;

			if( $formdata['type'] == 2 ){
				$campi_aggiuntivi['weight']['obbligatorio'] = 0;
			}
		}
			
		//se il prodotto è un prodotto figlio ed è stato impostato il prrezzo del prodotto padre allora
		if($formdata['parent'] && $formdata['parentPrice'] == 1){
			$campi_aggiuntivi['price_default']['obbligatorio'] = 0;
		}elseif($formdata['parent']){
			$campi_aggiuntivi['price_default']['obbligatorio'] = 0;
		}
		

	
		
		//aggiungo i campi di controllo relativi ai moduli
		Marion::do_action('product_form',array(&$campi_aggiuntivi));	

		

		//controllo i dati
		$array = $this->checkDataForm('product',$formdata,$campi_aggiuntivi);
		
		
		
		
		
		
		//controllo i dati sottomessi dai moduli
		$check_modules = $this->checkDataModules();
		
		if( $check_modules != 1 ){
			$array[0] = 'nak';
			$array[1] = $check_modules['error'];
			$array[3] = "tab_".$check_modules['tab'];
		}


		
		// il controllo del form è andato a buon fine
		if($array[0] == 'ok'){
			unset($array[0]);
		

			if($action == 'edit'){
				$product = Product::withId($array['id']);
				//if(!is_object($product)) $template->errore_generico(478);
			}else{
				
				//se il prodotto è un prodotto figlio allora lo copio dal padre
				if($array['parent']){
					$product = Product::withId($array['parent'])->copy();
					//elimino i campi del padre che non sono necessari o che prevedono delle dipendenze
					unset($product->images);
					unset($product->dateInsert);
				}else{
					$product = Product::create();
				}
			}
			if( !$array['manufacturer'] ) unset($array['manufacturer']);
			//setto i dati del prodotto
			$product->set($array);


			
			$product->setAttributes($array);
			
			//setto le sezioni secondarie
			$product->setOtherSections($array['otherSections']);
			
			
			
			//debugga($product);exit;
			//salvo il prodotto
			$result = $product->save();
			
			//se il salvataggio non è andato a buon fine
			if(!is_object($result)){
				if( _translate($result) ){
					$errore = _translate($result);
				}else{
					$errore = $result;
				}
				if( $ajax ){
					$risposta = array(
						'result' => 'nak',
						'error' => $errore
					);
					echo json_encode($risposta);
					exit;
				}
				
			

				$this->errors[] = $errore;

			}else{
				
				$_parent_fields = Product::getParentFields();
				foreach($_parent_fields as $key_parent){
					$dati_parent[$key_parent] = $result->$key_parent;
				}

				
				//salvo i correlati
				$product->setRelatedSections($formdata['section_related']);
				$product->saveRelatedSections();
				//setto i tag del prodotto
				$product->saveTags($array['tags']);
				//aggiorno la quantità
				
				if( $product->isConfigurable() ){
					$tot = 0;
					//aggiorno le quantita dei figli
					if($formdata['stock_children']){
						foreach($formdata['stock_children'] as $k => $v){

							$child = Product::withId($k);
							if($child){
								if( $v['image'] ){
									$child->images[0] = $v['image'];
								}else{
									unset($child->images[0]);
								}
								$child->images = array_values($child->images);
								$tmp_data_child = $dati_parent;

								
								
								$tmp_data_child['sku'] = $v['sku'];
								$tmp_data_child['ean'] = $v['ean'];
								$tmp_data_child['upc'] = $v['upc'];
								$tmp_data_child['weight'] = (int)$v['weight'];
								$tmp_data_child['minOrder'] = (int)$v['minOrder'];
								$tmp_data_child['maxOrder'] = (int)$v['maxOrder'];
								
								$child->set( $tmp_data_child )->save();
								$child->updateInventory((int)$v['stock']);
							}
						}
					}
					$tot += (int)$v['stock'];
					$product->updateInventory($tot);
				}else{
					$product->updateInventory($array['stock']);
				}
				
					
				//$this->savePrices($result,$array,$prices_data);
				
				$link = '';
				if( $array['redirect'] ){
					$link = $product->getUrl();
				}else{
					if( $action == 'add' && $result->isConfigurable()){
						$link=$this->getUrlScript()."&action=edit&id=".$result->id."&tab=inventory&add_children_message=1";
					}else{
						if($result->hasParent()){
							$link=$this->getUrlScript()."&action=edit&id=".$result->getParentId()."&tab=inventory";
						}else{
							
						}

					}
				}

				//processo le procedure dei moduli
				$this->processModules($result);

				//controllo se occorre ricaricare la pagina in qualche modulo
				if( !$this->checkReloadPageInModules()){

					$module_contents = $this->reloadContentModules();
				}else{
					if( $action == 'edit'){
						$url_redirect = $this->getUrlEdit();
					}else{
						$url_redirect = $this->getUrlEdit()."&id=".$result->id;
					}
					
				}
				
				

				
				if( $ajax ){
					
					$risposta = array(
						'result' => 'ok',
						'redirect' => $url_redirect?$url_redirect:($link?$link:$this->getUrlList()."&saved=1"),
						'modules' => $module_contents,
						'force_redirect'=> $url_redirect?1:0
					);
					if( $array['redirect'] ){
						$risposta['redirect'] = $product->getUrl();
					}
					echo json_encode($risposta);
					exit;
				}
				

				if( $link ){
					header("Location: {$link}");
					exit;
				}else{
					$this->redirectToList(array('saved'=>1));
				}
				
				//$template->link = $link;
				

				
			
				//$template->output('continua.htm');
			}
		}else{
			if( $ajax ){
				$risposta = array(
					'result' => 'nak',
					'error' => $array[1],
					'field' => $array[2],
					'tab' =>  $array[3] ? $array[3] : '',
				);
				echo json_encode($risposta);
				exit;
			}else{
				$this->errors[] = $array[1];
			}
			
		}

	}

	

	/*function displayList(){
		$this->setMenu('manage_products');
		$this->showMessage();


		

		$reset = _var('reset');
	
		

		
		if( $reset ){
			unset($_SESSION['filter_search_products_admin']);
			unset($_SESSION['order_filter_search_products_admin']);
		}
		$dati = _var('formdata');
		
		

		
		
		if( okArray($dati) ){
			$this->setVar('filtering',true);
			$array = $this->checkdataForm('search_product',$dati);
			
			if( $array[0] == 'ok' ){
				$where = $this->getWhereList($dati);
			}
		}
		

		$limit = $this->getLimitList();
		$offset = $this->getOffsetList();

		$order = _var('orderBy');
		$order_value = _var('orderByValue');

		$this->setVar('limit',$limit);
		
		
		
		

		$query = Product::prepareQuery()->where('parent',0)->where('deleted',0);
		if( $where ){
			$query->whereExpression($where);
		}
		if( $limit ){
			$query->limit($limit);
		}

		if( $order ){
			$query->orderBy($order,$order_value);
			
		}
		$query->orderBy('dateInsert','DESC');
		

		if( $offset ){
			$query->offset($offset);
		}
		$database = Marion::getDB();;
		if( $where ){
			$tot = $database->select('count(*) as cont','product',"parent=0 AND deleted = 0 AND {$where}");
		}else{
			$tot = $database->select('count(*) as cont','product',"parent=0 AND deleted = 0");
		}

		$tot = $tot[0]['cont'];
		$pager_links = $this->getPagerList($tot);

		$prodotti = $query->get();
		$sections = Section::getAll();
		$this->setVar('sections',$sections);
		$this->setVar('prodotti',$prodotti);
		$this->setVar('links',$pager_links);
		
		$dataform = $this->getDataForm('search_product',$dati,$this);
		$this->setVar('dataform',$dataform);
		
		
		
		
		
		$this->output('@catalogo/product/list.htm');




	}*/

	function getList(){
		$database = Marion::getDB();;
		
		$condizione = "parent = 0 AND (deleted is NULL OR deleted= 0) AND (locale is NULL OR locale = '{$GLOBALS['activelocale']}') AND ";
		
		
		$limit = $this->getListContainer()->getPerPage();
		
		if( $sku = _var('sku') ){
			$condizione .= "sku LIKE '%{$sku}%' AND ";
		}

		if( $name = _var('name') ){
			$condizione .= "name LIKE '%{$name}%' AND ";
		}

		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		if( $type = _var('type') ){
			$condizione .= "type = {$type} AND ";
		}
		if( $section = _var('section') ){
			$condizione .= "section = {$section} AND ";
		}

		$visibility = _var('visibility');
		if( isset($_GET['visibility']) && $visibility != -1 ){
			$condizione .= "visibility = {$visibility} AND ";
		}

		$image = _var('image');
		if( isset($_GET['image']) && $image != -1 ){
			$images = serialize(array());
			
			if( $image ){
				$condizione .= "images <> '{$images}' AND ";
			}else{
				$condizione .= "images = '{$images}' AND ";
			}
			
		}
		
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','product as p left outer join productLocale as l on l.product=p.id',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $database->select('id,name,sku,visibility,section,type,images','product as p left outer join productLocale as l on l.product=p.id',$condizione);
		//debugga($database->lastquery);exit;
		$total_items = $tot[0]['tot'];

		//$this->setListOption('html_template','@catalogo/product/list.htm');
		//$this->setListOption('total_items',$total_items);
		$this->_list_options['html_template'] = '@catalogo/product/list.htm';
		//$this->getListContainer()->setTemplateHtml('@catalogo/product/list.htm');
		if( $total_items > 0){
			$this->getListContainer()
				->setTotalItems($total_items)
				->setDataList($list);
		}
		
		
		
	}

	function displayList()
	{
		$this->setMenu('manage_products');
		$this->setTitle(_translate('products.list.title','catalogo'));


		if( _var('updated') ){
			$this->displayMessage(_translate('products.messages.updated','catalogo'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('products.messages.deleted','catalogo'));
		}

		if( _var('created') ){
			$this->displayMessage(_translate('products.messages.created','catalogo'));
		}



		$this->categories = Category::getAll();
		

		$fields = [
			[
				'name' => 'Immagine',
				'function' => function($row){
					debugga($row);exit;
				},
				'function_type' => 'row',
				'searchable' => true,
				'search_name' => 'image',
				'search_value' => (isset($_GET['image']))? _var('image'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => 'seleziona..',
					1 => 'ha immagine',
					0 => 'non ha immagine'
				)
			],
			[
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',
			],
			[
				'name' => 'cod. articolo',
				'field_value' => 'sku',
				'sortable' => true,
				'sort_id' => 'sku',
				'searchable' => true,
				'search_name' => 'sku',
				'search_value' => _var('sku'),
				'search_type' => 'input',
			],
			[
				'name' => 'Nome articolo',
				'field_value' => 'name',
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			],
			[
				'name' => 'Categoria',
				'field_value' => 'product_category_id',
				'function' => function($id){
					if( $id && isset($this->categories[$id]) ){
						return $this->categories[$id];
					}
					return '';
				},
				'function_type' => 'value',
				'sortable' => true,
				'sort_id' => 'product_category_id',
				'searchable' => true,
				'search_name' => 'product_category_id',
				'search_value' => _var('product_category_id'),
				'search_type' => 'product_category_id',
				'search_options' => $this->categories
			],
			[
				'name' => 'visibilità',
				'function' => function($row){
					if( _var('export') ){
						if ($row->online ){
							$html = strtoupper(_translate('online'));
						}else{
							$html = strtoupper(_translate('offline'));
						}
					}else{
						if ($row->online ){
							$html = "<span class='label label-success'  id='status_{$row->id}' style='cursor:pointer;' onclick='change_visibility({$row->id}); return false;'>".strtoupper(_translate('online'))."</span>";
						}else{
							$html = "<span class='label label-danger' id='status_{$row->id}' style='cursor:pointer;' onclick='change_visibility({$row->id}); return false;'>".strtoupper(_translate('offline'))."</span>";
						}
					}
					return $html;
				},
				'function_type' => 'row',
				'sortable' => true,
				'sort_id' => 'online',
				'searchable' => true,
				'search_name' => 'online',
				'search_value' => (isset($_GET['online']))? _var('online'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => 'seleziona..',
					1 => 'online',
					0 => 'offline'
				)
			],
			[
				'name' => 'Tipo',
				'field_value' => 'type',
				'function' => function($val){
					$type = '';
					switch($val){
						case 1:
							$type = 'semplice';
							break;
						case 2:
							$type = 'configurabile';
							break;
					}
					return $type;
				},
				'function_type' => 'value',
				'sortable' => true,
				'sort_id' => 'type',
				'searchable' => true,
				'search_name' => 'type',
				'search_value' => _var('type'),
				'search_type' => 'select',
				'search_options' => array(
					'' => 'seleziona..',
					1 => 'semplice',
					2 => 'configurabile'
				)
			],
			[
				'name' => '',
				'field_value' => 'id',
				'function_type' => 'value',
				'function' => function($id){
					$url = _MARION_BASE_URL_."catalog/product/".$id."/preview";

					$html = "<a href='{$url}' target='_blank' class='edit btn btn-sm btn-default'><i class='fa fa-link'></i></a>";
					return $html;
				}
	
			]

		];

		//parent = 0 AND (deleted is NULL OR deleted= 0) AND (locale is NULL OR locale = '{$GLOBALS['activelocale']}') AND 
		$dataSource = (new DataSource('products'))
				->addFields(
					[
						'product_langs.name',
						'products.id',
						'products.product_category_id',
						'products.sku',
						'products.online',
						'products.type',
					]);
        $dataSource->queryBuilder()
        ->leftJoin('product_langs','product_langs.product_id','=','products.id')
		/*->leftJoin('product_category_langs',function($join){
			$join->on('product_category_langs.product_category_id','=','products.product_category_id');
			$join->where('product_category_langs.lang',_MARION_LANG_);
		})*/
        ->where('product_langs.lang',_MARION_LANG_)
        ->whereNull('products.parent_id')
		->where('products.deleted',0);

        
        

		ListHelper::create('catalogo_product_list',$this)
			->setDataSource($dataSource)
			->setFieldsFromArray($fields)
			->enableExport(true)
			->setExportTypes(['pdf','csv','excel'])
			->enableBulkActions(true)
			->enableSearch(true)
			->setFieldsFromArray($fields)
			->enableBulkActions(false)
			->addEditActionRowButton()
			->addCopyActionRowButton()
			->addDeleteActionRowButton()
			/*->addActionRowButton(
				(new ListActionRowButton('add'))
				->setEnableFunction(function() use ($add){
					return $add;
				})
				->setUrlFunction(function($prod) use ($manufacturer_id){
					return $this->getUrlScript()."&action=add_product&manufacturer_id=".$manufacturer_id."&product_id=".$prod->id."&url_back=".urlencode($this->getUrlCurrent());
					
				})
				->setIcon('fa fa-plus')
				->setText(_translate('manufacturers.product_list.add_product','catalogo'))
				
			)
			->addActionRowButton(
				(new ListActionRowButton('delete'))
				->setEnableFunction(function() use ($add){
					return !$add;
				})
				->setUrlFunction(function($prod) use ($manufacturer_id){
					return $this->getUrlScript()."&action=remove_product&manufacturer_id=".$manufacturer_id."&product_id=".$prod->id."&url_back=".urlencode($this->getUrlCurrent());
					
				})
				->setIcon('fa fa-trash-o')
				->setText(_translate('manufacturers.product_list.remove','catalogo'))
				
			)
			->onDelete(function($id){
				//eliminazione del tag
				$object = Manufacturer::withId($id);
				if( is_object($object)){
					$object->delete();
					$this->displayMessage(_translate('manufacturers.messages.deleted','catalogo'));
				}
				
				
			})
			->onSearch(function(\Illuminate\Database\Query\Builder $query){
				if( $name = _var('name') ){
					$query->where('name','like',"%{$name}%");
				}
				
				if( $section = _var('section') ){
					$query->where('section',$section);
				}
		
				if( $id = _var('id') ){
					$query->where('id',$id);
				}
			})
			->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
				if( in_array($field,['id','name','section'])){
					$query->orderBy($field,$order);
				}
			})*/
			->display();
	}

	function displayListOld(){
		$this->setMenu('manage_products');
		$this->showMessage();
		$this->categories = $this->array_sezioni();
		$fields = [
			[
				'name' => 'Immagine',
				'field_value' => 'images',
				'function' => 'getProductImage',
				'function_type' => 'value',
				'searchable' => true,
				'search_name' => 'image',
				'search_value' => (isset($_GET['image']))? _var('image'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => 'seleziona..',
					1 => 'ha immagine',
					0 => 'non ha immagine'
				)
			],
			[
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',
			],
			[
				'name' => 'cod. articolo',
				'field_value' => 'sku',
				'sortable' => true,
				'sort_id' => 'sku',
				'searchable' => true,
				'search_name' => 'sku',
				'search_value' => _var('sku'),
				'search_type' => 'input',
			],
			[
				'name' => 'Nome articolo',
				'field_value' => 'name',
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			],
			[
				'name' => 'Categoria',
				'field_value' => 'section',
				'function' => 'getCategoryName',
				'function_type' => 'value',
				'sortable' => true,
				'sort_id' => 'section',
				'searchable' => true,
				'search_name' => 'section',
				'search_value' => _var('section'),
				'search_type' => 'select',
				'search_options' => $this->categories
			],
			[
				'name' => 'visibilità',
				'function' => 'onlineOffline',
				'function_type' => 'row',
				'sortable' => true,
				'sort_id' => 'visibility',
				'searchable' => true,
				'search_name' => 'visibility',
				'search_value' => (isset($_GET['visibility']))? _var('visibility'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => 'seleziona..',
					1 => 'online',
					0 => 'offline'
				)
			],
			[
				'name' => 'Tipo',
				'field_value' => 'type',
				'function' => 'productType',
				'function_type' => 'value',
				'sortable' => true,
				'sort_id' => 'type',
				'searchable' => true,
				'search_name' => 'type',
				'search_value' => _var('type'),
				'search_type' => 'select',
				'search_options' => array(
					'' => 'seleziona..',
					1 => 'semplice',
					2 => 'configurabile'
				)
			],
			[
				'name' => '',
				'field_value' => 'id',
				'function_type' => 'value',
				'function' => 'getProductLink'
	
			]

		];

		//$bulk_actions = $this->getListOption('bulk_actions');

		$bulk_actions['actions']['active'] = array(
				'text' => 'rendi online',
				'icon_type' => 'icon',
				'icon' => 'fa fa-eye',
				'img' => '',
				'confirm' => true,
				'confirm_message' => 'Sicuro di voler rendere <b>online</b> i prodotti selezionati?',
				

		);
		$bulk_actions['actions']['inactive'] = array(
				'text' => 'rendi offline',
				'icon_type' => 'icon',
				'icon' => 'fa fa-eye-slash',
				'img' => '',
				'confirm' => true,
				'confirm_message' => 'Sicuro di voler rendere <b>offline</b> i prodotti selezionati?',
				

		);
		$bulk_actions['actions']['change_section'] = array(
				'text' => 'cambia categoria',
				'icon_type' => 'icon',
				'icon' => 'fa fa-move',
				'img' => '',
				'confirm' => true,
				//'confirm_message' => 'Sicuro di voler rendere offline i prodotti selezionati?',
				'ajax_content' => 'displayFormCategoryBulk',

		);
		//$this->setListOption('bulk_actions',$bulk_actions);



		$this->setTitle('Prodotti');
		$this->getListContainer()
			->addEditActionRowButton()
			->addCopyActionRowButton()
			->addDeleteActionRowButton()
			->setFieldsFromArray($fields)
			->build();
			//->addActionBulkButtons($bulk_actions);
		$this->getList();


		parent::displayList();

	}
	
	function displayFormCategoryBulk(){
		$dataform = $this->getDataForm('product_bulk_action_category');
		$this->setVar('dataform',$dataform);
		$this->output('@catalogo/product/form_bulk_action.htm');
		
	}

	function getProductImage($val){
		$html = '';
		$images = unserialize($val);
		if( okArray($images) ){
			$id_image = $images[0];
			if( $id_image ){
				$html = "<img class='imgprodlist' src='/img/{$id_image}/th/img.png' alt=''>";
			}
		}
		return $html;
	}
	function getCategoryName($val){
		if( isset($this->categories[$val]) && $this->categories[$val]) return $this->categories[$val];
		return '';
	}

	function getProductLink($val){
		$url = _MARION_BASE_URL_."catalog/product/".$val."/preview";

		$html = "<a href='{$url}' target='_blank' class='edit btn btn-sm btn-default'><i class='fa fa-link'></i></a>";
		return $html;
	}


	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Prodotto salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Prodotto eliminato con successo','success');
		}

		if( _var('deleted_multiple') ){
			$this->displayMessage('I prodotti selezionati sono stati eliminati con successo','success');
		}

		
	}
	


	function saved(){
		$this->redirectTolist(array('saved'=>1));
	}

	function bulk(){
		$action = $this->getBulkAction();
		$ids = $this->getBulkIds();
		$database = Marion::getDB();;

		switch($action){
			case 'active':
				
				foreach($ids as $id){

					$obj = Product::withId($id);
					if( is_object($obj) ){
						$obj->set(array('visibility' => 1));
						$obj->save();
					}
				}
				break;
			case 'inactive':
				foreach($ids as $id){
					$obj = Product::withId($id);
					if( is_object($obj) ){
						$obj->set(array('visibility' => 0));
						$obj->save();
					}
					
				}
				break;
			case 'delete':
				foreach($ids as $id){
					$obj = Product::withId($id);
					if( is_object($obj) ){
						$obj->delete();
					}
				}
				break;
			case 'change_section':
				$data = $this->getBulkForm();
				if( $data['section'] ){
					foreach($ids as $id){
						$obj = Product::withId($id);
						if( is_object($obj) ){
							$obj->set(array('section' => $data['section']));
							$obj->save();
						}
					}
				}
				break;
		}
		parent::bulk();
	}


	function delete(){
		$id = $this->getID();
		if( (int)$id ){
			$list[] = $id; 
		}else{
			$list = (array)json_decode($id);
		}
		
		foreach($list as $v){
			$obj = Product::withId($v);
			
			$obj->delete();
		}
		if( count($list) > 1 ){
			$this->redirectToList(array('deleted_multiple'=>1));
		}else{
			if( $obj->parent ){
				header('Location: '.$this->getUrlScript()."&action=edit&id=".$obj->parent."&tab=inventory");
			}else{
				$this->redirectToList(array('deleted'=>1));
			}



		}

		
	}


	function ajax(){
		
		$action = $this->getAction();
		$id = $this->getID();
		switch($action){
			case 'get_product_section':
				$section = _var('section');
				$name = _var('name');
				$query = Product::prepareQuery()
						->whereExpression("(name like '%{$name}%' OR sku like '%{$name}%')")
						->where('section',$section)
						->where('parent',0);
				
				$prodotti = $query->get();
				$toreturn = array();
				
				if( okArray($prodotti) ){
					foreach($prodotti as $k => $v){
						$item = array(
							'name' => $v->get('name'),
							'id' => $v->id,
							'img' => $v->getUrlImage(0,'small')
						);
						$toreturn[] = $item;
					}
				}
				$risposta = array(
					'result' => 'ok',
					'data' => $toreturn
				);
				
				

				break;

			case 'change_visibility':
				$obj = Product::withId($id);
				if( is_object($obj) ){
					if( $obj->visibility ){
						$obj->visibility = 0;
					}else{
						$obj->visibility = 1;
					}
					
					$obj->save();
					$risposta = array(
						'result' => 'ok',
						'status' => $obj->visibility,
						'text' => $obj->visibility? strtoupper(_translate('online')):strtoupper(_translate('offline')),
					);
				}else{
					$risposta = array(
						'result' => 'nak'	
					);
				}
				break;
			case 'add_child_rapid_ok':
				$formdata = $this->getFormdata();
				
				
				$combinazioni = $formdata['combinazioni'];
				$parent = $formdata['parent'];
				
				unset($_SESSION['last_child_product_'.$parent]);

				$product = Product::withId($parent);
				$attributes = $product->getAttributes();
				$num_var = count($attributes);
				$tmp = $product;
				unset($tmp->id);
				unset($tmp->images);
				unset($tmp->_old_images);
				unset($tmp->dateInsert);
				unset($tmp->dateLastUpdate);
				unset($tmp->relatedSections);
				$tmp->type = 1;
				$tmp->parent = $parent;
				$database = Marion::getDB();;
				foreach($combinazioni as $comb){
					
					$check = 0;
					//debugga($comb['attributi']);
					foreach($comb['attributi'] as $v){
						if( $v ){
							$check++;
						}
					}
					if( $check != $num_var ){
						$errore = "Selezionare un valore per ciascuna variazione";
					}
					
					if( !$errore ){
						if( $comb['checked'] ){
							$child = clone $tmp;
							//$child->parentPrice = 1;
							$child->stock = (int)$comb['stock'];
							$child->visibility = 1;
							//$child->minOrder = 1;
							$child->setAttributes($comb['attributi']);
							$res = $child->save();

							
							
							
							
							if( is_object($res) ){
								
								$_SESSION['last_child_product_'.$parent][$res->id] = $res->id;
							}else{
								$errore = _translate($res);
							}
							
						}
					}
				}
				
				if( !$errore ){
					$risposta = array(
						'result' => 'ok',
						'id' => $parent,
					);
				}else{
					$risposta = array(
						'result' => 'nak',
						'errore' => $errore
					);
				}
				
				
		
				break;
			case 'get_children_stock':


				
				$product = Product::withId($id);
				//prendo le quantità dei figli
				//$form_stock = $product->getStockChildren();
				$form_stock = $product->getInventoryChildren();
				if( okArray($form_stock) ){
					foreach($form_stock as $k => $v){
						if( !$_SESSION['last_child_product_'.$id][$k] ){
							unset($form_stock[$k]);
						}
					}
					unset($_SESSION['last_child_product_'.$id]);


					$this->setVar('form_veloce_stock',$form_stock); 
				}
				
				ob_start();
				$this->output('@catalogo/product/form_stock_children.htm');
				$html = ob_get_contents();
				ob_end_clean();
			   
				$risposta = array(
					'result' => 'ok',
					'html' => $html,
				);

				

				break;
		
				
		}

		echo json_encode($risposta);
		
	}



	// METODI 


	function getWhereList($formdata){

		if( okArray($formdata) ){
			$_SESSION['filter_search_products_admin'] = $formdata;
			$array = $this->checkDataForm('search_product',$formdata);
			$where = '';
			if( $array[0] == 'ok'){
			
				if( $array['visibility'] ){
					if( $array['visibility'] == 1 ){
						$where .= "visibility = 1 AND ";
					}else{
						$where .= "visibility = 0 AND ";
					}
				}

				if( $array['section'] ){
					$where .= "section = {$array['section']} AND ";
				}

				if( $array['id'] ){
					$where .= "id = {$array['id']} AND ";
				}

				if( $array['name'] ){
					$where .= "name LIKE '%{$array['name']}%' AND ";
				}

				if( $array['sku'] ){
					$where .= "sku LIKE '%{$array['sku']}%' AND ";
				}

				if( $array['type'] ){
					$where .= "type = {$array['type']} AND ";
				}
			}
			
		}

		if( $where ){
			$where = preg_replace('/ AND $/','',$where);
		}
		

		return $where;


	}
	function getUserCategories(){
		$categorie = UserCategory::prepareQuery()->get();
		$this->setVar('categorie',$categorie);
	}




	function getAttributesInput($dati,$prodotto=NULL,&$campi_aggiuntivi=null){
		$action = $this->getAction();
		$attributeSet = $dati['attributeSet'];
		
		
		
		if($attributeSet){

			$insieme_Attributi = AttributeSet::withId($attributeSet);
			if($insieme_Attributi){
				if( $dati['type'] == 1 ){
					$attributeSelect = $insieme_Attributi->getAttributeWithValues(); 
				}
				if( $dati['type'] == 2 ){
					$attributeSelect = $insieme_Attributi->getAttributeWithValuesAndImages(); 
					//debugga($attributeSelect);exit;
				}
			}
		}

		if( $this->isSubmitted()){

			
			if( $dati['type'] == 1 ){
				if( okArray($attributeSelect) ){
					foreach($attributeSelect as $k => $v){
						$campi_aggiuntivi[$k] = array(
								'campo'=>$k,
								'type'=>'select',
								'options' => $v,
								'obbligatorio'=>'t',
								'default'=>'0',
								'etichetta'=>$k
							);
						$attributi_selezionati[$k] = $dati[$k];

					}
				}
			}
		
		}else{

			
			if($action != 'add'){
				//prelevo i valori degli attributi per il prodotto in esame
				$attributi_selezionati = $prodotto->getAttributes();
				
			}
		}
		if(okArray($attributeSelect)){

			$this->setVar('attributes',$attributeSelect);
			$this->setVar('select_variazione_prodotto',$attributeSelect);
		}
		if(okArray($attributi_selezionati)){
			$this->setVar('attributiSelezionati',$attributi_selezionati);
		}
		
		

	}

	function getRelatesProducts($prodotto){
		if( !$prodotto->parent ){

			//PRENDO I PRODOTTI CORRELATI
			$sections_related = $prodotto->relatedSections;
			$num_products_related = 0;
			if( okArray($sections_related) ){
				foreach($sections_related as $k => $v){
					$sectionRel = Section::withId($v['section']);
					if( is_object($sectionRel) ){
						$v['section_name'] = $sectionRel->get('name');
						if( $v['type'] == 'specific' ){
							if( okArray($v['products']) ){
								$where = '(id in (';
								foreach($v['products'] as $_id){
									$where .= "{$_id}, ";
								}
								$where = preg_replace('/\, $/','))',$where);
								$v['products'] = Product::prepareQuery()->where('visibility',1)->whereExpression($where)->get();
								foreach($v['products'] as $v2){
									$list_products_related[] = $v2->id;
								}
							}
						}
						$num_products_related += count($v['products']);
						$sections_related[$k] = $v;
						$list_sections_related[] = $v['section'];
					}else{
						unset($sections_related[$k]);
					}
					
				}
			}
			$this->setVar('list_products_related',$list_products_related);
			$this->setVar('list_sections_related',$list_sections_related);
			if( okArray($sections_related) ){
				$num_sections_related = count($sections_related);
			}else{
				$num_sections_related = 0;
			}
			
			$this->setVar('num_products_related',$num_products_related);
			$this->setVar('num_sections_related',$num_sections_related);
			$this->setVar('relatedSections',$sections_related);
			
			
		}
	}


	//FORM
	function array_sezioni_prodotto(){
		return $this->array_sezioni();
	}
	function array_sezioni(){
		
		$sezioni = Section::getAll('it');
		
		$select = array('seleziona...');
		if( okArray($sezioni) ){
			foreach($sezioni as $k => $v){
				$select[$k] = $v;
			}
		}
		
		return $select;
	}

	function array_produttori(){
		
		$produttori = Manufacturer::prepareQuery()->get();
		
		$select = array('seleziona...');
		foreach($produttori as $v){
			$select[$v->id] = $v->get('name');
		}
		return $select;

	}

	function array_tag_product(){
		
		$tag = TagProduct::prepareQuery()->get();
		
		
		foreach($tag as $v){
			$select[$v->id] = $v->label;
		}
		return $select;

	}





	function array_insieme_attributi(){
		
		$insiemi = AttributeSet::getList();
		
		$select = array('nessuno');
		foreach($insiemi as $k => $v){
			$select[$v->getId()] = $v->getLabel();
		}
		return $select;

	}


	
	/*  MODULI */
	//metodo che carica i moduli controllers
	function loadModuleControllers(){
		if(okArray(Product::$_registred_classes)){
			foreach(Product::$_registred_classes as $v){
				
				$mod_ctrl = new $v($this);
				if($mod_ctrl->isEnabled()){
					$this->_module_ctrls[] = $mod_ctrl;
				}
				
			}
			
		}
	}

	//metodo che aggiunge al form le tab dei moduli
	function getTabModules(){
		
		if(okArray($this->_module_ctrls)){
			$this->setVar('admin_tab_classes',$this->_module_ctrls);
		}
	}
	//metodo che controlla i dati passati dal form dei moduli
	function checkDataModules(){
		
		$check = true;
		if( okArray($this->_module_ctrls) ){
			foreach( $this->_module_ctrls as $obj){
				$_check = $obj->checkData();
				if( $_check != 1 ){
					
					$check = array();
					$check['error'] = $_check;
					$check['tab'] = $obj->getTag();
					break;
				}
			}
		}

		

		return $check;
	}
	//carica i file js e css dei moduli
	function setMediaModules(){
		
		if(okArray($this->_module_ctrls)){
			foreach( $this->_module_ctrls as $obj){
				$obj->setMedia();
			}
		}
	}
	

	//metodo che processa i moduli dopo il salvataggio del prodotto
	function processModules($product){
		
		
		if(okArray($this->_module_ctrls)){
			foreach($this->_module_ctrls as $obj){
				
				
				$obj->process($product);

			}
		}

		
	}

	//metodo che ricarica il contenuto delle tab dei moduli
	function reloadContentModules(){
		
		if(okArray($this->_module_ctrls)){
			foreach($this->_module_ctrls as $obj){
				
				
				
				if( $obj->reloadContent()){
					ob_start();
					
					$obj->getContent();
					$html = ob_get_contents();
					ob_end_clean();
					$content[$obj->getTag()] = $html; 
				}
			}
		}

		return $content;
	}

	//metodo che controlla se occorre ricaricare la pagina
	function checkReloadPageInModules(){
		$check = false;
		if(okArray($this->_module_ctrls)){
			foreach($this->_module_ctrls as $obj){
				
				
				
				if( $obj->reloadPage()){
					$check = true;
					break;
				}
			}
		}

		return $check;
	}

	/*  FINE MODULI */




	//override

	function getFormdata($num=null){
		if(	$num ){
			if( $this->_ajax || _var('ajax_request') ){
				$formdata = _formdata($num);
			}else{
				$formdata = _var('formdata'.$num);
			}
		}else{
			if( $this->_ajax  || _var('ajax_request')){
				$formdata = _formdata();
			}else{
				$formdata = _var('formdata');
			}
		}		
		
		return $formdata;
	}

	function createFragmentAttributesProduct(Product $product, ?Product $child): Fragment{
		$template = $product->template();
		$select = $template->getAttributeWithValues();


		if( $child ){
				$attributes = $child->getAttributes();
		}


		$fields = [];
		$xml_fields = [];
		foreach($select as $v){
			$xml_fields[] = 'attribute_'.$v['attribute_id'];
			$fields['attribute_'.$v['attribute_id']] = [
					'type' => 'select',
					'label' => $v['attribute_name'],
					'options' => $v['values'],
					'validation' => 'required'
					
				];
		}

		$xml = '<row>';
		foreach($xml_fields as $xml_flield){
			$xml .= "<col><field name='{$xml_flield}'/></col>";
		}
		$xml .= "</row>";

		$fragment = new Fragment('box_attributes',$this);
        $fragment->setTemplate("
            <fragment>
					{$xml}
            </fragment>
        ");

        
        $fragment->setFields($fields);

		if( isset($attributes) ){
			foreach($attributes as $attribute => $value){
				$data = [
					'attribute_'.$attribute => $value
				];
			}
			$fragment->setDataForm($data);
		}
        return $fragment;

	}

	/**
	 * Crea la tabella degli attributi nel form
	 *
	 * @param FormHelper $form
	 * @param Product $product
	 * @return void
	 */
	function createTableAttributes(FormHelper $form, Product $product){
		if( $product->product_template_id ){
			$data = DB::table('products','p')->leftJoin('product_langs as l',function($join){
				$join->on('l.product_id','=','p.id');
				$join->where('lang',_MARION_LANG_);
			})
			->where('parent_id',$product->id)
			->select(['p.id','p.sku','p.ean','p.upc','l.name'])->get()->toArray();

			$attributes = $product->template()->getAttributeWithValues();
			
			$attribute_names = [];
			foreach($attributes as $a){
				foreach($a['values'] as $id => $name){
					$attribute_names[$a['attribute_id']]['name'] = $a['attribute_name'];
					$attribute_names[$a['attribute_id']]['values'][$id] = $name;
				}
				
			}
			
		
			foreach($data as $row){
				$values = DB::table('product_combinations','c')->where("product_id",$row->id)
					->join('product_attribute_value_langs as l',function($join){
						$join->on('l.product_attribute_value_id','=','c.product_attribute_value_id');
						$join->where('l.lang',_MARION_LANG_);
					})
					->select('l.*','c.product_attribute_id')
					->get()->toArray();
				$combiantion_name = '';
				
				foreach( $values as $v){
					
					if( isset($attribute_names[$v->product_attribute_id]['name']) ){
						$combiantion_name .= "<b>".$attribute_names[$v->product_attribute_id]['name']."</b>: ";
						if( isset($attribute_names[$v->product_attribute_id]['values'][$v->product_attribute_value_id])){
							$combiantion_name .= $attribute_names[$v->product_attribute_id]['values'][$v->product_attribute_value_id];
						}
						$combiantion_name .= "<br/>";
					}
					
				}
				$row->combination_name = $combiantion_name;
				$this->createtableRowAttribute($form,$row);
			}
		}
	}

	function createtableRowAttribute(FormHelper $form, object $row){
		$fields = [
			"product_child_{$row->id}_id" => [
				'type' => 'hidden'
			],
			"product_child_{$row->id}_sku" => [
				'type' => 'text',
				'label' => 'sku' 
			],
			"product_child_{$row->id}_ean" => [
				'type' => 'text',
				'label' => 'ean' 
			],
			"product_child_{$row->id}_upc" => [
				'type' => 'text',
				'label' => 'upc' 
			],
		];
		$data = [
			"product_child_{$row->id}_id" => $row->id,
			"product_child_{$row->id}_sku" => $row->sku,
			"product_child_{$row->id}_ean" => $row->ean,
			"product_child_{$row->id}_upc" => $row->upc,
		];
		$fragment = new Fragment('variation_product_'.$row->id,$this);
		$fragment->setTemplate("
			<fragment>
					<tr>
						<td>{$row->combination_name}</td>
						<td>
							<field name='product_child_{$row->id}_id' hidden='true'/>
							<field name='product_child_{$row->id}_sku' />
						</td>
						<td>
							<field name='product_child_{$row->id}_ean' />
						</td>
						<td>
							<field name='product_child_{$row->id}_upc' />
						</td>
						<td>
							<div style='display: flex; justify-content: space-between; align-items: center;'>
								<button class='btn btn-info' type='button' onclick='javascript:formEvent(\"delete_variation\",{$row->id})'> <i class='fa fa-pencil'> </i> "._translate('list.edit')."</button>
								<button class='btn btn-danger' type='button' onclick='javascript:formEvent(\"delete_variation\",{$row->id})'> <i class='fa fa-trash-o'> </i> "._translate('list.delete')."</button>
							</div>
                        </td>
					</tr>
			</fragment>
		");
		$fragment->setFields($fields);
		$fragment->setDataForm($data);
		$form->addFragment('rows',$fragment);
	}



}



?>