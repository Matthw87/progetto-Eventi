<?php

use Api\ApiKey;
use Marion\Support\Form\FormHelper;
use Marion\Core\Marion;
use Marion\Controllers\ListAdminController;
use Marion\Support\ListWrapper\DataSource;
use Marion\Support\ListWrapper\ListHelper;

class KeyController extends ListAdminController{

    public $_auth = 'catalog';

	/**
	 * Display Form
	 *
	 * @return void
	 */
	function displayForm(){
		$this->setMenu('api_keys');
		$this->setTitle(_translate('api_key.form.title','api'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'api_key' => [
				'type' => 'text',
                'label' => _translate('api_key.form.fields.api_key','api'),
				'validation'=> 'required|max:100',
            ],
            'active' => [
				'type' => 'switch',
                'label' => _translate('api_key.form.fields.active','api'),
            ],
            'enabled_modules' => [
                'label' => _translate('api_key.form.fields.enabled_modules','api'),
                'type' => 'multiselect:tabs',
                'options' => function(){
                    $modules = Marion::$modules;
                    $options = [];
                    foreach($modules as $m){
                        $options[$m['directory']] = $m['directory'];
                    }
                    uasort($options,function($a, $b){
                        if ($a == $b) {
                            return 0;
                        }
                        return ($a < $b) ? -1 : 1;
                    });
                    return $options;
                }
                
            ],
            'token_duration' => [
                'type' => 'text',
                'description' =>_translate('setting.duration_token_description','api'),
                'label' =>_translate('setting.duration_token','api'),
                'validation' => ['required']
            ],
		];

		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('catalogo_tag_product',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'api/templates/admin/forms/api_key.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso
				if( $form->isSubmitted() ){

					
				}else{
					if($action != 'add'){
						$obj = ApiKey::withId(_var('id'));
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
					$obj = ApiKey::withId($data['id']);
				}else{
					$obj = ApiKey::create();
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
			$this->setMenu('api_keys');
			$this->setTitle(_translate('api_key.list.title','api'));
			if( _var('updated') ){
				$this->displayMessage(_translate('api_key.messages.updated','api'));
			}
			if( _var('created') ){
				$this->displayMessage(_translate('api_key.messages.created','api'));
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
					'name' => _translate('api_key.form.fields.api_key','api'),
					'field_value' => 'api_key',
					'sortable' => true,
					'sort_id' => 'api_key',
					'searchable' => true,
					'search_name' => 'api_key',
					'search_value' => _var('api_key'),
					'search_type' => 'api_key',
				],
				[
					'name' => _translate('api_key.form.fields.active','api'),
					'field_value' => 'active',
                    'function_type' => 'value',
                    'function' => function($value){
                        if( $value ){
                            return "<span class='label label-success'>".strtoupper(_translate('general.yes'))."</span>";
                        }else{
                            return "<span class='label label-danger'>".strtoupper(_translate('general.no'))."</span>";
                        }
                    }
				],

			];

			$dataSource = (new DataSource('api_keys'))
				->addFields(['api_keys.*']);
			


			ListHelper::create('apy_key',$this)
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
					return _translate(['tags.messages.confirm_delete_message',$row->api_key],'catalogo');
				})
				->onDelete(function($id){
					//eliminazione del tag
					$object = ApiKey::withId($id);
					if( is_object($object)){
						$object->delete();
						$this->displayMessage(_translate('tags.messages.deleted','catalogo'));
					}
				})
				->setDataSource($dataSource)
				->display();
		
	}

}