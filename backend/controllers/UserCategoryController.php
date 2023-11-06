<?php
use Marion\Entities\UserCategory;
use Marion\Support\Form\FormHelper;
use Marion\Controllers\ListAdminController;
use Marion\Support\ListWrapper\{DataSource, ListHelper,ListActionBulkButton,ListActionRowButton};

class UserCategoryController extends ListAdminController{
	public $_auth = 'user_management';

	/**
	 * Display Form
	 *
	 * @return void
	 */
	function displayForm(): void{
		$this->setMenu('manage_user_categories');
		$this->setTitle(_translate('user_categories_management.form.title'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'label' => [
				'type' => 'text',
                'label' => _translate('user_categories_management.form.fields.label'),
				'validation'=> 'required|max:100',
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('user_categories_management.form.fields.name'),
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


       
        $form = FormHelper::create('core_user_category',$this)
            ->layoutFile(_MARION_ROOT_DIR_.'backend/templates/admin/forms/form_user_category.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso
				if( $form->isSubmitted() ){

					
				}else{
					if($action != 'add'){
						$user = UserCategory::withId(_var('id'));
						if( is_object($user)){
							$data = $user->getDataForm();
							unset($data['password']);
							$form->formData->data = $data;
						}
					}
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				
                if( $action == 'edit' ){
					$user = UserCategory::withId($data['id']);
				}else{
					$user = UserCategory::create();
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
					$form->ctrl->errors[] = _translate("user_categories_management.entity_errors.".$res);
				}

            })->setFields($fields);

        $form->display();
	}

	/**
	 * display List
	 *
	 * @return void
	 */
	function displayList(): void{
		$this->setMenu('manage_user_categories');
		$this->setTitle(_translate('user_categories_management.list.title'));

		if( _var('updated') ){
			$this->displayMessage(_translate('user_categories_management.form.messages.updated'));
		}
		if( _var('created') ){
			$this->displayMessage(_translate('user_categories_management.form.messages.inserted'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('user_categories_management.form.messages.deleted'));
		}
		
		$fields = array(
			'id' => array(
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => _var('id'),
				'search_type' => 'input',
			),
			'name' => array(
				'name' => _translate('user_categories_management.list.name'),
				'function_type' => 'row',
				'function' => function($row){
					return $row->name;
				},
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			)

		);

		

		
		$dataSource = new DataSource('user_categories');
		$dataSource->queryBuilder()->leftJoin('user_categories_langs','user_categories_langs.user_category_id','=','user_categories.id')
		->where('locale',_MARION_LANG_);
		$dataSource->addFields(
			['user_categories.id','user_categories_langs.name','user_categories.locked']
		);
		

		$list = ListHelper::create('core_user_category',$this)
			->setDataSource($dataSource)
			->enableExport(true)
			->setExportTypes(['pdf','csv','excel'])
			->enableBulkActions(true)
			->enableSearch(true)
			->setFieldsFromArray($fields)
			->addEditActionRowButton()
			->addCopyActionRowButton()
			->enableBulkActions(false)
			->addDeleteActionRowButton(function($row){
				return _translate(['user_categories_management.list.confirm_delete_message',$row->name]);
			})
			->onDelete(function($id){
				//eliminazione della categoria
				$category = UserCategory::withId($id);
				if( is_object($category)){
					
					if( $category->locked ){
						$this->displayMessage(_translate(['user_categories_management.errors.locked_category',$category->get('name')]),'danger');
					}else{
						$category->delete();
						$this->displayMessage(_translate('user_categories_management.form.messages.'));
					}
				}
				
			})->onSearch(function(\Illuminate\Database\Query\Builder $query){
				if( $name = _var('name') ){
					$query->where('name','like',"%{$name}%");
				}
		
				if( $id = _var('id') ){
					$query->where('id',$id);
				}
			})->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
				if( in_array($field,['id','name'])){
					$query->orderBy($field,$order);
				}
			});

			$list->getActionRowButton('delete')
				->setEnableFunction(function($row){
				return !$row->locked;
			});
			
			$list->display();

	}
	

}
?>