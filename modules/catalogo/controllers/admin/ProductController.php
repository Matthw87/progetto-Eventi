<?php
use Marion\Support\ListWrapper\DataSource;
use Marion\Support\ListWrapper\ListHelper;
use Marion\Controllers\ListAdminController;
use Catalogo\{Product, Category, Manufacturer, Tag, Template, Warehouse};
use Marion\Support\Form\FormHelper;
use Marion\Support\Form\Fragment;
use Illuminate\Database\Capsule\Manager as DB;


class ProductController extends ListAdminController{
	public $_auth = 'catalog';
	public $categories = [];



	function displayContent()
	{
		$this->displayVariationForm();
	}

	function displayVariationForm(){
		$action = $this->getAction();
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
			'quantity' => [
				'type' => 'number',
                'label' => _translate('products.form.fields.quantity','catalogo'),
				'validation' => ['required']
			],
			'weight' => [
				'type' => 'number',
                'label' => _translate('products.form.fields.weight','catalogo'),
				'validation' => ['required']
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

		
		
		
		
	
		$id = null;
		$parent = null;
		if( $action == 'edit_variation' ){
			$id = _var('id');
		}else{
			$parent = _var('parent');
		}
		
		

		
       
        $form = FormHelper::create('catalogo_product_variation',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'catalogo/templates/admin/forms/product-variation.xml')
			->init(function(FormHelper $form) use ($parent,$id,$action){
				//controllo se il form è stato sottomesso
				
				if( !$form->isSubmitted() ){
					if( $action == 'add_variation' ){
						$product_parent = Product::withId($parent);
						if( $product_parent ){
							$fragment = $this->createAttributesProductFragment($product_parent);
							$form->addFragment('attributes',$fragment);
							$data = $product_parent->getDataForm();
							unset($data['id']);
							unset($data['images']);
							unset($data['attachments']);
							$data['parent_id'] = $product_parent->id;
							$data['quantity'] = 1;
							$data['online'] = 1;
							$form->formData->data = $data;						
						}
					}else{
						$product = Product::withId($id);
						$fragment = $this->createAttributesProductFragment($product,$product->getAttributes());
						$form->addFragment('attributes',$fragment);
						$data = $product->getDataForm();

						$data['quantity'] = $product->getInventory();
						$form->formData->data = $data;				
					}	
				}
            })->process(function(FormHelper $form) use ($action){
				$data = $form->getValidatedData();
				$attributes = [];
				foreach($data as $k => $v){
					 if( preg_match('/attribute_/',$k)){
						 $attribute_id = preg_replace('/attribute_/','',$k);
						 $attributes[$attribute_id] = $v;
					 }
				}

				if( $action == 'edit_variation' ){
					$child = Product::withId($data['id']);
				}else{
					$parent = Product::withId($data['parent_id']);	
					$child = $parent->copy();
					$child->_old_images = [];
					
				}
				
				
				
				
				$child->set($data);
				$child->setDataFromArray($data);
				$child->setAttributes($attributes);
				
				
				$res = $child->save();
				if( is_object($res) ){
					$res->updateInventory($data['quantity']);
				
					$form->closePopup();
					$form->triggerEvent('load_variation',[
							'id' => $child->id,
							'action' => $action == 'add_variation'?'add':'edit'
						],true);					
				}else{
					$this->errors[] = $res;
				}
            })
			->setIconSubmitButton($action == 'add_variation'?'fa fa-plus':'fa fa-refresh')
			->setTextSubmitButton($action == 'add_variation'?_translate('form.add'):_translate('form.update'))
			->setFields($fields);

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
		

		$this->setVar('url_add_variation','/backend/index.php?ctrl=Product&mod=catalogo&mod=catalogo&action=add_variation&parent='._var('id'));

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
			'related_category' => [
				'type' => 'select',
                'label' => _translate('products.form.fields.related_categories','catalogo'),
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
			'order_view' => [
				'type' => 'number',
                'label' => _translate('products.form.fields.order_view','catalogo')
			],
			'quantity' => [
				'type' => 'number',
                'label' => _translate('products.form.fields.quantity','catalogo'),
				'validation' => ['required']
			],
			'weight' => [
				'type' => 'number',
                'label' => _translate('products.form.fields.weight','catalogo'),
				'validation' => ['required']
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

		$product = null;
		$id = _var('id');
		if( $id ){
			$product = Product::withId($id);
			if( is_object($product) ){
				$this->setVar('product',$product);
			}
			
		}
		
       
        $form = FormHelper::create('catalogo_product',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'catalogo/templates/admin/forms/product.xml')
			->onDeleteVariation(function(FormHelper $form, $params){
				$id = $params[0];
				$form->removeFragmentById('variation_product_'.$id);
			})
			/*->onChange('related_products_',function(FormHelper $form, $value){
				$form->fields['related_products_1_value']['options'] = [
					1 => 'ciro',
					2 => 'napolitano'
				];
				$form->changeField('related_products_1_value');
			})*/
			->onChangeRelatedCategory(function(FormHelper $form, $value){
				if( $value ){
					$form->formData->data['related_category'] = '';
					$form->changeField('related_category');

					$index = $form->retrive('related_products_index');
					if( !$index ) $index = 0;
					$index++;
					$fragment = $this->createRelatedProductsBox($index);
					$form->store('related_products_index',$index);
					$form->addFragment('wrapper-related-products',$fragment);
				}
				
			})->onChangeType(function(FormHelper $form, $value){
				if( $value == 2 ){
					$form->fields['product_template_id']['validation'] = ['required'];
					$form->hideField('quantity');
					$form->hideField('weight');
				}else{
					$form->fields['product_template_id']['validation'] = [];
					$form->showField('quantity');
					$form->showField('weight');
					
				}
				$form->changeField('product_template_id');
			})->onLoadVariation(function(FormHelper $form, $params){
				$params = $params[0];

				$new_variation_id = $params['id'];
				$child_action = $params['action'];
				$data = DB::table('products','p')->leftJoin('product_langs as l',function($join){
					$join->on('l.product_id','=','p.id');
					$join->where('lang',_MARION_LANG_);
				})->leftJoin('product_quantities as q', function($join2){
					$join2->on('q.product_id','=','p.id');
					$join2->where('warehouse_id',1);
				})
				->where('id',$new_variation_id)
				->select(['p.id','p.sku','p.ean','p.upc','l.name','p.parent_id','q.quantity','p.images','p.online'])->get();
				
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

					$mode = ($child_action == 'edit')?'replace':'append';
					$this->createTableRowAttribute($form,$row,$mode);
				}
			})
			->onDeleteAllVariation(function(FormHelper $form, $params){
					$parent_id = $params[0];
					$children = DB::table('products')->where('parent_id',$parent_id)->select(['id'])->get();
					foreach($children as $child){
						$id = $child->id;
						$form->removeFragmentById('variation_product_'.$id);
					}
			})->init(function(FormHelper $form) use ($action, $product){
				//controllo se il form è stato sottomesso
			
				if( $product && $product->isConfigurable()){
					unset($form->fields['quantity']);
					unset($form->fields['weight']);
				}
			
				if( !$form->isSubmitted() ){
					if($action != 'add'){
						if( isset($product) && is_object($product)){
							$data = $product->getDataForm();

							if( !$product->isConfigurable() ){
								$data['quantity'] = $product->getInventory();
							}
                            if( $action == 'duplicate' ){
                                unset($data['id']);
                            }
							$form->formData->data = $data;
							$this->createTableAttributes($form,$product);
						}
					}else{
						
						$form->formData->data = [
							'order_view' => 10,
							'weight' => 1000,
							'quantity' => 1,
							'online' => 1
						];
					}
				}else{
					$submitted_data = $form->getSubmittedData();
					
					if( isset($submitted_data['type']) ){
						if( $submitted_data['type'] == 2 ){
							$form->hideField('quantity');
							$form->hideField('weight');
							$form->fields['quantity']['validation'] = [];
							$form->fields['weight']['validation'] = [];
							if( isset($form->fields['product_template_id']) ){
								$form->fields['product_template_id']['validation'] = ['required'];
							}
							
							if( isset($form->fields['product_template_id']) ){
								$form->fields['product_template_id']['validation'] = ['required'];
							}
						}else{
							//debugga($submitted_data['type']);exit;
							
							$form->fields['quantity']['validation'] = ['required'];
							$form->fields['weight']['validation'] = ['required'];
							
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
					if( !$res->isConfigurable() && isset($data['quantity']) ){
						$res->updateInventory($data['quantity']);
					}
					
					$data_children = DB::table('products')->where("parent_id",$res->id)
						->leftJoin('product_quantities',function($join){
							$join->on('product_quantities.product_id','=','products.id');
							$join->where('warehouse_id',Warehouse::DEFAULT);
						})
						->get(['id','sku','ean','upc','quantity as qty'])->toArray();
					
					$old_children = [];
					foreach($data_children as $_c){
						$old_children[$_c->id] = (array)$_c;
					}
					

					$combinations = [];
					foreach($data as $k => $v){
						if( preg_match('/product_child_/',$k)){
							$combination_id = preg_replace('/product_child_([0-9]+)_(.*)/','$1',$k);
							$field = preg_replace('/product_child_([0-9]+)_(.*)/','$2',$k);
							$combinations[$combination_id][$field] = $v;
						}
					}
					
					foreach($combinations as $child_id => $child_data){
						if (array_key_exists($child_id, $old_children) ) {

							$check_update = false;
							foreach($old_children[$child_id] as $k => $v){
								if( trim($v) != trim($child_data[$k]) ){
									$check_update = true;
								}
							}
							if( $check_update ){
								$child = Product::withId($child_id);
								$child->set($child_data)->save();
								$child->updateInventory($child_data['qty']);
							}
							unset($old_children[$child_id]);
						}
					}
					foreach(array_keys($old_children) as $old_child_id){
						$product = Product::withId($old_child_id);
						$product->delete();
					}


					$params = [];
					if( $action == 'edit' ){
						$params['updated'] = 1;
					}else{
						$params['created'] = 1;
						if( $res->isConfigurable() ){
							header('Location: index.php?ctrl=Product&mod=catalogo&mod=catalogo&action=edit&id='.$res->id."&message=add_variations");
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


	/**
	 * Display List
	 *
	 * @return void
	 */
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
					if( $row->images ){
						$images = unserialize($row->images);
						if(okArray($images)){
							return '<img style="max-width: 80px;" src=\''._MARION_BASE_URL_."img/".$images[0]."/th/image.png'></img>";
						}
					}
					
				},
				'function_type' => 'row',
				'searchable' => false,
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
				'search_type' => 'select',
				'search_options' => array_merge([0 => _translate('general.select..')],$this->categories)
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
					$url = _MARION_BASE_URL_."catalogo/product/".$id."/preview";

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
						'products.images',
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

        if( !_var('orderBy') ){
			$dataSource->queryBuilder()->orderBy('id','DESC');
		}
        

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
			->onDelete(function($id){
				
				$object = Product::withId($id);
				if( is_object($object)){
					$object->delete();
					$this->displayMessage(_translate('products.messages.deleted','catalogo'));
				}
				
				
			})
			->onSearch(function(\Illuminate\Database\Query\Builder $query){
				if( $name = _var('name') ){
					$query->where('product_langs.name','like',"%{$name}%");
				}

				if( $sku = _var('sku') ){
					$query->where('products.sku','like',"%{$sku}%");
				}

				if( isset($_GET['online']) ){
					if( $_GET['online'] != '-1' ){
						$query->where('products.online',$_GET['online']);
					}
				}
				
				if( $type = _var('type') ){
					$query->where('products.type',$type);
				}
				

				if( $category_id = _var('product_category_id') ){
					$query->where('products.product_category_id',$category_id);
				}
		
				if( $id = _var('id') ){
					$query->where('products.id',$id);
				}
			})
			->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
				if( in_array($field,['id','name'])){
					$query->orderBy($field,$order);
				}
			})
			->display();
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
			})->leftJoin('product_quantities as q', function($join2){
				$join2->on('q.product_id','=','p.id');
				$join2->where('warehouse_id',1);
			})
			->where('parent_id',$product->id)
			->select(['p.id','p.sku','p.ean','p.upc','l.name','q.quantity','p.images','p.online'])->get()->toArray();

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
				$this->createTableRowAttribute($form,$row);
			}
		}
	}

	function createTableRowAttribute(FormHelper $form, object $row, string $mode= 'append'){
		$fields = [
			"product_child_{$row->id}_id" => [
				'type' => 'hidden'
			],
			"product_child_{$row->id}_sku" => [
				'type' => 'text',
				'label' => 'sku',
				'validation' => ['required']
			],
			"product_child_{$row->id}_qty" => [
				'type' => 'text',
				'label' => _translate('products.form.fields.quantity','catalogo'),
				'validation' => ['required']
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
			"product_child_{$row->id}_qty" => $row->quantity,
			"product_child_{$row->id}_id" => $row->id,
			"product_child_{$row->id}_sku" => $row->sku,
			"product_child_{$row->id}_ean" => $row->ean,
			"product_child_{$row->id}_upc" => $row->upc,
		];
		$fragment = new Fragment('variation_product_'.$row->id,$this);
		
		$url_edit_variation = "index.php?mod=catalogo&amp;ctrl=Product&amp;action=edit_variation&amp;id=".$row->id;
		$edit_title = _translate('products.form.edit_variation','catalogo');
		$images = unserialize($row->images);
		$image = '';
		if( okArray($images) ){
			$image = _MARION_BASE_URL_."img/".$images[0]."/sm/preview.png";
		}
		$online_status = $row->online? "<label class='label label-success'>".strtoupper(_translate('online','catalogo'))."</label>": "<label class='label label-danger'>".strtoupper(_translate('offline','catalogo'))."</label>";
		$fragment->setTemplate("
				<fragment>
						<tr>
							<td><img style='width: 50px; height: auto;' src='{$image}'/></td>
							<td>{$row->combination_name}</td>
							<td>{$online_status}</td>
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
								<field name='product_child_{$row->id}_qty' />
							</td>
							<td>
								<div style='display: flex; justify-content: flex-end; align-items: center;'>
									<button class='btn btn-info' type='button' onclick='javascript:openPopup(\"{$edit_title}\",\"{$url_edit_variation}\")'> <i class='fa fa-pencil'> </i> </button>
									<button class='btn btn-danger' type='button' onclick='javascript:formEvent(\"delete_variation\",{$row->id})'> <i class='fa fa-trash-o'> </i> </button>
								</div>
							</td>
						</tr>
				</fragment>
			
		");
		$fragment->setFields($fields);
		$fragment->setDataForm($data);
		if( $mode == 'append' ){
			$form->addFragment('rows',$fragment);
		}else{
			$form->replaceFragment('variation_product_'.$row->id,$fragment);
		}
		
	}


	/**
	 *  Create Fragment for product attributes
	 *
	 * @param Product $product
	 * @param Product|null $child
	 * @return Fragment
	 */
	function createAttributesProductFragment(Product $product, ?array $attributes = []): Fragment{
		$template = $product->template();
		$select = $template->getAttributeWithValues();


		
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
			$data = [];
			foreach($attributes as $attribute => $value){
				$data['attribute_'.$attribute] = $value;
			}
			$fragment->setDataForm($data);
		}
        return $fragment;

	}



	private function createRelatedProductsBox(int $index): Fragment{
		
		$fragment = new Fragment('related_products_box_'.$index,$this);
        $fragment->setTemplate("
            <fragment>
					<div class='panel panel-info'>
					    <div class='panel-heading'>titolo</div>
						<div class='panel-body'>
							<row>
								<col>
									<field name='related_products_{$index}_type'/>
								</col>
								<col>
									<field name='related_products_{$index}_value'/>
								</col>
							</row>
						</div>
					</div>
            </fragment>
        ");
		$fragment->setFields([
			"related_products_{$index}_type" => [
				'type' => 'select',
				'label' => 'Tipo',
				'options' => [
					'random' => 'prodotti casuali',
					'specifc_product' => 'prodotto specifico'
					]
				],
				"related_products_{$index}_value" => [
					'type' => 'autocomplete',
					'label' => 'Valore',
					'options' => [
						1 => 'bello',
						2 => 'figo',
						3 => 'belloissimo',
					]
				]
		]);

		return $fragment;
	}


}



?>