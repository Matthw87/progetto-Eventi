<?php
use Catalogo\Tag;
use Marion\Controllers\ListAdminController;
use Marion\Support\ListWrapper\ListHelper;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\DataSource;


class TagController extends ListAdminController{
	public $_auth = 'catalog';

	/**
	 * Display Form
	 *
	 * @return void
	 */
	function displayForm(){
		$this->setMenu('tagProduct');
		$this->setTitle(_translate('tags.form.title','catalogo'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'label' => [
				'type' => 'text',
                'label' => _translate('tags.form.fields.label','catalogo'),
				'validation'=> 'required|max:100',
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('tags.form.fields.name','catalogo'),
				'validation'=> 'required|max:100',
				'multilang' => true
			],
			'note' => [
				'type' => 'textarea',
                'label' => _translate('user_categories_management.form.fields.note'),
				'validation'=> 'max:300',
				'multilang' => true
			]
		];

		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('catalogo_tag_product',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'catalogo/templates/admin/forms/tag.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso
				if( $form->isSubmitted() ){

					
				}else{
					if($action != 'add'){
						$obj = Tag::withId(_var('id'));
						if( is_object($obj)){
							$data = $obj->getDataForm();
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
					$obj = Tag::withId($data['id']);
				}else{
					$obj = Tag::create();
				}
				
				
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
			$this->setMenu('tagProduct');
			$this->setTitle(_translate('tags.list.title','catalogo'));
			if( _var('updated') ){
				$this->displayMessage(_translate('tags.messages.updated','catalogo'));
			}
			if( _var('created') ){
				$this->displayMessage(_translate('tags.messages.created','catalogo'));
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
					'name' => _translate('tags.form.fields.label','catalogo'),
					'field_value' => 'label',
					'sortable' => true,
					'sort_id' => 'label',
					'searchable' => true,
					'search_name' => 'label',
					'search_value' => _var('label'),
					'search_type' => 'input',
				],
				[
					'name' => _translate('tags.form.fields.name','catalogo'),
					'field_value' => 'name',
					'sortable' => true,
					'sort_id' => 'name',
					'searchable' => true,
					'search_name' => 'name',
					'search_value' => _var('name'),
					'search_type' => 'input',
				],
				[
					'name' => _translate('tags.list.url','catalogo'),
					'function_type' => 'row',
					'function' => function($row){
						$url = _MARION_BASE_URL_."catalogo/tag/".$row->label;
						return "<a href='{$url}' target='_blank' class='btn btn-default btn-sm'><i class='fa fa-link'></i></a>";
					}
				],

			];

			$dataSource = (new DataSource('product_tags'))
				->addFields(['product_tag_langs.name','product_tags.id','product_tags.label']);
			$dataSource->queryBuilder()
			->leftJoin('product_tag_langs','product_tag_langs.product_tag_id','=','product_tags.id')
			->where('product_tag_langs.lang',_MARION_LANG_);


			ListHelper::create('catalogo_tag_product',$this)
				->setFieldsFromArray($fields)
				->enableExport(true)
				//->setPerPage($limit)
				->setExportTypes(['pdf','csv','excel'])
				->enableBulkActions(true)
				->enableSearch(true)
				->setFieldsFromArray($fields)
				->addEditActionRowButton()
				->addCopyActionRowButton()
				->enableBulkActions(false)
				->addDeleteActionRowButton(function($row){
					return _translate(['tags.messages.confirm_delete_message',$row->name],'catalogo');
				})
				->onDelete(function($id){
					//eliminazione del tag
					$object = Tag::withId($id);
					if( is_object($object)){
						$object->delete();
						$this->displayMessage(_translate('tags.messages.deleted','catalogo'));
					}
				})
				->setDataSource($dataSource)
				->onSearch(function(\Illuminate\Database\Query\Builder $query){
					if( $id = _var('id') ){
						$query->where('id',$id);
					}
					if( $label = _var('label') ){
						$query->where('label',$label);
					}
				})
				->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
					if( in_array($field,['label','id','name'])){
						$query->orderBy($field,$order);
					}
				})->display();
		
	}

}



?>