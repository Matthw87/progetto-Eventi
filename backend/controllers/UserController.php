<?php
use Marion\Entities\User;
use Marion\Core\Marion;
use Marion\Entities\Cms\Notification;
use Marion\Controllers\ListAdminController;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\{DataSource, ListHelper,ListActionBulkButton};

use Illuminate\Database\Capsule\Manager as DB;
class UserController extends ListAdminController{
	public $_auth = 'user_management';


	/**
	 * Override metodo setMedia
	 */

	function setMedia(): void{
		parent::setMedia();
		$this->registerJS($this->getBaseUrlBackend().'assets/js/user.js','end');
	}

	/**
	 * disaplay Form
	 *
	 * @return void
	 */
	function displayForm(): void{
		$this->setMenu('manage_users');
		$this->setTitle(_translate('users_management.form.title'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'email' => [
				'type' => 'email',
                'label' => 'email',
				'validation'=> 'required|max:100',
			],
			'username' => [
				'type' => 'username',
                'label' => 'username',
				'validation'=> 'required|max:100',
			],
			'password' => [
				'type' => 'password',
                'label' => 'password'
			],
			'password_confirm' => [
				'type' => 'password',
                'label' => _translate('users_management.form.fields.password_confirm')
			],
			'reset_password' => [
				'type' => 'switch',
                'label' => _translate('users_management.form.fields.reset_password')
			],
			'active' => [
				'type' => 'switch',
                'label' => _translate('users_management.form.fields.active')
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('users_management.form.fields.name'),
				'validation'=> 'required|max:100',
			],
			'surname' => [
				'type' => 'text',
				'label' => _translate('users_management.form.fields.surname'),
				'validation'=> 'required|max:100',
			],
			'phone' => [
				'type' => 'text',
                'label' => _translate('users_management.form.fields.phone'),
				'validation'=> 'phone|max:50',
			],
			'cellular' => [
				'type' => 'text',
				'label' => _translate('users_management.form.fields.cellular'),
				'validation'=> 'phone|max:50',
			],
			'city' => [
				'type' => 'text',
                'label' => _translate('users_management.form.fields.city'),
				'validation'=> 'max:200',
			],
			'postal_code' => [
				'type' => 'text',
                'label' => _translate('users_management.form.fields.postal_code'),
				'validation'=> 'max:60',
			],
			'address' => [
				'type' => 'text',
                'label' => _translate('users_management.form.fields.address'),
				'validation'=> 'max:200',
			],
			'user_category_id' => [
				'type' => 'select',
                'label' => _translate('users_management.form.fields.category'),
				'options' => function(){
					$options = [_translate('general.select..')];
					$categories = DB::table('user_categories as u')
						->leftJoin('user_categories_langs as l','l.user_category_id','=','u.id')
						->where('locale',_MARION_LANG_)
						->orderBy('name')
						->select('id','name')
						->get()
						->toArray();
					foreach($categories as $item){
						$options[$item->id] = $item->name;
					}
					return $options;
				}
			],
			'province' => [
				'type' => 'select',
                'label' => _translate('users_management.form.fields.province'),
				'options' => function(){
					$options = [_translate('general.select..')];
					$province = DB::table('provincia')
						->orderBy('sigla')
						->select('sigla','nome')
						->get()
						->toArray();
					foreach($province as $pr){
						$options[$pr->sigla] = $pr->nome;
					}
					return $options;
				}
			],
			'country' => [
				'type' => 'select',
                'label' => _translate('users_management.form.fields.country'),
				'options' => function(){
					$options = [_translate('general.select..')];
					$province = DB::table('country as c')
						->join('countryLocale as l','l.country','=','c.id')
						->where('locale',_MARION_LANG_)
						->orderBy('name')
						->select('id','name')
						->get()
						->toArray();
					foreach($province as $v){
						$options[$v->id] = $v->name;
					}
					return $options;
				}
			],
            
		];

		//prendo l'action
		$action = $this->getAction();

		if( $action == 'edit' ){
			$current_user = Marion::getUser();
			$notification = Notification::prepareQuery()
				->where('receiver',$current_user->id)
				->where('custom', _var('id'))
				->getOne();

			if( is_object($notification) ){
				$notification->set(
						array('view'=>1)
					)->save();
			}
		}
       
        $form = FormHelper::create('core_user',$this)
            ->layoutFile(_MARION_ROOT_DIR_.'backend/templates/admin/forms/form_user.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso
				if( $form->isSubmitted() ){

					//prendo i dati sottomessi
					$data = $form->getSubmittedData();
					// se è spuntato il reset password allora risulteanno obbligatori i capi: password e password_confirm
					if((isset($data['reset_password']) && $data['reset_password']) || !$data['id']){
						$form->fields['password']['validation'] = 'required';
						$form->fields['password_confirm']['validation'] = 'required';
					}
				}else{
					if($action != 'add'){
						$user = User::withId(_var('id'));
						if( is_object($user)){
							$data = $user->getDataForm();
							unset($data['password']);
							$form->formData->data = $data;
						}
					}

					if( $action != 'edit' ){
						//se sto aggiungendo un nuovo utente rimuovo lo switch per il reset password
						unset($form->fields['reset_password']);
						$form->fields['password']['validation'] = 'required';
						$form->fields['password_confirm']['validation'] = 'required';
					}
				}
            })->validate(function(FormHelper $form) use ($action){
				$data = $form->getValidatedData();
				if( $action != 'edit' || $data['reset_password'] ){
					if( $data['password'] != $data['password_confirm'] ){
						$form->errors[] = "Le password devono coincidere";
						$form->error_fields[] = 'password';
						$form->error_fields[] = 'password_confirm';
					}
				}
                
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				if($action != 'edit'){
					$data['reset_password'] = 1;
				}
				
                if( $action == 'edit' ){
					$user = User::withId($data['id']);
				}else{
					$user = User::create();
				}
				
				if( !$data['reset_password'] ){
					if( isset($data['password']) ){
						unset($data['password']);
					}
					if( isset($data['password_confirm']) ){
						unset($data['password_confirm']);
					}
					
					
				}
				if(isset($data['password']) && $data['password']) $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
			
				$res = $user->set($data)->save();
				if( is_object($res) ){
					$params = [];
					if( $action == 'edit' ){
						$params['updated'] = 1;
					}else{
						$params['inserted'] = 1;
					}
					if( $form->ctrl instanceof ListAdminController ){
						$form->ctrl->redirectTolist($params);
					}
					
				}else{
					$form->ctrl->errors[] = _translate("users_management.entity_errors.".$res);
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
		$this->setMenu('manage_users');
		$this->setTitle(_translate('users_management.list.title'));

		if( _var('updated') ){
			$this->displayMessage(_translate('users_management.form.messages.updated'));
		}
		if( _var('inserted') ){
			$this->displayMessage(_translate('users_management.form.messages.inserted'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('users_management.form.messages.deleted'));
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
				'name' => _translate('users_management.list.user'),
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
			'username' => array(
				'name' => 'username',
				'field_value' => 'username',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'username',
				'search_name' => 'username',
				'search_value' => _var('username'),
				'search_type' => 'input',
			),
			'email' => array(
				'name' => 'email',
				'field_value' => 'email',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'email',
				'search_name' => 'email',
				'search_value' => _var('email'),
				'search_type' => 'input',
			),
			'active' => array(
				'name' => _translate('users_management.list.active'),
				'function_type' => 'row',
				'function' => function($row){
					if( _var('export') ){
						if ($row->active ){
							$html = strtoupper(_translate('users_management.list.active'));
						}else{
							$html = strtoupper(_translate('users_management.list.inactive'));
						}
					}else{
						if ($row->active ){
							$html = "<span class='label label-success'  id='status_{$row->id}' style='cursor:pointer;' onclick='change_visibility({$row->id}); return false;'>".strtoupper(_translate('users_management.list.active'))."</span>";
						}else{
							$html = "<span class='label label-danger' id='status_{$row->id}' style='cursor:pointer;' onclick='change_visibility({$row->id}); return false;'>".strtoupper(_translate('users_management.list.inactive'))."</span>";
						}
					}
					return $html;
				},
				'searchable' => true,
				'search_name' => 'active',
				'search_value' => (isset($_GET['active']))? _var('active'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => _translate('general.select..'),
					0 => _translate('users_management.list.inactive'),
					1 => _translate('users_management.list.active'),

				),
			),


		);

		$dataSource = new DataSource('users');
		$dataSource->addFields(
			['users.id','users.username','users.email','users.name','users.surname','users.active']
		);
		

		ListHelper::create('core_user',$this)
			->setDataSource($dataSource)
			->enableExport(true)
			->setExportTypes(['pdf','csv','excel'])
			->enableBulkActions(true)
			->enableSearch(true)
			->setFieldsFromArray($fields)
			->addEditActionRowButton()
			->addCopyActionRowButton()
			->addDeleteActionRowButton(function($row){
				return _translate(['users_management.list.confirm_delete_message',$row->name,$row->surname]);
			})
			->onDelete(function($id){
				//eliminazione dell'utente
				$user = User::withId($id);
				if( is_object($user)){
					$user->delete();
				}
				$this->displayMessage(_translate('users_management.form.messages.deleted'));
			})
			->onBulkAction(function($action,$ids){
				$this->bulkActions($action,$ids);
			})
			->addActionBulkButtons(
				[	
					(new ListActionBulkButton('active'))
						->setConfirm(true)
						->setConfirmMessage(_translate('users_management.list.bulk.activation'))
						->setText(_translate('users_management.list.active_action'))
						->setIconType('icon')
						->setIcon('fa fa-eye'),

					(new ListActionBulkButton('inactive'))
						->setConfirm(true)
						->setConfirmMessage(_translate('users_management.list.bulk.disactivation'))
						->setIconType('icon')
						->setIcon('fa fa-eye-slash')
						->setText(_translate('users_management.list.inactive_action'))
				]
			)->onSearch(function(\Illuminate\Database\Query\Builder $query){
				if( $name = _var('name') ){
					$query->where(function($condition) use ($name){
						$condition->where('name','like',"%{$name}%");
						$condition->orWhere('surname','like',"%{$name}%");
					});
				}
				if( $username = _var('username') ){
					$query->where('username','like',"%{$username}%");
				}
				if( $id = _var('id') ){
					$query->where('id',$id);
				}
				if( $email = _var('email') ){
					$query->where('email','like',"%{$email}%");
				}
				if( isset($_GET['active']) ){
					$active = _var('active');
					if( $active != -1 ){
						$query->where('active',$active);
					}
				}
			})
			->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
				if( in_array($field,['id','email','name','active'])){
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
	 * ajax request
	 *
	 * @return void
	 */
	function ajax(): void{

		$action = $this->getAction();
		$id = _var('id');
		switch($action){
			case 'change_visibility':
				$obj = User::withId($id);
				if( is_object($obj) ){
					if( $obj->active ){
						$obj->active = 0;
					}else{
						$obj->active = 1;
					}

					$obj->save();
					$response = array(
						'result' => 'ok',
						'text' => $obj->active? strtoupper(_translate('active')):strtoupper(_translate('inactive')),
						'status' => $obj->active
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

	/**
	 * Action bulks
	 *
	 * @param string $action
	 * @param array $ids
	 * @return void
	 */
	private function bulkActions(string $action=null, array $ids=null){
		switch($action){
			case 'active':

				foreach($ids as $id){
					$user = User::withId($id);
					if( is_object($user) ){
						$user->active = 1;
						$user->save();
					}
				}
				break;
			case 'inactive':
				foreach($ids as $id){
					$user = User::withId($id);
					if( is_object($user) ){
						$user->active = 0;
						$user->save();
					}

				}
				break;
			case 'delete':
				foreach($ids as $id){
					$user = User::withId($id);
					if( is_object($user) ){
						$user->deleted = 1;
						$user->save();
					}
				}
				break;
		}
	}



}



?>
