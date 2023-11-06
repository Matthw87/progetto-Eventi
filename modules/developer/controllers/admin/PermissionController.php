<?php
use Marion\Controllers\ListAdminController;
use Marion\Entities\Permission;
use Marion\Support\ListWrapper\ListHelper;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\DataSource;

class PermissionController extends ListAdminController{
	public $_auth = 'superadmin';


    /**
	 * Display List Forms
	 *
	 * @return void
	 */
	function displayList(): void{
		
        $this->setMenu('developer_permissions');
		$this->setTitle(_translate('permissions','developer'));
		if( _var('updated') ){
			$this->displayMessage(_translate('permission_updated','developer'));
		}
		if( _var('created') ){
			$this->displayMessage(_translate('permission_added','developer'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('permission_deleted','developer'));
		}

		$fields = array(
            array(
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',
			),
			array(
				'name' => 'Label',
				'field_value' => 'label',
				'sortable' => true,
				'sort_id' => 'label',
				'searchable' => true,
				'search_name' => 'label',
				'search_value' => _var('label'),
				'search_type' => 'input',
            ),
            array(
				'name' => 'Name',
				'field_value' => 'name',
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
            ),
            array(
				'name' => 'Description',
				'field_value' => 'description',
			
			)


		);

		$dataSource = new DataSource('permissions');
		$dataSource->addFields(['permissions.id','permissions_langs.description','permissions.label','permissions_langs.name']);
		$dataSource->queryBuilder()
                ->join('permissions_langs','permissions_langs.permission_id','=','permissions.id')
                ->where('permissions_langs.locale',_MARION_LANG_);
		

		ListHelper::create('developer_permission',$this)
			->setDataSource($dataSource)
			->enableExport(true)
			->setExportTypes(['pdf','csv','excel'])
			->enableBulkActions(true)
			->enableSearch(true)
			->setFieldsFromArray($fields)
			->addEditActionRowButton()
			->addCopyActionRowButton()
			->addDeleteActionRowButton(function($row){
				return _translate(['confirm_delete_permission',$row->name],'developer');
			})
			->onDelete(function($id){
				//eliminazione dell'oggetto
                $obj = Permission::withId($id);
				if( $obj ){
					$obj->delete();
				}
				$this->displayMessage(_translate('permission_deleted','developer'));
				
			})
			->onSearch(function(\Illuminate\Database\Query\Builder $query){
				if( $name = _var('nome') ){
					$query->where('nome','like',"%{$name}%");
				}
				if( $id = _var('codice') ){
					$query->where('codice',$id);
				}
			})
			->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
				if( in_array($field,['id','name','description','label'])){
					$query->orderBy($field,$order);
				}
			})->enableBulkActions(false)
			->display();
			
	}

    /**
	 * Display form
	 *
	 * @return void
	 */
	function displayForm(): void
	{

        $this->setMenu('developer_permissions');
		$this->setTitle(_translate('permission','developer'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'label' => [
				'type' => 'text',
                'label' => 'etichetta',
				'validation'=> 'required|max:100|regex:^([A-Za-z\_]+)$'
			],
            'name' => [
				'type' => 'text',
                'label' => 'nome',
                'multilang' => true,
				'validation'=> 'required|max:100',
			],
			'description' => [
				'type' => 'textarea',
                'multilang' => true,
                'label' => 'descrizione',
				'validation'=> 'max:200',
			]
		];

		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('developer_permission',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'developer/templates/admin/forms/permission.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso

				if( !$form->isSubmitted() ){
					
					if($action != 'add'){
						$obj = Permission::withId(_var('id'));
						if($obj ){
							$form->formData->data = (array)$obj->getDataForm();
						}

					}
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				
				$params = [];
                if( $action == 'edit' ){
                    $obj = Permission::withId($data['id']);
					$params['updated'] = 1;
				}else{
                    $data['scope'] = 'admin';
					$params['created'] = 1;
					$obj = Permission::create();
					
				}
                $obj->set($data)->save();
				if( $form->ctrl instanceof ListAdminController ){
					$form->ctrl->redirectTolist($params);
				}
				
				

            })->setFields($fields);

        $form->display();
	}

    
}