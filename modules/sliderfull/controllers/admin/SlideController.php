<?php
use Marion\Controllers\ListAdminController;
use Marion\Core\Marion;
use SliderFull\Slide;
use SliderFull\Slider;
use Marion\Controllers\Elements\UrlButton;
use Marion\Entities\UserCategory;
use Marion\Support\Cache;
use Marion\Support\ListWrapper\ListHelper;
use Marion\Support\ListWrapper\DataSource;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\ListActionRowButton;

class SlideController extends ListAdminController{
	public $_auth = 'cms';

	


	function resetCache($id_slider){
		$database = Marion::getDB();
		/*$select = DB::table('composition_page_tmp','h')
			->join('module as m','h.module','=','=m.id')
			->where('m.directory','sliderfull')
			->get(['*'])->toArray();*/
		$select = $database->select('*','composed_page_composition_tmp as h join modules as m on m.id=h.module',"m.directory='sliderfull'");
		if( okArray($select) ){
			
			foreach($select as $v){
				if( isset($v['parameters']) ){
					$dati = unserialize($v['parameters']);
					
					if( $dati['id_slider'] == $id_slider ){
						if( isset($dati['id_box']) ){
							$key = 'sliderfull_'.$v['id'];
							if( Cache::exists($key) ){
								Cache::remove($key);
							}
						}
						
					}
				}
				
			}
		}
		$select = $database->select('*','composed_page_composition as h join modules as m on m.id=h.module',"m.directory='sliderfull'");
		if( okArray($select) ){
			
			foreach($select as $v){
				if( isset($v['parameters']) ){
					$dati = unserialize($v['parameters']);
					if( $dati['id_slider'] == $id_slider ){
						if( isset($dati['id_box']) ){
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
	

	/**
	 * Display list
	 *
	 * @return void
	 */
	function displayList(): void{
		
		$this->setMenu('sliderfull');
		
		
		$categories = UserCategory::prepareQuery()->get();
		$user_category_select = [];
		if( okArray($categories)){
			foreach($categories as $c){
				$user_category_select[$c->id] = $c->get('name');
			}
		}
		
		

		$fields = [
			[
				'name' => '',
				'function_type' => 'row',
				'function' => function($row){
					$image =  _MARION_BASE_URL_."media/filemanager/".$row->image;;
					return "<img src='{$image}'>";
				}
			],
			/*[
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => _var('id'),
				'search_type' => 'input',

			],*/
			[
				'name' => 'Url',
				'field_value' => 'url',
				'function_type' => 'value',
				'function' => function($value){
					if( $value ){
						return "<a target='_blank' class='btn btn-default btn-small' href='{$value}'><i class='fa fa-link'></i></a>";
					}else{
						return '';
					}
					
				}

			],
			[
				'name' => 'Categiorie utente supportate',
				'field_value' => 'allowed_user_categories',
				'function_type' => 'value',
				'function' => function($value) use ($user_category_select){
					$value = unserialize($value);
					
					if( $value ){
						$return = '';
						foreach($value as $v){
							if( isset($user_category_select[$v])){
								$name = $user_category_select[$v];
								$return .= "<span style='margin-right: 10px;' class='label label-info'>{$name}</span>";
							}	
						}
						return $return;
					}else{
						return "<span style='margin-right: 10px;' class='label label-success'>TUTTE</span>";
					}
					
				}

			],
			[
				'name' => 'Lingue supportate',
				'field_value' => 'allowed_langs',
				'function_type' => 'value',
				'function' => function($value){
					$value = unserialize($value);
					
					if( $value ){
						$return = '';
						foreach($value as $v){
							$v = strtoupper($v);
							$return .= "<span style='margin-right: 10px;' class='label label-info'>{$v}</span>";
						}
						//debugga($return);exit;
						return $return;
					}else{
						return "<span style='margin-right: 10px;' class='label label-success'>TUTTE</span>";
					}
					
				}

			],
			/*[
				'name' => 'Titolo',
				'field_value' => 'title',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'title',
				'search_name' => 'title',
				'search_value' => _var('title'),
				'search_type' => 'input',

			]*/

		];
		$slider_id = _var('slider_id');

		$title = 'Slides';
		if( $slider_id ){
			$slider = Slider::withId($slider_id);
			if( $slider ){
				$title = _translate(['slide.list.title',$slider->name],'sliderfull');
			}
		}
		$this->setTitle($title);


		$this->resetToolButtons();
		$this->addToolButton(
			(new UrlButton('back'))
			->setText(_translate('list.back'))
			->setIcon('fa fa-arrow-left')
			->setUrl('index.php?mod=sliderfull&ctrl=Slider&action=list')
			->setClass('btn btn-default')
		);
		$this->addToolButton(
			(new UrlButton('add'))
			->setText(_translate('list.add'))
			->setIcon('fa fa-plus')
			->setUrl('index.php?mod=sliderfull&ctrl=Slide&action=add&slider_id='.$slider_id)
			->setClass('btn btn-principale')
		);
		
	


		$dataSource = (new DataSource('sliderfull_slides'))
            ->addFields([
						'sliderfull_slides_langs.title',
						'sliderfull_slides.id',
						'sliderfull_slides.image',
						'sliderfull_slides.url',
						'sliderfull_slides.allowed_langs',
						'sliderfull_slides.allowed_user_categories'
					]);
		$dataSource->queryBuilder()
			->leftJoin('sliderfull_slides_langs',function($join){
				$join->on('sliderfull_slides.id','=','sliderfull_slides_langs.slide_id');
				$join->where('sliderfull_slides_langs.lang',_MARION_LANG_);
			})
			->where('sliderfull_slides.slider_id',$slider_id);

        ListHelper::create('sliderfull_slider',$this)
            ->setFieldsFromArray($fields)
            ->enableExport(true)
			->addEditActionRowButton()
			->addActionRowButton(
				(new ListActionRowButton('copy'))
				->setIcon('fa fa-copy')
				->setText(_translate('list.duplicate'))
				->setUrl($this->getUrlScript()."&action=duplicate&slider_id="._var('slider_id')."&id={{field_id}}")
			)
            ->addDeleteActionRowButton()
			//->setPerPage($limit)
            ->setExportTypes(['pdf','csv','excel'])
            ->enableBulkActions(false)
            ->enableSearch(true)
            ->setFieldsFromArray($fields)
            ->setDataSource($dataSource)
            ->onSearch(function(\Illuminate\Database\Query\Builder $query){
                if( $id = _var('id') ){
                    $query->where('sliderfull_slides.id',$id);
                }
                if( $title = _var('title') ){
                    $query->where('sliderfull_slides_langs.title','like',"%{$title}%");
                }
            })
			->onDelete(function($id){
				$this->delete($id);
			})
            ->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
                if( in_array($field,['id','title'])){
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
		
		$action = $this->getAction();
		
		if( $action == 'edit' ){
			$id = _var('id');
			if( !$id && isset($_POST['formdata']['id']) ){
				$id = $_POST['formdata']['id'];
			}

			$slide = Slide::withId($id);
			if( $slide ){
				$slider = $slide->getSlider();
				$slider_id = $slider->id;
			}
			
		}else{
			$slider_id = _var('slider_id');
			if( !$slider_id && isset($_POST['formdata']['slider_id'])){
				$slider_id = $_POST['formdata']['slider_id'];
			}
			
		}

		$title = 'Slides';
		if( isset($slider_id) && $slider_id ){
			if( !isset($slider) ){
				$slider = Slider::withId($slider_id);
			}
			
			if( $slider ){
				$title = _translate(['slide.list.title',$slider->name],'sliderfull');
			}
		}else{
			header('Location: index.php?mod=sliderfull&ctrl=Slider&action=list');
			exit;
		}

		


		
		
		

		$this->resetToolButtons();
		$this->addToolButton(
			(new UrlButton('back'))
			->setText(_translate('list.back'))
			->setIcon('fa fa-arrow-left')
			->setUrl('index.php?action=list&mod=sliderfull&ctrl=Slide&action=list&slider_id='.$slider_id)
			->setClass('btn btn-secondario m-t-10')
		);

		
		$this->setTitle($title);
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'slider_id' => [
				'type' => 'hidden'
			],
			'title' => [
				'type' => 'text',
                'label' =>_translate('slide.form.title','sliderfull'),
				'validation'=> 'max:100',
				'multilang' => true,
			],
			'color_title' => [
				'type' => 'palette',
				'label' => _translate('slide.form.title_color','sliderfull'),
				'validation'=> 'required'
			],
			'subtitle' => [
				'type' => 'text',
                'label' => _translate('slide.form.subtitle','sliderfull'),
				'validation'=> 'max:100',
				'multilang' => true,
			],
			'url' => [
				'type' => 'text',
                'label' =>_translate('slide.form.url','sliderfull'),
				'validation'=> 'max:500'
			],
			'allowed_langs' => [
				'type' => 'multiselect',
                'label' =>_translate('slide.form.restricted_langs','sliderfull'),
				'options'=> function(){
					$langs = Marion::getConfig('locale','supportati');
					$toreturn = [];
					foreach($langs as $lang){
						$toreturn[$lang] = strtoupper($lang);
					}
					return $toreturn;
				}
			],
			'allowed_user_categories' =>[
				'type' => 'multiselect',
                'label' =>_translate('slide.form.restricted_user_categories','sliderfull'),
				'options' => function(){
					$categories = UserCategory::prepareQuery()->get();
					$user_category_select = [];
					if( okArray($categories)){
						foreach($categories as $c){
							$user_category_select[$c->id] = $c->get('name');
						}
					}
					return $user_category_select;
				}
			],
			'color_subtitle' => [
				'type' => 'palette',
				'label' => _translate('slide.form.sutitle_color','sliderfull'),
				'validation'=> 'required'
			],
			'order_view' => [
				'type' => 'text',
				'label' => _translate('slide.form.order_view','sliderfull'),
				'validation'=> 'required|integer'
			],
			'date_start' => [
				'type' => 'date',
				'label' => _translate('slide.form.date_start','sliderfull')
			],
			'date_end' => [
				'type' => 'date',
				'label' => _translate('slide.form.date_end','sliderfull')
			],
			'locales' => [
				'type' => 'select',
				'label' => _translate('slide.form.locales','sliderfull')
			],
			
			'image' => [
				'type' => 'media',
				'label' => _translate('slide.form.image','sliderfull'),
				'validation'=> 'required'
			],
			'mobile_image' => [
				'type' => 'media',
				'label' => _translate('slide.form.image_mobile','sliderfull')
			],
			'webp_image' => [
				'type' => 'media',
				'label' => _translate('slide.form.image','sliderfull')
			],
			'webp_mobile_image' => [
				'type' => 'media',
				'label' => _translate('slide.form.image_mobile','sliderfull')
			],
		];

		//prendo l'action
		$action = $this->getAction();

		//debugga($action);exit;
       
        $form = FormHelper::create('sliderfull_slider',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'sliderfull/templates/admin/forms/slide.xml')
			->init(function(FormHelper $form) use ($action,$slider_id){
				//controllo se il form è stato sottomesso
				//debugga($form);exit;
				if( $form->isSubmitted() ){
					//debugga('qua');exit;
					
				}else{
					
					if($action != 'add'){
						$obj = Slide::withId(_var('id'));
						if( is_object($obj)){
							$data = $obj->getDataForm();
							if( $action == 'duplicate' ){
                                unset($data['id']);
                            }
							$form->formData->data = $data;
						}
					}else{
						$data = [
							'slider_id' => $slider_id,
							'color_title' => '#000000',
							'color_subtitle' => '#000000',
						];
						$form->formData->data = $data;
					}
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				//debugga($data);exit;
				
				
				
                if( $action == 'edit' ){
					$obj = Slide::withId($data['id']);
				}else{
					$obj = Slide::create();
				}
				
				
				$res = $obj->set($data)->save();
				//debugga($res);exit;
				if( is_object($res) ){
					$this->resetCache($res->id);
					$params = [];
					if( $action == 'edit' ){
						$params['updated'] = 1;
					}else{
						$params['created'] = 1;
					}
					$params['slider_id'] = $res->slider_id;
					if( $form->ctrl instanceof ListAdminController ){
						$form->ctrl->redirectTolist($params);
					}
					
					
				}else{
					$form->ctrl->errors[] = _translate('error');
				}

            })->setFields($fields);

        $form->display();
	}
	


	private function delete(int $id){
		$obj = Slide::withId($id);
		if( $obj ){
			$obj->delete();
			$this->resetCache($obj->id);
		}
		
		$this->displayMessage('Slider eliminato con successo','success');
	}

	


	


}

function array_locales_sliderfull(){
	$locales = Marion::getConfig('locale','supportati');
	
	foreach($locales as $loc){
		$toreturn[$loc] = $loc;
	}
	return $toreturn;

}



?>