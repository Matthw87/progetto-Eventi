<?php

use Catalogo\Attribute;
use Catalogo\AttributeValue;
use Marion\Controllers\ListAdminController;
use Marion\Core\Marion;
use Marion\Support\ListWrapper\ListHelper;
use Marion\Support\Form\FormHelper;
use Marion\Support\Form\Fragment;
use Marion\Support\ListWrapper\DataSource;

class AttributeController extends ListAdminController{
	public $_auth = 'catalog';

	/**
	 * Display Form
	 *
	 * @return void
	 */
	function displayForm(){
		$this->setMenu('manage_attributes');
		$this->setTitle(_translate('attributes.form.title','catalogo'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('attributes.form.fields.name','catalogo'),
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


       
        $form = FormHelper::create('catalogo_attribute',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'catalogo/templates/admin/forms/attribute.xml')
            ->onAdd(function(FormHelper $form, $params){
				
                //prelevo l'ultimo valore inserito per "count_members"
                $i = $form->retrive('count_rows')+1;
			
                //aggiorno il valore di "count_members"
                $form->store('count_rows',$i);
                $fragment = $this->createRow($i);
				
                $form->addFragment('rows',$fragment);
				
            })->onDeleteAll(function(FormHelper $form, $params){
                $form->removeAllFragments();

            })->onDeleteRow(function(FormHelper $form, $params){
                $id = $params[0];

                $form->removeFragmentById('row_'.$id);

			})
			->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso
				if( $form->isSubmitted() ){

					
				}else{
					if($action != 'add'){
						$obj = Attribute::withId(_var('id'));
						if( is_object($obj)){
							$data = $obj->getDataForm();
							if( $action == 'duplicate' ){
                                unset($data['id']);
                            }
							$form->formData->data = $data;


							$values = $obj->getValues();
							
                            foreach($values as $i=> $value){
                                $fragment = $this->createRow($i,$value->getDataForm());
                                $form->addFragment('rows',$fragment);
                                
                            }
                            //aggiorno il valore di "count_members"
                            $form->store('count_rows',count($values));
						}
					}
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				
				$new_data = [];
                foreach($data as $k => $v){
                    if( preg_match('/values_([0-9+])_([a-zA-Z\_]+)/',$k) ){
                        preg_match('/values_([0-9+])_([a-zA-Z\_]+)/',$k,$matches);
                        $new_data['values'][$matches[1]][$matches[2]] = $v;
                    }else{
                        $new_data[$k] = $v;
                    }
                }

				//debugga($new_data);exit;
				
				
				
                if( $action == 'edit' ){
					$obj = Attribute::withId($data['id']);
				}else{
					$obj = Attribute::create();
				}
				
				
				$res = $obj->set($data)->save();
				if( is_object($res) ){
					
					$values = isset($new_data['values'])?$new_data['values']:[];

					$old_values = AttributeValue::prepareQuery()
						->where('product_attribute_id',$res->id)
						->get();
					$removed_values = [];
					if(okArray($old_values)){
						foreach($old_values as $k => $v){
							$removed_values[$v->id] = $v;
						}
					}

					if(okArray($values)){
						foreach($values as $v){
							unset($removed_values[$v['id']]);
							$v['product_attribute_id'] = $res->id;
							
							$value = AttributeValue::create()->set($v);
							$value->save();
						}
					}
					
					
					if(okArray($removed_values)){
						foreach($removed_values as $v){
							$v->delete();
						}
					}

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
		$this->setMenu('manage_attributes');
		$this->setTitle(_translate('attributes.list.title','catalogo'));
		if( _var('updated') ){
			$this->displayMessage(_translate('attributes.messages.updated','catalogo'));
		}
		if( _var('created') ){
			$this->displayMessage(_translate('attributes.messages.created','catalogo'));
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
				'name' => _translate('tags.form.fields.name','catalogo'),
				'field_value' => 'name',
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			]

		];

		$dataSource = (new DataSource('product_attributes'))
				->addFields(['product_attribute_langs.name','product_attributes.id']);
			$dataSource->queryBuilder()
			->leftJoin('product_attribute_langs','product_attribute_langs.product_attribute_id','=','product_attributes.id')
			->where('product_attribute_langs.lang',_MARION_LANG_);
		ListHelper::create('catalogo_attribute',$this)
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
				return _translate(['attributes.messages.confirm_delete_message',$row->name],'catalogo');
			})
			->onDelete(function($id){
				//eliminazione del 
				$this->delete($id);				
				
			})->display();
		
	}

	function createRow(int $i, array $data=[]): Fragment{
        $fields = [
            "values_{$i}_id" => [
                'type' => 'hidden'
            ],
            "values_{$i}_value" => [
                'type' => 'text',
				'multilang' => true,
                'label' => _translate('attributes.form.fields.value','catalogo'),
                'validation'=> 'required'
			],
			"values_{$i}_order_view" => [
                'type' => 'text',
                'label' => _translate('attributes.form.fields.order_view','catalogo'),
                'validation'=> 'required'
			],
			"values_{$i}_image" => [
                'type' => 'image:small',
                'label' => _translate('attributes.form.fields.image','catalogo')
			],
            
        ];
        
        $fragment = new Fragment('row_'.$i,$this);
        $fragment->setTemplate("
            <fragment>
                    <tr>
                        <td>
                            <field name='values_{$i}_id' hidden='true'/>
                            <field name='values_{$i}_value'/>
                        </td>
						<td>
                            <field name='values_{$i}_order_view'/>
                        </td>
						<td>
                            <field name='values_{$i}_image'/>
                        </td>
                        <td>
                            <button class='btn btn-danger pull-right' type='button' onclick='javascript:formEvent(\"delete_row\",{$i})'> <i class='fa fa-trash-o'> </i> elimina</button>
                        </td>
                    </tr>
            </fragment>
        ");

        if( okArray($data) ){
            $fragment->setDataForm([
                "values_{$i}_id" => $data['id'],
                "values_{$i}_value" => $data['value'],
				"values_{$i}_order_view" => $data['order_view'],
				"values_{$i}_image" => $data['image'],
            ]);
        }
        
        $fragment->setFields($fields);
        return $fragment;
    }


	private function delete($id): void{
		if( $this->checkAttributeSets($id)){

			$obj = Attribute::withId($id);
			if( is_object($obj) ){
				$obj->delete();
				$this->displayMessage(_translate('attributes.messages.deleted','catalogo'));
			}
		}else{
			$obj = Attribute::withId($id);
			if( is_object($obj) ){
				$this->errors[] = "L'attributo <b>{$obj->get('name')}</b> è parte della composizione di uno o più insieme attributi. Prima di procedere con questa operazione rimuovilo dalla composizione degli insieme attributi.";
			}
		}		
	}


	private function checkAttributeSets($id): bool{
		$database = Marion::getDB();;
		$select = $database->select('*','product_template_configuration',"product_attribute_id='{$id}'");
		return !okArray($select);
	}

}



?>