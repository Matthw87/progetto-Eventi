<?php
use Marion\Entities\Profile;
use Marion\Entities\User;
use Marion\Entities\Permission;
use Marion\Core\Marion;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Controllers\Elements\UrlButton;
use Marion\Controllers\ListAdminController;
use Marion\Support\ListWrapper\{DataSource, ListHelper,ListActionRowButton};
use Marion\Support\Form\FormHelper;
class ProfileController extends ListAdminController{
	public $_auth = 'user_management';
	
	/**
	 * Display List Profiles
	 *
	 * @return void
	 */
	function displayList(): void{
		
		$this->setMenu('profiles');
		if( _var('updated') ){
			$this->displayMessage(_translate('profiles_management.form.messages.updated'));
		}
		if( _var('created') ){
			$this->displayMessage(_translate('profiles_management.form.messages.inserted'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('profiles_management.form.messages.deleted'));
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
				'name' => _translate('profiles_management.list.name'),
				'field_value' => 'name',
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			),
			'users' => array(
				'name' => '',
				'function_type' => 'row',
				'function' => function($row){
					$cont = DB::table('users')->where("profile_id",$row->id)->count();
					return "<a href='".$this->getUrlScript()."&action=users&id_profile={$row->id}' class='btn btn-sm btn-default'><i class='fa fa-users'></i> {$cont} "._translate('profiles_management.list.users')."</a>";
				}
			)
		);
		$this->setTitle(_translate('profiles_management.list.title'));
		
			
		$dataSource = new DataSource('profiles');
		$dataSource->addFields(['profiles.id','profiles.name']);

		ListHelper::create('core_profile',$this)
			->setDataSource($dataSource)
			->enableExport(true)
			->setExportTypes(['pdf','csv','excel'])
			->enableBulkActions(true)
			->enableSearch(true)
			->setFieldsFromArray($fields)
			->addEditActionRowButton()
			->addCopyActionRowButton()
			->addDeleteActionRowButton(function($row){
				return _translate(['profiles_management.list.confirm_delete_message',$row->name]);
			})
			->onDelete(function($id){
				//eliminazione dell'utente
				$profile = Profile::withId($id);
				$user = Marion::getUser();

				if( is_object($profile)){
					if($profile->superadmin ){
						if($user->auth('superadmin')){
							$this->errors[] = "Il profilo <b>{$profile->get('name')}</b> non può essere eliminato";
							$this->displayMessage(_translate('profiles_management.errors.profile_superadmin'));
						}
					}else{
						$profile->delete();
						$this->displayMessage(_translate('profiles_management.form.messages.deleted'));
					}
					
				}
				
			})->enableBulkActions(false)
			->onSearch(function(\Illuminate\Database\Query\Builder $query){
				
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
			})->display();
			
	}

	/**
	 * Display profile's user list
	 *
	 * @return void
	 */
	function displayUsers(): void{
		$id_profile = _var('id_profile');
		$action = $this->getAction();
		$obj = Profile::withId($id_profile);
		if( is_object($obj) ){
			if( $action == 'users'){
				$title = _translate(['profiles_management.list.users_list',$obj->get('name')]);
				$this->addToolButton(
					(new UrlButton('add_users'))
					->setText(_translate('profiles_management.list.add_users_btn_text'))
					->setIcon('fa fa-plus')
					->setUrl($this->getUrlScript()."&action=add_user&id_profile=".$obj->id)
				)->addToolButton(
					(new UrlButton('backs'))
					->setText(_translate('list.back'))
					->setIcon('fa fa-arrow-left')
					->setUrl($this->getUrlScript()."&action=list")
				);
			}else{
				$title = _translate(['profiles_management.list.add_users',$obj->get('name')]);
				$this->addToolButton(
					(new UrlButton('add_users'))
					->setText(_translate('profiles_management.list.back_to_users'))
					->setIcon('fa fa-arrow-left')
					->setUrl($this->getUrlScript()."&action=users&id_profile=".$obj->id)
				);
			}
		}
		$this->setTitle($title);
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
				'name' => _translate('profiles_management.list.name'),
				'function_type' => 'row',
				'function' => function($row){
					return $row->name." ".$row->surname;
				},
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			),

		);

		$dataSource = new DataSource('users');
		$dataSource->addFields(['users.id','users.name','users.surname']);

		if( $action == 'users'){
			$dataSource->queryBuilder()->where('profile_id',$id_profile);
		}else{
			$dataSource->queryBuilder()->where(function($condition){
				$condition->whereNull('profile_id')
						->orWhere('profile_id',0);
			});
		}
		$list = ListHelper::create('core_profile',$this)
			->setDataSource($dataSource)
			->enableExport(true)
			->setExportTypes(['pdf','csv','excel'])
			->enableBulkActions(false)
			->enableSearch(true)
			->setFieldsFromArray($fields);
		if( $action == 'users'){
			$list->addDeleteActionRowButton(function($row){
				return _translate(['profiles_management.list.confirm_remove_user_message',$row->name,$row->surname]);
			})
			->onDelete(function($id){
				//eliminazione dell'utente
				$profile_id = _var('id_profile');
				$profile = Profile::withId($profile_id);
				if( is_object($profile) ){
					$profile->removeUser($id);
				}
				$this->displayMessage(_translate(['profiles_management.list.removed_user',$profile->name]));
			});
		}else{
			$list->addActionRowButton(
					(new ListActionRowButton('add_user'))
					->setText(_translate('list.add'))
					->setIcon('fa fa-plus')
			)->onAction(function($action,$id){
				if( $action  == 'add_user'){
					$profile_id = _var('id_profile');
					
					$user = User::withId($id);
					
					$profile = Profile::withId($profile_id);
					if( is_object($user) ){
						$user->profile_id = $profile_id;
						$user->save();
						
					}
					$this->displayMessage(_translate(['profiles_management.list.added_user',$user->name,$user->surname,$profile->name]));
				}

			});

		}

		$list->onSearch(function(\Illuminate\Database\Query\Builder $query){
				
			if( $name = _var('name') ){
				$query->where(function($condition) use ($name){
					$condition->where('name','like',"%{$name}%");
					$condition->orWhere('surname','like',"%{$name}%");
				});
			}
			if( $id = _var('id') ){
				$query->where('id',$id);
			}
		})->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
			if( in_array($field,['id','name'])){
				if( $field == 'name' ){
					$query->orderBy('name',$order);
					$query->orderBy('surname',$order);
				}else{
					$query->orderBy($field,$order);
				}
			}
		})->display();
	}
	

	/**
	 * display content
	 *
	 * @return void
	 */
	function displayContent(): void{
		$this->setMenu('profiles');
		$action = $this->getAction();
		switch($action){
			case 'add_user':
			case 'users':
				$this->displayUsers();
				break;
		}
	}

	function displayForm(){
		$this->setMenu('profiles');
		$this->setTitle(_translate('profiles_management.form.title'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('users_management.form.fields.name'),
				'validation'=> 'required|max:100',
			],
			'permissions' => [
				'type' => 'multiselect:tabs',
                'label' => _translate('profiles_management.form.fields.permissions'),
				'options' => function(){
					$options = [];

					$user = Marion::getUser();
					$query = Permission::prepareQuery()
						->where('active',1)
						->where('label','base','<>');
						
					if( is_object($user) && !$user->auth('superadmin') ){
						$query->where('label','admin','<>');
						$query->where('label','config','<>')->where('label','superadmin','<>');
					}
						
					$permessi = $query->orderBy('orderView')->get();
					
					foreach($permessi as $v){
						$options[$v->id] = $v->get('name');
					}
					return $options;

				}
			]
            
		];

		//prendo l'action
		$action = $this->getAction();

        $form = FormHelper::create('form_profile_admin',$this)
            ->layoutFile(_MARION_ROOT_DIR_.'backend/templates/admin/forms/form_profile.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso
				if( $form->isSubmitted() ){

					//prendo i dati sottomessi
					$data = $form->getSubmittedData();
					
				}else{
					if($action != 'add'){
						$profile = Profile::withId(_var('id'));
						if( is_object($profile)){
							$data = $profile->getDataForm();
							$form->formData->data = $data;
						}
						if( $action == 'duplicate'){
							unset($data['id']);
						}
					}
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				
                if( $action == 'edit' ){
					$profile = Profile::withId($data['id']);
				}else{
					$profile = Profile::create();
				}
				$profile->setPermissions($data['permissions']);

				$res = $profile->set($data)->save();
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
					$form->ctrl->errors[] = _translate("profiles_management.entity_errors.".$res);
				}

            })->setFields($fields);

        $form->display();
	}

	/**
	 * register media (css, js)
	 *
	 * @return void
	 */
	function setMedia(): void{
		if( $this->getAction() == 'add_user'){
			$this->registerJS('assets/js/profiles.js');
		}
	}

	/**
	 * ajax requst
	 *
	 * @return void
	 */
	function ajax(): void{
		
		$action = $this->getAction();
		$id = _var('id');
		switch($action){
			case 'add_profile_user':
				$user = User::withId($id);
				
				if( is_object($user) ){
					$user->profile_id = _var('profile');
					
					$user->save();
					$response = array(
						'result' => 'ok',
					);
				}else{
					$response = array(
						'result' => 'nak'	
					);
				}
				break;
				
		}

		echo json_encode($response);
	}

}



?>