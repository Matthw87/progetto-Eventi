<?php
use Marion\Controllers\ListAdminController;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Support\Form\FormHelper;
use Marion\Entities\Cms\Footer;
use Marion\Support\ListWrapper\{DataSource, SimpleListHelper,ListActionRowButton};
class FooterController extends ListAdminController{
	public $_auth = 'cms';
	
	/**
	 * Display form
	 *
	 * @return void
	 */
	function displayForm()
	{

		$this->setMenu('edit_footer');
		$this->setTitle(_translate('footers.form.title'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('footers.form.fields.name'),
				'validation'=> 'required|max:100',
			]
		];

		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('core_footer',$this)
            ->layoutFile(_MARION_ROOT_DIR_.'backend/templates/admin/forms/form_footer.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso

				if( !$form->isSubmitted() ){
					
					if($action != 'add'){
						$home = Footer::withId(_var('id'));
						if( is_object($home)){
							$data = $home->getDataForm();
							$form->formData->data = $data;
						}
					}
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				
                if( $action == 'edit' ){
					$user = Footer::withId($data['id']);
				}else{
					$user = Footer::create();
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
		$this->setTitle('Footers');
		$this->setMenu('edit_footer');

		
		if( _var('updated') ){
			$this->displayMessage(_translate('footers.form.messages.updated'));
		}
		if( _var('added') ){
			$this->displayMessage(_translate('footers.form.messages.added'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('footers.form.messages.deleted'));
		}
		
		$dataSource = (new DataSource('footers'))
			->addFields(['footers.id','footers.name','footers.active','footers.page_id']);
		SimpleListHelper::create('core_footer',$this)
			->setPerPage(5)
			->setPerPageSelect([5,10,15])
			->setDataSource($dataSource)
			->setContent(function($item){
				return $item->name;
			})->setActiveClass(function($item){
				if( $item->active){
					return true;
				}else{
					return false;
				}
			})->addActionRowButton(
				(new ListActionRowButton('setting'))
				->setIcon('fa fa-wrench')
				->setText(_translate('homepages.list.buttons.setting'))
				->setUrl(function($item){
					return 'index.php?ctrl=Footer&action=edit&id='.$item->id;
				})
			)->addActionRowButton((new ListActionRowButton('preview'))
				->setIcon('fa fa-eye')
				->setText(_translate('homepages.list.buttons.preview'))
				->setUrl(function($item){
					return _MARION_BASE_URL_.'index.php?ctrl=Preview&mod=pagecomposer&id='.$item->page_id;
				})
			)->addActionRowButton(
				(new ListActionRowButton('edit'))
				->setIcon('fa fa-edit')
				->setText(_translate('homepages.list.buttons.edit'))
				->setUrl(function($item){
                    return 'index.php?mod=pagecomposer&ctrl=PagecomposerAdmin&action=edit&id='.$item->page_id;
				})
			)->addActionRowButton(
				(new ListActionRowButton('active'))
				->setEnableFunction(function($item){
					return !$item->active;
				})
				->setConfirm(true)
				->setConfirmMessage(function($item){
					return _translate(['homepages.list.buttons.confirm_active_message',$item->name]);
				})
				->setIcon('fa fa-rocket')
				->setText(_translate('homepages.list.buttons.active'))
				->setClass('btn btn-default')
			)->addActionRowButton(
				(new ListActionRowButton('disable'))
				->setEnableFunction(function($item){
					return $item->active;
				})
				->setConfirm(true)
				->setConfirmMessage(function($item){
					return _translate(['homepages.list.buttons.confirm_disable_message',$item->name]);
				})
				->setIcon('fa fa-rocket')
				->setText(_translate('homepages.list.buttons.disable'))
				->setClass('btn btn-default')
			)->addActionRowButton(
				(new ListActionRowButton('active'))
				->setEnableFunction(function($item){
					return !$item->active;
				})
				->setConfirm(true)
				->setConfirmMessage(function($item){
					return _translate(['homepages.list.buttons.confirm_active_message',$item->name]);
				})
				->setIcon('fa fa-rocket')
				->setText(_translate('homepages.list.buttons.active'))
				->setClass('btn btn-default')
			)->addActionRowButton(
				(new ListActionRowButton('delete'))
				->setConfirm(true)
				->setConfirmMessage(function($item){
					return _translate(['homepages.list.buttons.confirm_delete_message',$item->name]);
				})
				->setIcon('fa fa-trash-o')
				->setText(_translate('homepages.list.buttons.delete'))
				->setClass('btn btn-danger')
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
			})->display();
	}




	/**
	 * delete item
	 *
	 * @param int $id
	 * @return void
	 */
	private function delete(int $id){
		DB::table('footers')->where('id',$id)->delete();
		$this->displayMessage(_translate('footers.form.messages.deleted'));
	}

	/**
	 * active item
	 *
	 * @param integer $id
	 * @return void
	 */
	private function active(int $id){
		DB::table('footers')->update(array('active'=>0));
		DB::table('footers')->where('id',$id)->update(array('active'=>1));		
		$this->displayMessage(_translate('footers.list.messages.activated'));
	}

	/**
	 * disable item
	 *
	 * @param integer $id
	 * @return void
	 */
	private function disable(int $id){
		DB::table('footers')->where('id',$id)->update(array('active'=>0));		
		$this->displayMessage(_translate('footers.list.messages.disabled'));
	}
	
}
?>