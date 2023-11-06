<?php
use Marion\Controllers\ListAdminController;
use Marion\Core\Marion;
use Marion\Support\Form\Form;
use Marion\Support\ListWrapper\ListHelper;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\ListActionRowButton;

class FormFieldAdminNewController extends ListAdminController{
	public $_auth = 'superadmin';


	/**
	 * Display form
	 *
	 * @return void
	 */
	function displayForm()
	{

		$this->setMenu('developer_forms');
		$this->setTitle('Form');
	
		
		$fields = [
			'codice' => [
				'type' => 'hidden'
			],
			'nome' => [
				'type' => 'text',
                'label' => 'nome',
				'validation'=> 'required|max:100',
			],
			'commenti' => [
				'type' => 'textarea',
                'label' => 'descrizione',
				'validation'=> 'max:200',
			]
		];

		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('developer_form_form',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'developer/templates/admin/forms/form.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso

				if( !$form->isSubmitted() ){
					
					if($action != 'add'){
						$form_data = DB::table('form')->where('codice',_var('id'))->first();
						if($form_data ){
							$form->formData->data = (array)$form_data;
						}

					}
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				
				$params = [];
                if( $action == 'edit' ){
					$params['updated'] = 1;
					DB::table('form')->where('codice',$data['codice'])->update($data);
				}else{
					$params['inserted'] = 1;
					unset($data['codice']);
					DB::table('form')->insert($data);
				}
				if( $form->ctrl instanceof ListAdminController ){
					$form->ctrl->redirectTolist($params);
				}
				
				

            })->setFields($fields);

        $form->display();
	}


	
	/**
	 * Display List Forms
	 *
	 * @return void
	 */
	function displayList(){
		
		$this->setMenu('developer_forms');

		$id_form = _var('id_form');

		$form = $this->getForm($id_form);
		$this->setTitle(_translate(['Campi del form <b>%s</b>',$form['nome']],'developer'));

		if( _var('updated') ){
			$this->displayMessage(_translate('form_updated','developer'));
		}
		if( _var('inserted') ){
			$this->displayMessage(_translate('form_added','developer'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('form_deleted','developer'));
		}

		$type_list = $this->getTypeList();
		$fields = array(
			array(
				'name' => 'ID',
				'field_value' => 'codice',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'codice',
				'search_name' => 'codice',
				'search_value' => '',
				'search_type' => 'input',
			),
			array(
				'name' => 'Name',
				'field_value' => 'campo',
				'sortable' => true,
				'sort_id' => 'campo',
				'searchable' => true,
				'search_name' => 'campo',
				'search_value' => _var('campo'),
				'search_type' => 'input',
			),
			array(
				'name' => 'Etichetta',
				'field_value' => 'etichetta',
				'sortable' => true,
				'sort_id' => 'etichetta',
				'searchable' => true,
				'search_name' => 'etichetta',
				'search_value' => _var('etichetta'),
				'search_type' => 'input',
			),
			array(
				'name' => 'Tipo',
				'function_type' => 'row',
				'function' => function($row) use ($type_list){
					if( array_key_exists($row->type,$type_list) ){
						return $type_list[$row->type];
					}
					return '';
				},
				'sortable' => true,
				'sort_id' => 'type',
				'searchable' => true,
				'search_name' => 'type',
				'search_value' => _var('type'),
				'search_type' => 'select',
				'search_options' => $type_list
			),
			array(
				'name' => 'Obbligatorio',
				'function_type' => 'row',
				'function' => function($row){
					if( _var('export') ){
						if( $row->obbligatorio ){
							$html = strtoupper(_translate('general.yes'));
						}else{
							$html = strtoupper(_translate('general.no'));
						}
					}else{
						if( $row->obbligatorio ){
							$html = "<span class='label label-success'  id='field_{$row->codice}_online' style='cursor:pointer;' onclick='change_mandatory({$row->codice}); return false;'>".strtoupper(_translate('general.yes'))."</span>";
							$html .= "<span class='label label-danger' id='field_{$row->codice}_offline' style='cursor:pointer; display:none;' onclick='change_mandatory({$row->codice}); return false;'>".strtoupper(_translate('general.no'))."</span>";
						}else{
							$html = "<span class='label label-danger' id='field_{$row->codice}_offline' style='cursor:pointer;' onclick='change_mandatory({$row->codice}); return false;'>".strtoupper(_translate('general.no'))."</span>";
							$html .= "<span class='label label-success'  id='field_{$row->codice}_online' style='cursor:pointer;display:none;' onclick='change_mandatory({$row->codice}); return false;' >".strtoupper(_translate('general.yes'))."</span>";
						}
					}
			
					return $html;
				},
				'searchable' => true,
				'search_name' => 'obbligatorio',
				'search_value' => (isset($_GET['obbligatorio']))? _var('obbligatorio'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => _translate('general.select..'),
					0 => 'NO',
					1 => 'SI',
					
				),
			),
			array(
				'name' => '',
				'function_type' => 'row',
				'function' => 'getOrderButtons',
			),
			


		);

		
		$limit = ListHelper::limit();
		$offset = ListHelper::offset();

		$list = ListHelper::create('form_fields_list_developer',$this)
			->setRowId('codice')
			->enableExport(true)
			->setPerPage($limit)
			->setExportTypes(['pdf','csv','excel'])
			->enableBulkActions(true)
			->enableSearch(true)
			->setFieldsFromArray($fields)
			->addEditActionRowButton()
			->addCopyActionRowButton()
			->addDeleteActionRowButton(function($row){
				return _translate(['confirm_delete_form',$row->nome],'developer');
			})
			->onDelete(function($id){
				//eliminazione del form
				if( Form::exists($id) ){
					Form::delete($id);
				}
				$this->displayMessage(_translate('form_deleted','developer'));
				
			})
			->enableBulkActions(false);
			
			//prendo i dati
			$total_items = 0;
			$items = [];
			$this->getList(
					$total_items,
					$items,
					$limit,
					$offset
			);
			
			$list->setDataList($items);
			$list->setTotalItems($total_items);

			$list->display();
			
	}

	/**
	 * Datasource profile's list
	 *
	 * @param integer $tot
	 * @param array $data
	 * @param int $limit
	 * @param int $offset
	 * @return void
	 */
	function getList(&$tot=0,&$data=[],int $limit,int $offset): void{
		$query = DB::table('form_campo')->where('form',_var('id_form'));


		if( $name = _var('name') ){
			$query->where('campo','like',"%{$name}%");
		}
		if( $etichetta = _var('etichetta') ){
			$query->where('etichetta','like',"%{$name}%");
		}
		if( $id = _var('codice') ){
			$query->where('codice',$id);
		}

		$tot = $query->count();

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$query->orderBy($order,$order_type);
		}

		$query->limit($limit);
		if( $offset ){
			$query->offset($offset);
		}
		$data = $query->get()->toArray();
		//debugga($data);exit;
	}






	function displayContent(){
		$action = $this->getAction();
		switch($action){
			
		}
	}




	private function getTypeList(): array{
		$database = Marion::getDB();
		$select_types = $database->select('*','form_type');
		
		$list_type = array(_translate('general.select..'));
		foreach($select_types as $v){
			$list_type[$v['codice']] = $v['etichetta'];
		}
		return $list_type;
	}


	private function getForm($id){
		$database = Marion::getDB();
		$form = $database->select('*','form',"codice={$id}");
		return $form[0];
	}


}