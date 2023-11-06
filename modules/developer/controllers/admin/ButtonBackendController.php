<?php
use Marion\Controllers\ListAdminController;
use Marion\Entities\Cms\HomeButton;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\{DataSource, ListHelper};
class ButtonBackendController extends ListAdminController{
	public $_auth = 'superadmin';

    
    /**
	 * Display List Forms
	 *
	 * @return void
	 */
    function displayList(){
		
		$this->setMenu('developer_button_backend');
		$this->setTitle('Backend buttons');
		if( _var('updated') ){
			$this->displayMessage(_translate('backend_button_updated','developer'));
		}
		if( _var('inserted') ){
			$this->displayMessage(_translate('backend_button_added','developer'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('backend_button_deleted','developer'));
		}


        $dataSource = new DataSource('home_buttons');
        $dataSource->queryBuilder()
            ->join('home_buttons_langs','home_buttons_langs.home_button_id','=','home_buttons.id')
            ->where('home_buttons_langs.locale','=',_MARION_LANG_);
		$dataSource->addFields(
			['home_buttons.id','home_buttons.active','home_buttons_langs.name']
		);

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
					'name' => 'Nome',
					'field_value' => 'name',
					'sortable' => true,
					'sort_id' => 'name',
					'searchable' => true,
					'search_name' => 'name',
					'search_value' => _var('name'),
					'search_type' => 'input',
				),
				array(
					'name' => '',
					'function_type' => 'row',
					'function' => function($item){
						if( _var('export') ){
                            if ($item->active ){
                                $html = strtoupper(_translate('users_management.list.active'));
                            }else{
                                $html = strtoupper(_translate('users_management.list.inactive'));
                            }
                        }else{
                            if ($item->active ){
                                $html = "<span class='label label-success'  id='status_{$item->id}' style='cursor:pointer;' onclick='change_visibility({$item->id}); return false;'>".strtoupper(_translate('users_management.list.active'))."</span>";
                            }else{
                                $html = "<span class='label label-danger' id='status_{$item->id}' style='cursor:pointer;' onclick='change_visibility({$item->id}); return false;'>".strtoupper(_translate('users_management.list.inactive'))."</span>";
                            }
                        }
                        return $html;
                        
					},
				),


			);

		
		
		ListHelper::create('developer_form',$this)
			->enableExport(true)
			->setExportTypes(['pdf','csv','excel'])
			->enableBulkActions(true)
			->enableSearch(true)
			->setFieldsFromArray($fields)
			->addEditActionRowButton()
			->addCopyActionRowButton()
			->addDeleteActionRowButton(function($row){
				return _translate(['confirm_delete_backend_button',$row->name],'developer');
			})
			->onDelete(function($id){
				//eliminazione del form
				$button = HomeButton::withId($id);
                if( $button ){
                    $button->delete();
                }
				$this->displayMessage(_translate('backend_button_deleted','developer'));
				
			})
			->setDataSource($dataSource)
            ->onSearch(function(\Illuminate\Database\Query\Builder $query){
				if( $nome = _var('name') ){
					$query->where('name','like',"%{$nome}%");
				}
				if( $id = _var('id') ){
					$query->where('id',$id);
				}
			})
            ->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
				if( in_array($field,['id','active','name'])){
					$query->orderBy($field,$order);
				}
			})
            ->enableBulkActions(false)
			->display();
			
	}

    /**
	 * Display form
	 *
	 * @return void
	 */
	function displayForm()
	{

		$this->setMenu('developer_button_backend');
		$this->setTitle('Backend Button');
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'name' => [
				'type' => 'text',
                'label' => 'nome',
				'validation'=> 'required|max:100',
                'multilang' => true
			],
            'url' => [
				'type' => 'text',
                'label' => 'url',
				'validation'=> 'required',
			],
            'order_view' => [
				'type' => 'text',
                'label' => 'ordine di visualizzazione',
				'validation'=> 'required|number',
			],
            'active' => [
				'type' => 'switch',
                'label' => 'attivo',
				'validation'=> 'required|number',
			]
		];

		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('developer_backend_button',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'developer/templates/admin/forms/backend_button.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso

				if( !$form->isSubmitted() ){
					
					if($action != 'add'){
                        $obj = HomeButton::withId(_var('id'));
                        if( is_object($obj) ){
                            $form_data = $obj->getDataForm();
                            if($form_data ){
                                $form->formData->data = (array)$form_data;
                            }
                        }
					}
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				
				$params = [];
                if( $action == 'edit' ){
					$params['updated'] = 1;
                    $obj = HomeButton::withId($data['id']);
				}else{
					$params['inserted'] = 1;
                    $obj = HomeButton::create();
					
				}
                $obj->set($data);
                $obj->save();

                if( $form->ctrl instanceof ListAdminController ){
					$form->ctrl->redirectTolist($params);
				}
            })->setFields($fields);

        $form->display();
	}

}