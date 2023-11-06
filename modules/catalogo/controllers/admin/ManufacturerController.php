<?php
use Catalogo\Manufacturer;
use Catalogo\Category;
use Marion\Controllers\ListAdminController;
use Marion\Support\ListWrapper\ListHelper;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Controllers\Elements\UrlButton;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\DataSource;
use Marion\Support\ListWrapper\ListActionRowButton;

class ManufacturerController extends ListAdminController{
	public $_auth = 'catalog';


    function displayContent()
    {
        $add = false;
        switch($this->getAction()){
            case 'add_product':
                $manufacturer_id = _var('manufacturer_id');
                $product_id = _var('product_id'); 
                DB::table('products')->where('id',$product_id)->update(['product_manufacturer_id' => $manufacturer_id]);
                $url_back = _var('url_back');
                header('Location: '.$url_back);
                exit;
                break;
            case 'remove_product':
                $manufacturer_id = _var('manufacturer_id');
                $product_id = _var('product_id');
                DB::table('products')->where('id',$product_id)->where('product_manufacturer_id',$manufacturer_id)->update(['product_manufacturer_id' => null]);
                $url_back = _var('url_back');
                header('Location: '.$url_back);
                exit;
                break;
            case 'products':
                $this->displayProducts($add);
                break;
            case 'add_products':
                $add = true;
            case 'products':
                $this->displayProducts($add);
                break;
        }
    }


    private function displayProducts($add=false): void{
        $manufacturer_id = _var('manufacturer_id');
        $obj = Manufacturer::withId($manufacturer_id);
        $this->setMenu('manufacturer');
        
       
        
        if( !$add ){
            $this->setTitle(_translate(['manufacturers.product_list.title',$obj->get('name')],'catalogo'));
            $this->addToolButton(
                (new UrlButton('back'))
                ->setIcon('fa fa-arrow-left')
                ->setClass('btn btn-default')
                ->setText(_translate('list.back'))
                ->setUrl($this->getUrlScript()."&action=list")
            );
            $this->addToolButton(
                (new UrlButton('add_produt'))
                ->setIcon('fa fa-plus')
                ->setClass('btn btn-principale')
                ->setText(_translate('manufacturers.product_list.add_products','catalogo'))
                ->setUrl($this->getUrlScript()."&action=add_products&manufacturer_id=".$manufacturer_id)
            );
        }else{
            $this->setTitle(_translate(['manufacturers.product_list.add_products_to',$obj->get('name')],'catalogo'));
            $this->addToolButton(
                (new UrlButton('close'))
                ->setIcon('fa fa-times')
                ->setClass('btn btn-principale')
                ->setText(_translate('manufacturers.product_list.close','catalogo'))
                ->setUrl($this->getUrlScript()."&action=products&manufacturer_id=".$manufacturer_id)
            );
        }
        

        $_categories = Category::getAll(_MARION_LANG_);
		$category_select = [];
		$category_select[0] = _translate('general.select..');
		if( okArray($_categories) ){
			foreach($_categories as $k => $v){
				$category_select[$k] = $v;
			}
		}
		
        $fields = [
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
                'name' => _translate('manufacturers.form.fields.name','catalogo'),
                'field_value' => 'name',
                'sortable' => true,
                'sort_id' => 'name',
                'searchable' => true,
                'search_name' => 'name',
                'search_value' => _var('name'),
                'search_type' => 'input',
            ],
            [
                'name' => _translate('products.form.fields.category','catalogo'),
                'function_type' => 'row',
                'function' => function($row) use($category_select){
                    if( array_key_exists($row->product_category_id,$category_select) ) return $category_select[$row->product_category_id];
                    return '';
                },
                'sortable' => true,
                'sort_id' => 'section',
                'searchable' => true,
                'search_name' => 'section',
                'search_value' => _var('section'),
                'search_type' => 'select',
                'search_options' => $category_select
            ],
            [
                'name' => _translate('manufacturers.form.fields.online','catalogo'),
                'function_type' => 'row',
                'function' => function($row){
                   if( $row->online ) return "<span class='label label-success'>".strtoupper(_translate('general.yes'))."</span>";
                   return "<span class='label label-danger'>".strtoupper(_translate('general.no'))."</span>";
                },
                'sortable' => true,
                'sort_id' => 'active',
                'searchable' => false,
                'search_name' => 'active',
                'search_value' => _var('active'),
                'search_type' => 'active',
            ],

        ];

        
        $dataSource = (new DataSource('products'))
				->addFields(['product_langs.name','products.id','products.product_category_id','products.online']);
        $dataSource->queryBuilder()
        ->leftJoin('product_langs','product_langs.product_id','=','products.id')
        ->where('product_langs.lang',_MARION_LANG_)
        ->where('products.deleted',0);

        if( $add ){
            $dataSource->queryBuilder()->where(function($condition) use ($manufacturer_id){
                $condition->whereNull('product_manufacturer_id');
                $condition->orWhere('product_manufacturer_id','<>',$manufacturer_id);
            });
            
        }else{
            $dataSource->queryBuilder()->where('product_manufacturer_id',$manufacturer_id);
        }
        

		ListHelper::create('catalogo_manufacturer_association',$this)
            ->setDataSource($dataSource)
            ->setFieldsFromArray($fields)
            ->enableExport(true)
            ->setExportTypes(['pdf','csv','excel'])
            ->enableBulkActions(true)
            ->enableSearch(true)
            ->setFieldsFromArray($fields)
            ->enableBulkActions(false)
            ->addActionRowButton(
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
                    $query->where('product_category_id',$section);
                }
        
                if( $id = _var('id') ){
                    $query->where('id',$id);
                }
            })
            ->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
                if( in_array($field,['id','name','product_category_id'])){
                    $query->orderBy($field,$order);
                }
            })->display();
        
    }

	/**
	 * Display Form
	 *
	 * @return void
	 */
	function displayForm(){
		$this->setMenu('manufacturer');
		$this->setTitle(_translate('manufacturers.form.title','catalogo'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('manufacturers.form.fields.name','catalogo'),
				'validation'=> 'required|max:100',
				'multilang' => true
			],
            'description' => [
				'type' => 'editor',
                'label' => _translate('manufacturers.form.fields.description','catalogo'),
				'validation'=> 'max:500',
				'multilang' => true
			],
            'image' => [
				'type' => 'image',
                'label' => _translate('manufacturers.form.fields.image','catalogo')
			],
            'online' => [
				'type' => 'switch',
                'label' => _translate('manufacturers.form.fields.online','catalogo')
			],
		];

		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('catalogo_manufacturer',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'catalogo/templates/admin/forms/manufacturer.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso
				if( $form->isSubmitted() ){

					
				}else{
					if($action != 'add'){
						$user = Manufacturer::withId(_var('id'));
						if( is_object($user)){
							$data = $user->getDataForm();
                            if( $action == 'duplicate' ){
                                unset($data['id']);
                            }
							$form->formData->data = $data;
						}
					}
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				
                if( $action == 'edit' ){
					$user = Manufacturer::withId($data['id']);
				}else{
					$user = Manufacturer::create();
				}
				
				
				$res = $user->set($data)->save();
				if( is_object($res) ){
					$params = [];
					if( $action == 'edit' ){
						$params['updated'] = 1;
					}else{
						$params['created'] = 1;
					}
					if( $form->ctrl instanceof ListAdminController ){
						$form->ctrl->redirectTolist($params);
					}
					
					
				}else{
					$form->ctrl->errors[] = _translate('error');
				}

            })->setFields($fields);

        $form->display();
	}


	/**
	 * display list
	 *
	 * @return void
	 */
	function displayList(){
			$this->setMenu('manufacturer');
			$this->setTitle(_translate('manufacturers.list.title','catalogo'));
			if( _var('updated') ){
				$this->displayMessage(_translate('manufacturers.messages.updated','catalogo'));
			}
			if( _var('created') ){
				$this->displayMessage(_translate('manufacturers.messages.created','catalogo'));
			}

			$fields = [
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
					'name' => _translate('manufacturers.form.fields.name','catalogo'),
					'field_value' => 'name',
					'sortable' => true,
					'sort_id' => 'name',
					'searchable' => true,
					'search_name' => 'name',
					'search_value' => _var('name'),
					'search_type' => 'input',
                ],
                [
					'name' => '',
					'function_type' => 'row',
					'function' => function($row){
                        $url = $this->getUrlScript()."&action=products&manufacturer_id={$row->id}&url_back=".urlencode($this->getUrlCurrent());
                        
                        
                        $tot = DB::table('products')
                            ->where('deleted',0)
                            ->where('product_manufacturer_id',$row->id)
                            ->count();


                        $num_products_label = _translate(['manufacturers.list.product_number',$tot],'catalogo');
                        return "<a  href='{$url}' class='btn btn-primary'>{$num_products_label}</a>";
                    }
				]

			];
			$dataSource = (new DataSource('product_manufacturers'))
				->addFields(['product_manufacturer_langs.name','product_manufacturers.id']);
			$dataSource->queryBuilder()
			->leftJoin('product_manufacturer_langs','product_manufacturer_langs.product_manufacturer_id','=','product_manufacturers.id')
			->where('product_manufacturer_langs.lang',_MARION_LANG_);

			ListHelper::create('catalogo_manufacturer',$this)
                ->setDataSource($dataSource)
				->setFieldsFromArray($fields)
				->enableExport(true)
				->setExportTypes(['pdf','csv','excel'])
				->enableBulkActions(true)
				->enableSearch(true)
				->setFieldsFromArray($fields)
				->addEditActionRowButton()
				->addCopyActionRowButton()
				->enableBulkActions(false)
				->addDeleteActionRowButton(function($row){
					return _translate(['manufacturers.messages.confirm_delete_message',$row->name],'catalogo');
				})
				->onDelete(function($id){
					//eliminazione del tag
					$object = Manufacturer::withId($id);
					if( is_object($object)){
						$object->delete();
						$this->displayMessage(_translate('manufacturers.messages.deleted','catalogo'));
					}
					
					
				})
                ->onSearch(function(\Illuminate\Database\Query\Builder $query){
					if( $id = _var('id') ){
						$query->where('id',$id);
					}
					if( $name = _var('name') ){
						$query->where('name','like',"%$name%");
					}
				})
				->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
					if( in_array($field,['id','name'])){
						$query->orderBy($field,$order);
					}
                })->display();
		
	}


}



?>