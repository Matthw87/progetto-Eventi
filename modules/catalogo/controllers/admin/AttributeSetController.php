<?php

use Catalogo\Attribute;
use Catalogo\Template;
use Marion\Controllers\ListAdminController;
use Marion\Support\ListWrapper\ListHelper;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Support\Form\FormHelper;
use Marion\Support\Form\Fragment;
use Marion\Support\ListWrapper\DataSource;

class AttributeSetController extends ListAdminController{
	public $_auth = 'catalog';


	private $attributes = [];

	/**
	 * Display Form
	 *
	 * @return void
	 */
	function displayForm(){
		$this->setMenu('manage_attributeSets');
		$this->setTitle(_translate('attribute_sets.form.title','catalogo'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('attribute_sets.form.fields.name','catalogo'),
				'validation'=> 'required|max:100'
			]
		];

		//prendo l'action
		$action = $this->getAction();



		$attributes = Attribute::prepareQuery()->orderBy('name')->get();
		$this->attributes = $attributes;
		
       
        $form = FormHelper::create('catalogo_form_template_admin',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'catalogo/templates/admin/forms/attribute_set.xml')
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
						$obj = Template::withId(_var('id'));
						if( is_object($obj)){
							$data = $obj->getDataForm();
							if( $action == 'duplicate' ){
                                unset($data['id']);
                            }
							$form->formData->data = $data;
                            foreach($obj->composition as $i=> $value){
								//debugga($value);exit;
                                $fragment = $this->createRow($i,$value);
                                $form->addFragment('rows',$fragment);
                                
                            }
                            //aggiorno il valore di "count_members"
                            $form->store('count_rows',count($obj->composition));
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
				
				
                if( $action == 'edit' ){
					$obj = Template::withId($data['id']);
				}else{
					$obj = Template::create();
				}
				
				$obj->setComposition($new_data['values']);
				
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
			$this->setMenu('manage_attributeSets');
			$this->setTitle(_translate('attribute_sets.list.title','catalogo'));
			if( _var('updated') ){
				$this->displayMessage(_translate('attribute_sets.messages.updated','catalogo'));
			}
			if( _var('created') ){
				$this->displayMessage(_translate('attribute_sets.messages.created','catalogo'));
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
					'name' => _translate('attribute_sets.form.fields.name','catalogo'),
					'field_value' => 'name',
					'sortable' => true,
					'sort_id' => 'name',
					'searchable' => true,
					'search_name' => 'name',
					'search_value' => _var('name'),
					'search_type' => 'input',
				]

			];

			$dataSource = (new DataSource('product_templates'))
				->addFields(['product_templates.name','product_templates.id']);
			


			ListHelper::create('catalogo_tag_list',$this)
				->setFieldsFromArray($fields)
				->enableExport(true)
				->setDataSource($dataSource)
				->setExportTypes(['pdf','csv','excel'])
				->enableBulkActions(true)
				->enableSearch(true)
				->setFieldsFromArray($fields)
				->addEditActionRowButton()
				->addCopyActionRowButton()
				->enableBulkActions(false)
				->addDeleteActionRowButton(function($row){
					return _translate(['attribute_sets.messages.confirm_delete_message',$row->name],'catalogo');
				})
				->onDelete(function($id){
					//eliminazione del tag
					$object = Template::withId($id);
					if( is_object($object)){
						$object->delete();
						$this->displayMessage(_translate('attribute_sets.messages.deleted','catalogo'));
					}
					
					
				})->display();
		
	}


	function createRow(int $i, array $data=[]): Fragment{
        
		
		$attributes = [];
		foreach($this->attributes as $a){
			$attributes[$a->id] = $a->get('name');
		}
		$fields = [
            "values_{$i}_product_attribute_id" => [
                'type' => 'select',
                'label' => _translate('attribute_sets.form.fields.attribute','catalogo'),
                'validation'=> 'required',
				'options' => $attributes
			],
			"values_{$i}_order_view" => [
                'type' => 'text',
                'label' => _translate('attributes.form.fields.order_view','catalogo'),
                'validation'=> 'required'
			],
			"values_{$i}_type" => [
                'type' => 'select',
                'label' => _translate('attribute_sets.form.fields.type','catalogo'),
				'options' =>  [
						'select' => _translate('attribute_sets.form.fields.select','catalogo'),
						'radio' => _translate('attribute_sets.form.fields.radio','catalogo'),
					]
				
			],
			"values_{$i}_show_image" => [
                'type' => 'select',
                'label' => _translate('attribute_sets.form.fields.show_image','catalogo'),
				'options' =>[
						0 => strtoupper(_translate('general.no')),
						1 => strtoupper(_translate('general.yes')),
					]
				
			],
            
        ];
        
        $fragment = new Fragment('row_'.$i,$this);
        $fragment->setTemplate("
            <fragment>
                    <tr>
                        <td>
                            <field name='values_{$i}_product_attribute_id'/>
                        </td>
						<td>
                            <field name='values_{$i}_order_view'/>
                        </td>
						<td>
                            <field name='values_{$i}_type'/>
                        </td>
						<td>
                            <field name='values_{$i}_show_image'/>
                        </td>
                        <td>
                            <button class='btn btn-danger pull-right' type='button' onclick='javascript:formEvent(\"delete_row\",{$i})'> <i class='fa fa-trash-o'> </i> elimina</button>
                        </td>
                    </tr>
            </fragment>
        ");

        if( okArray($data) ){
            $fragment->setDataForm([
                "values_{$i}_product_attribute_id" => $data['product_attribute_id'],
				"values_{$i}_order_view" => isset($data['order_view'])?$data['order_view']:'',
				"values_{$i}_type" => $data['type'],
				"values_{$i}_show_image" => $data['show_image'],
            ]);
        }
        
        $fragment->setFields($fields);
        return $fragment;
    }




}



?>