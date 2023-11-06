<?php
use Marion\Controllers\ListAdminController;
use Marion\Core\Marion;
use SliderFull\{Slider};
use Marion\Support\Cache;
use Marion\Support\ListWrapper\ListHelper;
use Marion\Support\ListWrapper\DataSource;
use Marion\Support\Form\FormHelper;

class SliderController extends ListAdminController{
	public $_auth = 'cms';	

	/**
	 * Display list
	 *
	 * @return void
	 */
	function displayList(): void{
		
		$this->setMenu('sliderfull');
		$this->setTitle('Sliders');

		
		
		$fields = [

			  [
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => _var('id'),
				'search_type' => 'input',

			],
			[
				'name' => 'Nome',
				'field_value' => 'name',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'name',
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',

			],
			[
				'name' => 'Orientamento frecce',
				'field_value' => 'arrows_direction',
				'function_type' => 'value',
				'function' => function($val){
					return _translate('slider.form.'.$val,'sliderfull');
				}

			],
			[
				'name' => '',
				'function_type' => 'row',
				'function' => function($row){
					
					return '<a href="index.php?mod=sliderfull&ctrl=Slide&action=list&slider_id='.$row->id.'" class="btn btn-sm btn-default">slides</a>';
				}

			],

		];

		$dataSource = (new DataSource('sliderfull_sliders'))
            ->addFields(['sliderfull_sliders.name','sliderfull_sliders.id','sliderfull_sliders.arrows_direction']);


        ListHelper::create('sliderfull_slider',$this)
            ->setFieldsFromArray($fields)
            ->enableExport(true)
			->addEditActionRowButton()
			->addCopyActionRowButton()
            ->addDeleteActionRowButton()
			//->setPerPage($limit)
            ->setExportTypes(['pdf','csv','excel'])
            ->enableBulkActions(false)
            ->enableSearch(true)
            ->setFieldsFromArray($fields)
            ->setDataSource($dataSource)
            ->onSearch(function(\Illuminate\Database\Query\Builder $query){
                if( $id = _var('id') ){
                    $query->where('sliderfull_sliders.id',$id);
                }
                if( $name = _var('name') ){
                    $query->where('sliderfull_sliders.name','like',"%{$name}%");
                }
            })
			->onDelete(function($id){
				$this->delete($id);
			})
            ->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
                if( in_array($field,['id','name'])){
                    $query->orderBy($field,$order);
                }
            })->display();

	}

	
	/**
	 * Display Form
	 *
	 * @return void
	 */
	function displayForm(){
		
		$this->setMenu('sliderfull');
		$this->setTitle('Sliders');

		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('slider.form.name','sliderfull'),
				'validation'=> 'required|max:100'
			],
			'arrows_direction' =>[
				'type' => 'select',
                'label' => _translate('slider.form.arrows_direction','sliderfull'),
				'options'=> [
					'horizontal' => _translate('slider.form.horizontal','sliderfull'),
					'vertical' =>_translate('slider.form.vertical','sliderfull'),
				]
			], 
		];

		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('sliderfull_slider',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'sliderfull/templates/admin/forms/slider.xml')
			->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso
				if( $form->isSubmitted() ){

					
				}else{
					if($action != 'add'){
						$obj = Slider::withId(_var('id'));
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
					$obj = Slider::withId($data['id']);
				}else{
					$obj = Slider::create();
				}
				
				
				$res = $obj->set($data)->save();
				if( is_object($res) ){
					$this->resetCache($res->id);
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



	function delete(int $id){
		$obj = Slider::withId($id);
		if( $obj ){
			$obj->delete();
			$this->resetCache($obj->id);
		}
		
		$this->displayMessage('Slider eliminato con successo','success');
	}



	/**
	 * reset cache for pagecomposer
	 *
	 * @param [type] $id_slider
	 * @return void
	 */
	private function resetCache($id_slider): void{
		$database = Marion::getDB();
		$select = $database->select('*','composed_page_composition_tmp as h join modules as m on m.id=h.module',"m.directory='sliderfull'");
		if( okArray($select) ){
			foreach($select as $v){
				if( isset($v['parameters']) ){
					$dati = unserialize($v['parameters']);
					if( $dati['id_slider'] == $id_slider ){
						$key = 'sliderfull_'.$v['id'];
						

						if( Cache::exists($key) ){
							Cache::remove($key);
						}
					}
				}
				
				
			}
		}
		$select = $database->select('*','composed_page_composition_tmp as h join modules as m on m.id=h.module',"m.directory='sliderfull'");
		if( okArray($select) ){
			foreach($select as $v){
				if( isset($v['parameters']) ){
					$dati = unserialize($v['parameters']);
					if( $dati['id_slider'] == $id_slider ){
						$key = 'sliderfull_'.$v['id'];
						

						if( Cache::exists($key) ){
							Cache::remove($key);
						}
					}
				}
				
			}
		}
	}

}



?>