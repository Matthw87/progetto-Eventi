<?php

use Catalogo\Category;
use Marion\Support\ListWrapper\{DataSource, SortableListHelper,ListActionRowButton};
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Controllers\ListAdminController;
use Marion\Support\Form\FormHelper;

class CategoryController extends ListAdminController{
	public $_auth = 'catalog';
	public $url_type;



	function setMedia(): void
	{
		parent::setMedia();
		$this->registerJS('assets/js/link_menu_frontend.js');
	}

	/**
	 * Display form
	 *
	 * @return void
	 */
	function displayForm()
	{

		$this->setMenu('manage_sections');
		$this->setTitle(_translate('categories.form.title','catalogo'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'parent_id' => [
				'type' => 'select',
                'label' => _translate('categories.form.fields.parent','catalogo'),
				'options' => function(){
					return $this->getTree();
				}
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('categories.form.fields.name','catalogo'),
				'multilang' => true,
				'validation'=> 'required|max:100',
			],
			'description' => [
				'type' => 'editor',
                'label' => _translate('categories.form.fields.description','catalogo'),
				'multilang' => true,
				'validation'=> 'max:500',
			],
			'order_view' => [
				'type' => 'text',
                'label' => _translate('categories.form.fields.order_view','catalogo'),
				'validation'=> 'required|max:100',
			],
			'online' => [
				'type' => 'switch',
                'label' =>  _translate('categories.form.fields.online','catalogo')
			],
			'images' => [
				'type' => 'images',
                'label' => _translate('categories.form.fields.images','catalogo')
			],
			'attachments' => [
				'type' => 'files',
                'label' => _translate('categories.form.fields.attachments','catalogo')
			],
			'product_category_related' => [
				'type' => 'multiselect',
                'label' => _translate('categories.form.fields.related_sections','catalogo'),
				'options' => function(){
					$sections = Category::getAll(_MARION_LANG_);
					$toreturn = [];
					if( okArray($sections) ){
						foreach($sections as $k => $v){
							$toreturn[$k] = $v;
						}
					}
					return $toreturn;
				}
			],
			'slug' => [
				'type' => 'text',
				'multilang' => true,
                'label' => _translate('categories.form.fields.pretty_url','catalogo')
			],
			'meta_title' => [
				'type' => 'text',
				'multilang' => true,
                'label' => _translate('categories.form.fields.meta_title','catalogo')
			],
			'meta_description' => [
				'type' => 'textarea',
				'multilang' => true,
                'label' => _translate('categories.form.fields.meta_description','catalogo')
			],
		];

		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('catalogo_category',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'catalogo/templates/admin/forms/category.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso

				if( !$form->isSubmitted() ){
					
					if($action != 'add'){
						$obj = Category::withId(_var('id'));
						if( is_object($obj)){
							$data = $obj->getDataForm();
							if( $action == 'duplicate' ){
								unset($data['id']);
							}
							$form->formData->data = $data;
						}
					}else{
						$form->formData->data = [
							'order_view' => 10,
							'online' => 1
						];
					}
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				
                if( $action == 'edit' ){
					$obj = Category::withId($data['id']);
				}else{
					$obj = Category::create();
				}
				$obj->setRelatedCategories($data['product_category_related']);
				//debugga($data);exit;
				$res = $obj->set($data)->save();
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
					$form->ctrl->errors[] = _translate("error_".$res);
				}

            })->setFields($fields);

        $form->display();
	}



	function displayList()
	{
		$this->setTitle(_translate('categories.list.title','catalogo'));
		$this->setMenu('manage_sections');

		if( _var('updated') ){
			$this->displayMessage(_translate('categories.messages.updated','catalogo'));
		}
		if( _var('created') ){
			$this->displayMessage(_translate('categories.messages.created','catalogo'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('categories.messages.deleted','catalogo'));
		}

		$dataSource = (new DataSource('product_categories'));
		$dataSource->queryBuilder()
			->leftJoin('product_category_langs',"product_category_langs.product_category_id","=","product_categories.id")
			->where('product_category_langs.lang',_MARION_LANG_);
		$dataSource->addFields(['product_categories.id','product_categories.parent_id','product_category_langs.name','product_categories.online']);
		
		SortableListHelper::create('catalogo_section',$this)
			->setDataSource($dataSource)
			->setRowParent('parent_id')
			->setMaxDepth(4)
			->setContent(function($item){
				$content = $item->name;
				if( $item->online ){
					$content .= " <span class='label label-success'>ONLINE</span>";
				}else{
					$content .= " <span class='label label-danger'>OFFLINE</span>";
				}
				return $content;
			})->addActionRowButton(
				(new ListActionRowButton('edit'))
				->setIcon('fa fa-edit')
				->setText(_translate('list.edit'))
				->setUrl(function($item){
                    return 'index.php?ctrl=Category&mod=catalogo&action=edit&id='.$item->id;
				})
			)->addActionRowButton(
				(new ListActionRowButton('duplicate'))
				->setIcon('fa fa-copy')
				->setText(_translate('list.duplicate'))
				->setUrl(function($item){
                    return 'index.php?ctrl=Category&mod=catalogo&action=duplicate&id='.$item->id;
				})
			)->addActionRowButton(
				(new ListActionRowButton('delete'))
				->setConfirm(true)
				->setConfirmMessage(function($item){
					return _translate(['categories.messages.confirm_delete_message',$item->name],'catalogo');
				})
				->setIcon('fa fa-trash-o')
				->setText(_translate('list.delete'))
			)->onAction(function($action,$id){
				switch($action){
					case 'delete':
						$this->delete($id);
						break;
				}
			})->onChange(function($ids){
                $order = 1;
				foreach($ids as $v){
                    $update = [
						'parent_id' => null,
						'order_view' => $order
					];
					$order++;
					DB::table('product_categories')
					->where("id", $v['id'])
					->update($update);
					if( isset($v['children']) ){
						if( okArray($v['children']) ){
							foreach( $v['children'] as  $v1 ){
								$update = [
									'parent_id' => $v['id'],
									'order_view' => $order
								]; 
								$order++;
								DB::table('product_categories')
									->where("id", $v1['id'])
									->update($update);
								if( isset($v1['children']) ){
									foreach( $v1['children'] as  $v2 ){
										$update = [
											'parent_id' => $v1['id'],
											'order_view' => $order
										]; 
										$order++;
										DB::table('product_categories')
											->where("id", $v2['id'])
											->update($update);
										if( isset($v2['children']) ){
											foreach( $v2['children'] as  $v3 ){
												$update = [
													'parent_id' => $v2['id'],
													'order_view' => $order
												]; 
												$order++;
												DB::table('product_categories')
													->where("id", $v3['id'])
													->update($update);
											}
										}
									}
								}
							}
						}
					}
                    

                }
			})->display();
	}

	


	/**
	 * restituisce l'albero delle voci di menu
	 *
	 * @return array
	 */
	private function getTree(): array{
	
		$links = Category::prepareQuery()->get();
		$tree = Category::buildTree($links);
		
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
		
		uasort($toreturn,function($a,$b){
			 if ($a == $b) {
				return 0;
			}
			return ($a < $b) ? -1 : 1;
		});
		
		$_toreturn = [
			null => _translate('general.select..')
		];
		foreach($toreturn as $k => $v){
			$_toreturn[$k] = $v;
		}
		
		
		return $_toreturn;
		
	}



	/**
	 * delete item
	 *
	 * @param int $id
	 * @return void
	 */
	private function delete(int $id){
		DB::table('product_categories')->where('id',$id)->delete();
		$this->displayMessage(_translate('linkMenuFrontend.form.messages.deleted'));
	}

	/**
	 * active item
	 *
	 * @param integer $id
	 * @return void
	 */
	private function active(int $id){
		DB::table('product_categories')->where('id',$id)->update(array('visibility'=>1));		
		$this->displayMessage(_translate('linkMenuFrontend.list.messages.activated'));
	}

	/**
	 * disable item
	 *
	 * @param integer $id
	 * @return void
	 */
	private function disable(int $id){
		DB::table('product_categories')->where('id',$id)->update(array('visibility'=>0));		
		$this->displayMessage(_translate('linkMenuFrontend.list.messages.disabled'));
	}
}



?>