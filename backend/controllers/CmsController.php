<?php
use Marion\Core\Marion;
use Marion\Controllers\Controller;
use Marion\Support\Form\FormHelper;

class CmsController extends Controller{
	public $_auth = 'cms_page';
	
	

	function display(){
		$action = $this->getAction();
		switch($action){
			case 'logo':
				$this->displayLogoForm();
				break;

		}
		

		
	}

	function displayLogoForm(){
		$this->setMenu('logo');
		$this->setTitle(_translate('cms.logo.form.title'));
	
		
		$fields = [
			'image' => [
				'type' => 'media:image',
                'label' => _translate('cms.logo.form.fields.image'),
				'validation' => 'required'
			]
		];

	  
        $form = FormHelper::create('form_logo_admin',$this)
            ->layoutFile(_MARION_ROOT_DIR_.'backend/templates/admin/forms/form_logo.xml')
            ->init(function(FormHelper $form){
				//controllo se il form è stato sottomesso
				if( $form->isSubmitted() ){

					
				}else{
					$data = [];
					$data['image'] = Marion::getConfig('cms_setting','logo');
					$form->formData->data = $data;
				}
            })->process(function(FormHelper $form){
                $data = $form->getValidatedData();
				Marion::setConfig('cms_setting','logo',$data['image']);
				$this->displayMessage(_translate('cms.logo.form.messages.updated'));
				

            })->setFields($fields);

        $form->display();
	}


}



?>