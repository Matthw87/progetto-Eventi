<?php
use Marion\Entities\Cms\Page;
use Marion\Controllers\ListAdminController;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Support\Form\FormHelper;
use Marion\Entities\Cms\HomePage;
use Marion\Support\ListWrapper\{DataSource, SimpleListHelper,ListActionRowButton};
class HomeController extends ListAdminController{
	public $_auth = 'cms';
	


	function setMedia()
	{
		parent::setMedia();
		$this->registerJS('assets/js/homepage.js');
	}
	
	/**
	 * Display form
	 *
	 * @return void
	 */
	function displayForm()
	{

		$this->setMenu('edit_home');
		$this->setTitle(_translate('homepages.form.title'));
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('homepages.form.fields.name'),
				'validation'=> 'required|max:100',
			],
			'page_id' => [
				'type' => 'select',
                'label' => _translate('homepages.form.fields.page'),
				'validation'=> 'required',
				'options' => function(){
					$toreturn = [];
					$pages = Page::prepareQuery()->whereExpression('composed_page_id is not null')->get();
					//debugga($pages);exit;
					foreach( $pages as $v ){
						$toreturn[$v->composed_page_id] = $v->get('title');
					}
					return $toreturn;
				}
			],
			'timer' => [
				'type' => 'switch',
                'label' => _translate('homepages.form.fields.timer')
			],
			'start_date' => [
				'type' => 'date',
                'label' => _translate('homepages.form.fields.start_date'),
				'validation'=> ['date']
			],
			'end_date' => [
				'type' => 'date',
                'label' => _translate('homepages.form.fields.end_date'),
				'validation'=> ['date']
			]
		];

		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('core_home',$this)
            ->layoutFile(_MARION_ROOT_DIR_.'backend/templates/admin/forms/form_home.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso

				if( $form->isSubmitted() ){
					$submittedData = $form->getSubmittedData();
					
					if(isset($submittedData['timer'])){
						$form->fields['start_date']['validation'][] = 'required';
						$form->fields['end_date']['validation'][] = 'required';
					}
					
				}else{
					if($action != 'add'){
						$home = HomePage::withId(_var('id'));
						if( is_object($home)){
							$data = $home->getDataForm();
							$form->formData->data = $data;
						}
					}
				}
			})->validate(function(FormHelper $form){
				$data = $form->getValidatedData();
				if( $data['timer']){
					if(strtotime($data['end_date']) < strtotime($data['start_date']) ){
						$form->error_fields[] = 'end_date';
						$form->errors[] = _translate('homepages.form.errors.error_date1');
					}else{
						if( strtotime($data['end_date']) < strtotime( date('Y-m-d') ) ){
							$form->error_fields[] = 'end_date';
							$form->errors[] = _translate('homepages.form.errors.error_date2');
						}
					}
				}
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				
                if( $action == 'edit' ){
					$user = HomePage::withId($data['id']);
				}else{
					$user = HomePage::create();
				}
				
				
				$res = $user->set($data)->save();
				if( is_object($res) ){
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
					$form->ctrl->errors[] = _translate("homepages.entity_errors.".$res);
				}

            })->setFields($fields);

        $form->display();
	}


	function displayList()
	{
		$this->setTitle('Homepages');
		$this->setMenu('edit_home');

		
		$dataSource = (new DataSource('homepages'))->addFields(['homepages.*']);
		
		SimpleListHelper::create('core_home',$this)
			->setPerPage(5)
			->setPerPageSelect([5,10,15])
			->setDataSource($dataSource)
			->setContent(function($item){
				if( $item->timer ){
					
					return "{$item->name} <small style='color:orange;'>schedulata</small>
					<p class='countdown' startdate='{$item->start_date}'></p>";
					
				}else{
					return $item->name;
				}
				
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
					return 'index.php?ctrl=Home&action=edit&id='.$item->id;
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
		DB::table('homepages')->where('id',$id)->delete();
		$this->displayMessage(_translate('homepages.form.messages.deleted'));
	}

	/**
	 * active item
	 *
	 * @param integer $id
	 * @return void
	 */
	private function active(int $id){
		DB::table('homepages')->update(array('active'=>0));
		DB::table('homepages')->where('id',$id)->update(array('active'=>1,'timer'=>0));		
		$this->displayMessage(_translate('homepages.list.messages.activated'));
	}

	/**
	 * disable item
	 *
	 * @param integer $id
	 * @return void
	 */
	private function disable(int $id){
		DB::table('homepages')->where('id',$id)->update(array('active'=>0));		
		$this->displayMessage(_translate('homepages.list.messages.disabled'));
	}

	

	
}



?>