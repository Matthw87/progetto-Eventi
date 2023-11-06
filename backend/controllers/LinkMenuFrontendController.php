<?php
use Marion\Entities\Cms\LinkMenuFrontend;
use Marion\Support\ListWrapper\{DataSource, SortableListHelper,ListActionRowButton};
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Controllers\ListAdminController;
use Marion\Support\Form\FormHelper;

class LinkMenuFrontendController extends ListAdminController{
	public $_auth = 'cms';
	public $url_type;



	function setMedia(): void
	{
		parent::setMedia();
		$this->registerJS('assets/js/link_menu_frontend.js');
	}

	/**
	 * Display form
	 *
	 * @return void
	 */
	function displayForm()
	{

		$this->setMenu('link_menu');
		$this->setTitle(_translate('linkMenuFrontend.form.title'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'parent' => [
				'type' => 'select',
                'label' => _translate('linkMenuFrontend.form.fields.parent'),
				'validation'=> 'required|max:100',
				'options' => function(){
					return $this->getTree();
				}
			],
			'title' => [
				'type' => 'text',
                'label' => _translate('linkMenuFrontend.form.fields.title'),
				'multilang' => true,
				'validation'=> 'required|max:100',
			],
			'static_url' => [
				'type' => 'switch',
                'label' => _translate('linkMenuFrontend.form.fields.url_static')
			],
			'url' => [
				'type' => 'text',
                'label' => _translate('linkMenuFrontend.form.fields.static_url'),
				'validation'=> 'required',
			],
			'url_type' => [
				'type' => 'select',
                'label' => _translate('linkMenuFrontend.form.fields.type_url'),
				'validation'=> [
					'required',
					'notempty'
				],
				'options' => function(){
					$select = [
						0 => _translate('general.select..')
					];
					return array_merge($select,LinkMenuFrontend::listGroupPages());
				}
			],
			'id_url_page' => [
				'type' => 'select',
                'label' => _translate('linkMenuFrontend.form.fields.url'),
				'validation'=> [
					'required',
					'notempty'
				],
				'options' => function(){
					$select = [
						0 => _translate('general.select..')
					];
					return array_merge($select,LinkMenuFrontend::listPages());
				}
			],
			'orderView' => [
				'type' => 'text',
                'label' => _translate('linkMenuFrontend.form.fields.order_view'),
				'validation'=> 'required|max:100',
			],
			'visibility' => [
				'type' => 'switch',
                'label' => _translate('linkMenuFrontend.form.fields.visibility')
			],
			'target_blank' => [
				'type' => 'switch',
                'label' => _translate('linkMenuFrontend.form.fields.target_blank')
			],
			'image' => [
				'type' => 'media:image',
                'label' => _translate('linkMenuFrontend.form.fields.image')
			],
		];

		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('core_link_menu_frontend',$this)
            ->layoutFile(_MARION_ROOT_DIR_.'backend/templates/admin/forms/form_link_menu_frontend.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso

				if( !$form->isSubmitted() ){
					
					if($action != 'add'){
						$home = LinkMenuFrontend::withId(_var('id'));
						if( is_object($home)){
							$data = $home->getDataForm();
							$form->formData->data = $data;
						}
					}
				}else{
					$data = $form->getSubmittedData();
					if( isset($data['static_url']) ){
						$form->fields['url_type']['validation'] = [];
						$form->fields['id_url_page']['validation'] = [];
					}else{
						$form->fields['url']['validation'] = [];
					}
					//debugga($data);exit;
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
                if( $action == 'edit' ){
					$user = LinkMenuFrontend::withId($data['id']);
				}else{
					$user = LinkMenuFrontend::create();
				}
				
				
				$res = $user->set($data)->save();
				if( is_object($res) ){
					$params = [];
					if( $action == 'edit' ){
						$params['updated'] = 1;
					}else{
						$params['added'] = 1;
					}
					if( $form->ctrl instanceof ListAdminController ){
						$form->ctrl->redirectTolist($params);
					}
					
					
				}else{
					$form->ctrl->errors[] = _translate("footers.entity_errors.".$res);
				}

            })->setFields($fields);

        $form->display();
	}



	function displayList()
	{
		$this->setTitle(_translate('linkMenuFrontend.list.title'));
		$this->setMenu('link_menu');

		if( _var('updated') ){
			$this->displayMessage(_translate('linkMenuFrontend.form.messages.updated'));
		}
		if( _var('added') ){
			$this->displayMessage(_translate('linkMenuFrontend.form.messages.added'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('pages.form.messages.deleted'));
		}

		$dataSource = (new DataSource('link_menu_frontends'));
		$dataSource->queryBuilder()
			->join('link_menu_frontends_langs',"link_menu_frontends.id","=","link_menu_frontends_langs.link_menu_frontend_id")
			->where('link_menu_frontends_langs.locale',_MARION_LANG_);
		$dataSource->addFields(['link_menu_frontends.id','link_menu_frontends_langs.title','link_menu_frontends.parent','link_menu_frontends.visibility']);
		
		SortableListHelper::create('core_link_menu_frontend',$this)
			->setDataSource($dataSource)
			->setMaxDepth(2)
			->setContent(function($item){
				$content = $item->title;
				if( $item->visibility ){
					$content .= " <span class='label label-success'>ONLINE</span>";
				}else{
					$content .= " <span class='label label-danger'>OFFLINE</span>";
				}
				return $content;
			})->addActionRowButton(
				(new ListActionRowButton('edit'))
				->setIcon('fa fa-edit')
				->setText(_translate('linkMenuFrontend.list.buttons.edit'))
				->setUrl(function($item){
                    return 'index.php?ctrl=LinkMenuFrontend&action=edit&id='.$item->id;
				})
			)->addActionRowButton(
				(new ListActionRowButton('active'))
				->setEnableFunction(function($item){
					return !$item->visibility;
				})
				->setConfirm(true)
				->setConfirmMessage(function($item){
					return _translate(['linkMenuFrontend.list.buttons.confirm_active_message',$item->title]);
				})
				->setIcon('fa fa-rocket')
				->setText(_translate('linkMenuFrontend.list.buttons.active'))
			)->addActionRowButton(
				(new ListActionRowButton('disable'))
				->setEnableFunction(function($item){
					return $item->visibility;
				})
				->setConfirm(true)
				->setConfirmMessage(function($item){
					return _translate(['linkMenuFrontend.list.buttons.confirm_disable_message',$item->title]);
				})
				->setIcon('fa fa-rocket')
				->setText(_translate('linkMenuFrontend.list.buttons.disable'))
			)->addActionRowButton(
				(new ListActionRowButton('delete'))
				->setConfirm(true)
				->setConfirmMessage(function($item){
					return _translate(['linkMenuFrontend.list.buttons.confirm_delete_message',$item->title]);
				})
				->setIcon('fa fa-trash-o')
				->setText(_translate('linkMenuFrontend.list.buttons.delete'))
			)->onAction(function($action,$id){
				switch($action){
					case 'delete':
						$this->delete($id);
						break;
					case 'active':
						$this->active($id);
						break;
					case 'disable':
						$this->disable($id);
						break;
				}
			})->onChange(function($ids){
				$order = 1;
                foreach($ids as $v){
                    $update = [
						'parent' => 0,
					];
					DB::table('link_menu_frontends')
					->where("id", $v['id'])
					->update($update);
					$order++;

                    if( okArray($v['children']) ){
                        foreach( $v['children'] as  $v1 ){
                            $update = [
								'parent' => $v['id'],
								'orderView' => $order
							]; 
							
                            DB::table('link_menu_frontends')
								->where("id", $v1['id'])
								->update($update);
							$order++;
							if( isset($v1['children'])){
								foreach( $v1['children'] as  $v2 ){
									$update = [
										'parent' => $v1['id'],
										'orderView' => $order
									]; 
									
									DB::table('link_menu_frontends')
										->where("id", $v2['id'])
										->update($update);
									$order++;
									if( isset($v2['children'])){
										foreach( $v2['children'] as  $v3 ){
											$update = [
												'parent' => $v2['id'],
												'orderView' => $order
											]; 
											
											DB::table('link_menu_frontends')
												->where("id", $v3['id'])
												->update($update);
											$order++;
										}
									}
								}
							}
							
                        }
                    }

                }
			})->display();
	}

	


	/**
	 * restituisce l'albero delle voci di menu
	 *
	 * @return array
	 */
	private function getTree(): array{
	
		$links = LinkMenuFrontend::prepareQuery()->get();
		$tree = LinkMenuFrontend::buildTree($links);
		
		$toreturn = [
			0 => _translate('general.select..')
		];
		foreach($tree as $level1){
			$toreturn[$level1->id] = $level1->get('title');
			if( okArray($level1->children ) ){
				foreach($level1->children as $level2){
					$toreturn[$level2->id] = $level1->get('title')." / ".$level2->get('title');
					if( okArray($level2->children ) ){
						foreach($level2->children as $level3){
							$toreturn[$level3->id] = $level1->get('title')." / ".$level2->get('title')." / ".$level3->get('title');
							if( okArray($level3->children ) ){
								foreach($level3->children as $level4){
									$toreturn[$level4->id] = $level1->get('title')." / ".$level2->get('title')." / ".$level3->get('title')." / ".$level4->get('title');
									if( okArray($level4->children ) ){
										foreach($level4->children as $level5){
											$toreturn[$level5->id] = $level1->get('title')." / ".$level2->get('title')." / ".$level3->get('title')." / ".$level4->get('title')." / ".$level5->get('title');
												
											if( okArray($level5->children ) ){
												foreach($level5->children as $level6){
													$toreturn[$level6->id] = $level1->get('title')." / ".$level2->get('title')." / ".$level3->get('title')." / ".$level4->get('title')." / ".$level5->get('title')." / ".$level6->get('title');
												}
											}
										}
									}
								}
							}
						}
					}
				}

			}
		}
		uasort($toreturn,function($a,$b){
			 if ($a == $b) {
				return 0;
			}
			return ($a < $b) ? -1 : 1;
		});
			
		return $toreturn;
		
	}

		

	function ajax(){
		$action = $this->getAction();

		switch($action){
			case 'get_link_dinamic_menu':
				$type = _var('type');
	
				$links = LinkMenuFrontend::listPages($type);
				
				$toreturn = array(_translate('general.select..'));
				foreach($links as $k => $v){
					$toreturn[$k] = $v;
				}

				$risposta = array(
					'result' => 'ok',
					'options' => $toreturn
				);

				break;
		}

		
		echo json_encode($risposta);
		exit;


	}


	/**
	 * delete item
	 *
	 * @param int $id
	 * @return void
	 */
	private function delete(int $id){
		DB::table('link_menu_frontends')->where('id',$id)->delete();
		$this->displayMessage(_translate('linkMenuFrontend.form.messages.deleted'));
	}

	/**
	 * active item
	 *
	 * @param integer $id
	 * @return void
	 */
	private function active(int $id){
		DB::table('link_menu_frontends')->where('id',$id)->update(array('visibility'=>1));		
		$this->displayMessage(_translate('linkMenuFrontend.list.messages.activated'));
	}

	/**
	 * disable item
	 *
	 * @param integer $id
	 * @return void
	 */
	private function disable(int $id){
		DB::table('link_menu_frontends')->where('id',$id)->update(array('visibility'=>0));		
		$this->displayMessage(_translate('linkMenuFrontend.list.messages.disabled'));
	}
}



?>