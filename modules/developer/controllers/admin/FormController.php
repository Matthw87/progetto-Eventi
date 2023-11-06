<?php
use Marion\Controllers\ListAdminController;
use Marion\Core\Marion;
use Marion\Support\Form\Form;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\ListActionRowButton;
use Marion\Support\ListWrapper\{DataSource, ListHelper};

class FormController extends ListAdminController{
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


       
        $form = FormHelper::create('developer_form',$this)
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
		$this->setTitle('Forms');
		if( _var('updated') ){
			$this->displayMessage(_translate('form_updated','developer'));
		}
		if( _var('inserted') ){
			$this->displayMessage(_translate('form_added','developer'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('form_deleted','developer'));
		}

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
					'name' => 'Nome',
					'field_value' => 'nome',
					'sortable' => true,
					'sort_id' => 'nome',
					'searchable' => true,
					'search_name' => 'nome',
					'search_value' => _var('nome'),
					'search_type' => 'input',
				),
				array(
					'name' => '',
					'function_type' => 'row',
					'function' => function($item){
						return "<a href='index.php?mod=developer&ctrl=FormFieldAdmin&action=list&id_form={$item->codice}' class='btn btn-default btn-sm'><i class='fa fa-list'></i> "._translate('show_fields','developer')."</a>";
					},
				),


			);

		
		$dataSource = new DataSource('form');
		$dataSource->addFields(['form.codice','form.nome']);

		ListHelper::create('developer_form',$this)
			->setRowId('codice')
			->enableExport(true)
			->setExportTypes(['pdf','csv','excel'])
			->enableBulkActions(true)
			->enableSearch(true)
			->setFieldsFromArray($fields)
			->addEditActionRowButton()
			->addActionRowButton( 
				(new ListActionRowButton('export'))
					->setUrlFunction(function($row){
						return 'index.php?mod=developer&ctrl=Form&action=export&id='.$row->codice;
					})
					->setTargetBlank(true)
					->setText('esporta')
					->setIcon('fa fa-download')
			)->addCopyActionRowButton()
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
			->setDataSource($dataSource)
			->onSearch(function(\Illuminate\Database\Query\Builder $query){
				if( $nome = _var('nome') ){
					$query->where('nome','like',"%{$nome}%");
				}
				if( $id = _var('codice') ){
					$query->where('codice',$id);
				}
			})
			->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
				if( in_array($field,['nome','codice'])){
					$query->orderBy($field,$order);
				}
			})
			->enableBulkActions(false)
			->display();
			
	}


	private function export($id): void{
		
		$database = Marion::getDB();
		$form = $database->select('*','form',"codice={$id}");
		
		if( okArray($form) ){
			$name = $form[0]['nome'];
			$query = Form::export($name);
			echo $query;

		}
	}

	function displayContent(){
		$action = $this->getAction();
		switch($action){
			case 'export':
				$this->export(_var('id'));
				exit;
		}
	}


}