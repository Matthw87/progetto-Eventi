<?php
use Marion\Core\Marion;
use Marion\Entities\Cms\{Page,PageComposer};
use Marion\Utilities\PageComposerTools;
use Marion\Support\ListWrapper\{DataSource, ListHelper,ListActionBulkButton,ListActionRowButton};
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Controllers\Elements\UrlButton;
use Marion\Controllers\ListAdminController;
use Marion\Support\Form\FormHelper;
use Marion\Support\Form\Fragment;

class PageController extends ListAdminController{
	public $_auth = 'cms_page';

	/**
	 * override setMedia method
	 *
	 * @return void
	 */
	function setMedia(){
		$action = $this->getAction();
		if( in_array($action,['edit','add','duplicate'])){
			$this->registerJS('assets/js/pages.js');
			$this->registerCSS('assets/css/pages.css');
		}
		
		
	}
	
	/**
	 * override displayContent method
	 *
	 * @return void
	 */
	function displayContent(){
		$this->setMenu('cms_page');
		$action = $this->getAction();
		switch($action){
			case 'edit_page':
				$url = _var('url');
				$return_location = _var('return_location');
				$page = Page::prepareQuery()->where('url',$url)->getOne();
				
				if( is_object($page) ){
					header('Location: '.$this->getUrlScript()."&action=edit&id=".$page->id."&return_location=".$return_location);
					exit;
				}
				break;
			case 'import':
				
				$this->displayImportForm();
				break;
			case 'export':
				$id = _var('id');
				PageComposerTools::export($id);
				exit;
				break;
		}
		
	}

	/**
	 * Display form import page
	 *
	 * @return void
	 */
	function displayImportForm(){
		$this->setMenu('cms_page');
		$this->setTitle(_translate('pages.import_form.title'));


		$this->addToolButton((new UrlButton('import'))
			->setText(_translate('list.back'))
			->setIcon('fa fa-arrow-left')
			->setUrl('index.php?ctrl=Page&action=list')
			->setClass('btn btn-secondario')
		);

		$fields = [
			'file' => [
				'name' => 'file',
				'type' => 'file',
                'label' => _translate('pages.import_form.fields.file_zip'),
				'validation' => 'required|acceptedfiles:application/zip,application/octet-stream,application/x-zip-compressed,multipart/x-zip'
			]
		];
		$form = FormHelper::create('form_user_admin',$this)
		->layoutFile(_MARION_ROOT_DIR_.'backend/templates/admin/forms/form_import_page.xml')
		->process(function(FormHelper $form){
			$data = $form->getValidatedData();
			
			$dir = $this->tempdir();
			$file = $data['file']['tmp_name'];
		
			$zip = new ZipArchive;
			if ($zip->open($file) === TRUE) {
				$zip->extractTo($dir);
				$zip->close();
			} 
			
			if( file_exists($dir."/list_pages.json")){
				$list = json_decode(file_get_contents($dir."/list_pages.json"),true);
				
				if( $list ){
					foreach($list as $v){
						if( file_exists($dir."/".$v.".zip")){
							$id = PageComposerTools::import($dir."/".$v.".zip");
						}
					}
				}
				
				header('Location: index.php?ctrl=Page&action=list');
			}else{
				$id = PageComposerTools::import($data['file']['tmp_name'],$dir);
				header('Location: index.php?ctrl=Page&action=edit&id='.$id);
			}
			exit;
			

		})->setFields($fields);
		$form->display();
	}


	function displayNewPageForm(){
		$this->output('@core/admin/page/new_page.htm');
		exit;
	}

	/**
	 * Display Form
	 *
	 * @return void
	 */
	function displayForm(){
		//$this->displayFormOld();
		//exit;
		$this->setMenu('cms_page');
		$this->setTitle(_translate('pages.form.title'));
		
		//prendo l'action
		$action = $this->getAction();
		$type = _var('type');
		if( !_var('_change_value') ){
			if( $action == 'add' && !$type ){
				$this->displayNewPageForm();
			}
		}
		

		$this->setVar('type',$type);

		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'widget' => [
				'type' => 'hidden',
			],
			'advanced' => [
				'type' => 'hidden',
			],
			'title' => [
				'type' => 'text',
                'label' => _translate('pages.form.fields.title'),
				'validation'=> 'required|max:100',
				'multilang' => true
			],
			'url' => [
				'type' => 'text',
                'label' => _translate('pages.form.fields.slug'),
				'validation'=> 'required|max:100',
				'multilang' => true,
				'description' => _translate(['pages.form.fields.slug_description',$_SERVER['SERVER_NAME']])
			],
			'visibility' => [
				'type' => 'switch',
                'label' => _translate('pages.form.fields.visibility')
			],
			'enable_routing' => [
				'type' => 'switch',
                'label' => _translate('pages.form.fields.enable_routing')
			],
			'route' => [
				'type' => 'text',
                'label' => _translate('pages.form.fields.route'),
				'validation'=> ['max:100'],
				'placeholder' => '/my/{x}/custom/route/{y}',
				'description' => _translate('pages.form.fields.route_description')
			],
			'layout' => [
				'type' => 'radio',
                'label' => _translate('pages.form.fields.layout'),
				'validation' => ['required'],
				'options' => function(){
					return [
						1 => 'Layout1',
						2 => 'Layout2',
						4 => 'Layout4',
						5 => 'Layout5',
					];
				}
			],
			'meta_title' => [
				'type' => 'text',
                'label' => _translate('pages.form.fields.meta_title'),
				'validation'=> 'max:60',
				'multilang' => true
			],
			'meta_description' => [
				'type' => 'textarea',
                'label' => _translate('pages.form.fields.meta_description'),
				'validation'=> 'max:160',
				'multilang' => true
			],
			'content' => [
				'type' => 'editor',
                'label' => _translate('pages.form.fields.content'),
				'multilang' => true
			]
		];

		

		
       
        $form = FormHelper::create('core_page',$this)
            ->layoutFile(_MARION_ROOT_DIR_.'backend/templates/admin/forms/form_page.xml')
            ->onChangeEnableRouting(function(FormHelper $form, $value, $formdata, $other_data){
				//debugga('qua');exit;
				if( $other_data['checked'] ){
					$form->showField('route');
					$form->showElement('route-params');
					$form->hideField('url');
					//$form->fields['url']['validation'] = ['max:100'];
				}else{
					$form->showField('url');
					$form->hideElement('route-params');
					$form->hideField('route');
					//$form->fields['url']['validation'] = ['required','max:100'];
				}

			})
			->onChangeRoute(function(FormHelper $form, $value){

				$fragment = $this->createRouteParamsFragment($value);
				$form->removeAllFragments();
				$form->addFragment('route-params',$fragment);


			})
			->init(function(FormHelper $form) use ($action){
				
				
				//controllo se il form è stato sottomesso
				if( $form->isSubmitted() ){
					$data = $form->getSubmittedData();

					if( !$data['advanced'] ){
						$form->fields['layout']['validation'] = [];
						$form->fields['content']['validation'] = ['required'];
					}else{
						unset($form->fields['content']);
					}
					
				}else{
					if($action != 'add'){
						$page = Page::withId(_var('id'));
						if( is_object($page)){
							$data = $page->getDataForm();
							if( $data['route_id'] ){
								$route = DB::table('routes')->where('id',$data['route_id'])->first();
								if( $route ){
									$data['route'] = $route->route;
									$fragment = $this->createRouteParamsFragment($route->route,$route->params?unserialize($route->params):[]);
									$form->removeAllFragments();
									$form->addFragment('route-params',$fragment);
								}
								
							}
							if(!$data['advanced']){
								$form->fields['content']['validation'] = ['required'];
							}else{
								unset($form->fields['content']);
							}

							$composed_page = DB::table('composed_pages')->where('id',$data['composed_page_id'])->first();
							if( $composed_page ){
								$data['layout'] = $composed_page->layout_id;
							}
							$form->formData->data = $data;
						}
					}else{
						if( $type = _var('type') ){
							$data = [];
							switch($type){
								case 'page_adv':
									$data['advanced'] = 1;
									unset($form->fields['content']);
									break;
								case 'content':
									$data['widget'] = 1;
									$form->fields['content']['validation'] = ['required'];
									break;
								case 'page':
									$form->fields['content']['validation'] = ['required'];
									break;
							}
							$form->formData->data = $data;
							
						}
					}
				}


				
				if( isset($data['enable_routing']) && $data['enable_routing'] ){
					$form->fields['url']['validation'] = [];
					$form->showField('route');
					$form->showElement('route-params');
					$form->hideField('url');
				}else{
					$form->fields['url']['validation'] = ['required'];
					$form->showField('url');
					$form->hideElement('route-params');
					$form->hideField('route');
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				
                if( $action == 'edit' ){
					$page = Page::withId($data['id']);
				}else{
					$page = Page::create();
				}

				
				


				//debugga($route_data);exit;
				$page->set($data);
				$page->setLayout($data['layout']);
				$res = $page->save();
				if( is_object($res) ){

					if( $data['enable_routing'] ){
						$route_data = [
							'route' => $data['route']
						];
						foreach($data as $k => $v){
							 if( preg_match('/params/',$k)){
								 $param = preg_replace('/params_/','',$k);
							 }
							 if( preg_match('/params_/',$k)){
								$param_num = preg_replace('/params_([0-9]+)_(.*)/','$1',$k);
								$param = preg_replace('/params_([0-9]+)_(.*)/','$2',$k);
								$route_data['params'][$param_num][$param] = $v;
							}
						}

						$route_params = [];
						if( okArray($route_data['params'])){
							foreach($route_data['params'] as $p){
								$route_params[$p['param']] = $p['match'];
							}
						}
						

						if( isset($res->route_id) && $res->route_id ){
							DB::table('routes')->where('id',$res->route_id)->update([
								'route' => $route_data['route'],
								'params' => serialize($route_params),
								'methods' => serialize(['GET']),
								'action' => 'IndexController:page'
							]);
						}else{
							$route_id = DB::table('routes')->insertGetId([
								'route' => $route_data['route'],
								'params' => serialize($route_params),
								'methods' => serialize(['GET']),
								'action' => 'IndexController:page'
							]);
							$res->route_id = $route_id;
							$res->save();
						}
					}else{
						if( isset($res->route_id) ){
							DB::table('routes')->where('id',$res->route_id)->delete();
							$res->route_id = null;
							$res->save();
						}
					}



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
					$form->ctrl->errors[] = _translate("pages.entity_errors.".$res);
				}

            })->setFields($fields);

        $form->display();
	}


	/**
	 * Display list
	 *
	 * @return void
	 */
	function displayList(){
		$this->setMenu('cms_page');
		$this->setTitle(_translate('pages.list.title'));


		if( _var('updated') ){
			$this->displayMessage(_translate('pages.form.messages.updated'));
		}
		if( _var('inserted') ){
			$this->displayMessage(_translate('pages.form.messages.inserted'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('pages.form.messages.deleted'));
		}


		$this->addToolButton((new UrlButton('import'))
			->setText(_translate('pages.list.import'))
			->setIcon('fa fa-upload')
			->setUrl('index.php?ctrl=Page&action=import')
			->setClass('btn btn-principale btn-info')
		);

		$fields = array(
			'id' => array(
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',
			),
			'url' => array(
				'name' =>  _translate('pages.form.fields.slug'),
				'field_value' => 'url',
				'function_type' => 'value',
				'function' => '',
				'sortable' => true,
				'sort_id' => 'url',
				'searchable' => true,
				'search_name' => 'url',
				'search_value' => _var('url'),
				'search_type' => 'input',
			),
			'title' => array(
				'name' => _translate('pages.form.fields.title'),
				'field_value' => 'title',
				'function_type' => 'value',
				'function' => '',
				'sortable' => true,
				'sort_id' => 'title',
				'searchable' => true,
				'search_name' => 'title',
				'search_value' => _var('title'),
				'search_type' => 'input',
			),
			'type' => array(
				'name' => _translate('pages.list.type'),
				'field_value' => 'type',
				'function_type' => 'row',
				'function' => function($row){
					if( _var('export') ){
						if ($row->widget ){
							$html = strtoupper(_translate('pages.list.widget'));
						}else{
							if ($row->advanced ){
								$html =strtoupper(_translate('pages.list.advanced_page'));
							}else{
								$html = strtoupper(_translate('pages.list.standard_page'));
							}
						}
					}else{
						if ($row->widget ){
							$html = "<span class='label label-warning'>".strtoupper(_translate('pages.list.widget'))."</span>";
						}else{
							if ($row->advanced ){
								$html = "<span class='label label-info'>".strtoupper(_translate('pages.list.advanced_page'))."</span>";
							}else{
								$html = "<span class='label label-success'>".strtoupper(_translate('pages.list.standard_page'))."</span>";
							}
						}
					}
		
					return $html;
				},
				'searchable' => true,
				'search_name' => 'type',
				'search_value' => _var('type'),
				'search_type' => 'select',
				'search_options' => array(
					-1 => 'seleziona..',
					'widget' => 'widget',
					'advanced' => 'avanzata',
					'standard' => 'standard',
					
				),
			),
			'visibility' => array(
				'name' => _translate('pages.list.online'),
				'field_value' => 'visibility',
				'function_type' => 'row',
				'function' => function($row){
					if( _var('export') ){
						if ($row->visibility ){
							$html = strtoupper(_translate('pages.list.online'));
						}else{
							$html = strtoupper(_translate('pages.list.offline'));
						}
					}else{
						if ($row->visibility ){
							$html = "<span class='label label-success'>".strtoupper(_translate('pages.list.online'))."</span>";
						}else{
							$html = "<span class='label label-danger'>".strtoupper(_translate('pages.list.offline'))."</span>";
						}
					}
		
					return $html;
				},
				'searchable' => true,
				'search_name' => 'visibility',
				'search_value' =>(isset($_GET['visibility']))? _var('visibility'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => 'seleziona..',
					0 => 'offline',
					1 => 'online',
					
				),
			),
			'enable_routing' => array(
				'name' => _translate('pages.list.routing'),
				'field_value' => 'enable_routing',
				'function_type' => 'row',
				'function' => function($row){
					if( _var('export') ){
						if ($row->enable_routing ){
							$html = strtoupper(_translate('general.yes'));
						}else{
							$html = strtoupper(_translate('general.no'));
						}
					}else{
						if ($row->enable_routing ){
							$html = "<span class='label label-success'>".strtoupper(_translate('general.yes'))."</span>";
						}else{
							$html = "<span class='label label-danger'>".strtoupper(_translate('general.no'))."</span>";
						}
					}
		
					return $html;
				},
				'searchable' => true,
				'search_name' => 'enable_routing',
				'search_value' =>(isset($_GET['enable_routing']))? _var('enable_routing'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => 'seleziona..',
					1 => strtoupper(_translate('general.yes')),
					0 => strtoupper(_translate('general.no')),
					
				),
			),
			'link' => array(
				'name' => 'link',
				'function_type' => 'row',
				'function' => function($row){
					if ($row->enable_routing ){
						return '';
					}else{
						return "<a class='btn btn-sm btn-default' target='_blank' href='"._MARION_BASE_URL_."p/".$row->url."'><i class='fa fa-link'></i></a>";
					}
					
				},
			),

		);
		
		$dataSource = new DataSource('pages');
		$dataSource->addFields(
			[
				'pages.id',
				'pages.enable_routing',
				'pages.locked',
				'pages.widget',
				'pages.advanced',
				'pages_langs.url',
				'pages.visibility',
				'pages_langs.title',
				'pages.composed_page_id',
			]);
		$dataSource->queryBuilder()
			->leftJoin('pages_langs','pages_langs.page_id','=','pages.id')
			->where('pages_langs.locale',_MARION_LANG_);

		$list = ListHelper::create('core_page',$this)
			->setDataSource($dataSource)
			->enableExport(true)
			->setExportTypes(['pdf','csv','excel'])
			->enableBulkActions(true)
			->enableSearch(true)
			->setFieldsFromArray($fields)
			->addEditActionRowButton()
			->addCopyActionRowButton()
			->enableBulkActions(true)
			->addDeleteActionRowButton(function($row){
				return _translate(['pages.list.confirm_delete_message',$row->title]);
			})
			->onDelete(function($id){
				//eliminazione della categoria
				$page = Page::withId($id);
				if( is_object($page)){
					$page->delete();
					$this->displayMessage(_translate('pages.form.messages.deleted'));
				}
				
			})->addActionRowButton(
				(new ListActionRowButton('export'))
				->setEnableFunction(function($row){
					return $row->advanced;
				})
				->setUrlFunction(function($row){
					return "index.php?ctrl=Page&action=export&id={$row->id}";
				})
				->setText(_translate('pages.list.export'))
				->setIconType('icon')
				->setIcon('fa fa-download')
			)->addActionRowButton(
				(new ListActionRowButton('setting'))
				->setEnableFunction(function($row){
					return $row->advanced;
				})
				->setUrlFunction(function($row){
					return "index.php?ctrl=PageComposerAdmin&mod=pagecomposer&id={$row->composed_page_id}";
				})
				->setText('composer')
				->setIconType('icon')
				->setIcon('fa fa-magic')
			)
			->addActionBulkButton(
				(new ListActionBulkButton('export'))
				->setConfirm(true)
				->setConfirmMessage("Sicuro di voler procedere con questa operazione?")
				->setText(_translate('pages.list.export'))
				->setIconType('icon')
				->setIcon('fa fa-download')
			)->onBulkAction(function($action,$ids){
				$this->bulkActions($action,$ids);
			});

			$list->getActionRowButton('delete')
				->setEnableFunction(function($row){
				return !$row->locked;
			});
			
			
			//prendo i dati
			/*$total_items = 0;
			$items = [];
			$this->getDataList(
					$total_items,
					$items,
					$limit,
					$offset
			);
			
			$list->setDataList($items);
			$list->setTotalItems($total_items);
			*/
			$list->onSearch(function(\Illuminate\Database\Query\Builder $query){
				
				if( $title = _var('title') ){
					$query->where('title','like',"%{$title}%");
				}
		
				if( $id = _var('id') ){
					$query->where('id',$id);
				}
		
				if( isset($_GET['visibility']) && $_GET['visibility'] != -1 ){
					$visibility = _var('visibility');
					$query->where('visibility',$visibility);
				}
		
				if( $url = _var('url') ){
					$query->where('url','like',"%{$url}%");
				}
		
				
				if( $type = _var('type') ){
					switch($type){
						case 'widget':
							$query->where('widget',1);
							break;
						case 'advanced':
							$query->where('advanced',1);
							break;
						case 'standard':
							$query->where('advanced',0);
							break;
					}
					
				}
			})->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
				if( in_array($field,['id','title','url'])){
					$query->orderBy($field,$order);
				}
			})->display();
	}


	/**
	 * Bulk acttions
	 *
	 * @param string $action
	 * @param [type] $ids
	 * @return void
	 */
	function bulkActions(string $action, $ids): void{
		$list = $ids;
		switch($action){
			case 'delete':
				foreach($list as $v){
					$page = Page::withId($v);
					if( is_object($page) ){
						$page->delete();
					}
				}
				break;
				case 'export':
					
					$dir = $this->tempdir();
					mkdir($dir."/pages");
					foreach($list as $v){
						$list[] = $v;
						PageComposerTools::export($v,$dir."/pages/".$v.".zip");
					}
					file_put_contents($dir."/pages/list_pages.json",json_encode($list));
					$path_zip = $dir."/pages.zip";
					PageComposerTools::Zip($dir."/pages",$path_zip);
					$file_name = basename($path_zip);

					header("Content-Type: application/zip");
					header("Content-Disposition: attachment; filename=$file_name");
					header("Content-Length: " . filesize($path_zip));

					readfile($path_zip);
					unlink($path_zip);
					exit;
					
			break;
		}
	}

	/**
	 * Return tmp dir path
	 *
	 * @return string
	 */
	private function tempdir(): string {
		$tempfile=tempnam(sys_get_temp_dir(),'');
		// you might want to reconsider this line when using this snippet.
		// it "could" clash with an existing directory and this line will
		// try to delete the existing one. Handle with caution.
		if (file_exists($tempfile)) { unlink($tempfile); }
		mkdir($tempfile);
		if (is_dir($tempfile)) { return $tempfile; }
	}

	
	function createRouteParamsFragment(string $route, array $params = []): Fragment{
		
		preg_match_all("/\{[a-z_A-Z]+\}/",$route,$match);
		$fields = [];
		$xml = '<thead><th>Param</th><th>Regex expression</th></thead>';
		$data = [];
		if( okArray($match[0]) ){
			foreach($match[0] as $i => $p){
				$p = preg_replace('/[!\{\}]/','',$p);
				$data["params_{$i}_match"] = isset($params[$p])?$params[$p]:'';
				$data["params_{$i}_param"] = $p;
				$fields["params_{$i}_match"] = [
						'type' => 'text',
						'placeholder' => 'regex',
						'validation' => ['required']
				];
				$fields["params_{$i}_param"] = [
					'type' => 'hidden',
					'validation' => ['required']
				];
				
				$xml .= "<tr>
					<td><b>{$p}</b></td>
					<td>
						<field name='params_{$i}_param' hidden='true' />
						<field name='params_{$i}_match' hidden='true' />
					</td>
				</tr>";
			}
		}
		
		//debugga($xml);exit;
		
		$fragment = new Fragment('route_params',$this);

		
		
		$fragment->setTemplate("
			
				<fragment>
						<table class='table table-bordered'>
							{$xml}
						</table>
				</fragment>
			
		");
		$fragment->setFields($fields);
		$fragment->setDataForm($data);
		
		return $fragment;
		
	}

}



?>