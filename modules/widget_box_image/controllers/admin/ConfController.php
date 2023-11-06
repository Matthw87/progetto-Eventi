<?php

use Marion\Controllers\Controller;
use Marion\Support\Form\FormHelper;
use Illuminate\Database\Capsule\Manager as DB;

class ConfController extends Controller{
	//use FormHelper;
	public $_auth = 'cms';


	/**
	 * Display Form
	 *
	 * @return void
	 */
	function display(){
		
		
		$fields = [
			'id_box' => [
				'type' => 'hidden'
			],
			'image' => [
				'type' => 'media:image',
                'label' => _translate('form.fields.image','widget_box_image'),
				'validation'=>  ['required'],
				'multilang' => true
			],
			'image_webp' => [
				'type' => 'media:image',
                'label' => _translate('form.fields.image_webp','widget_box_image'),
				'validation'=>  ['required'],
				'multilang' => true
			],
			'label' => [
				'type' => 'text',
                'label' => _translate('form.fields.label','widget_box_image'),
				'validation'=>  ['max:100'],
			],
			'title' => [
				'type' => 'text',
                'label' => _translate('form.fields.title','widget_box_image'),
				'validation'=> ['max:100'],
				'multilang' => true
			],
			'button_text' => [
				'type' => 'text',
                'label' => _translate('form.fields.button_text','widget_box_image'),
				'validation'=> 'max:255',
				'multilang' => true
			],
			'url' => [
				'type' => 'text',
                'label' => _translate('form.fields.url','widget_box_image'),
				'validation'=> 'max:500',
				'multilang' => true
			],
			'hover' => [
				'type' => 'select',
                'label' => _translate('form.fields.hover','widget_box_image'),
				'options' => [
					'' => '------',
					'hover01' => 'Zoom In',
					'hover03' => 'Zoom Out',
					'hover05' => 'Slide',
					'hover06' => 'Rotate',
					'hover07' => 'Blur',
					'hover08' => 'Gray Scale',
					'hover09' => 'Sepia',
					'hover10' => 'Blur + Gray Scale',
					'hover11' => 'Opacity',
					'hover16' => 'Overlay',
					'hover13' => 'Flashing',
				]
			],
			"target_blank" => [
				'type' => 'switch',
                'label' => _translate('form.fields.target_blank','widget_box_image'),
			]
		];

		
        $form = FormHelper::create('widget_box_image',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'widget_box_image/templates/admin/forms/widget.xml')
            ->init(function(FormHelper $form){
				//controllo se il form è stato sottomesso
				if( !$form->isSubmitted() ){
					$id_box = _var('id_box');
					$data = DB::table('composed_page_composition_tmp')->where('id',$id_box)->first();
					if( $data ){
						if( isset($data->parameters) ){
							$data =  unserialize($data->parameters);
							if( okArray($data) ){
								$form->formData->data = $data;
							}
						}else{
							$form->formData->data = [];
						}	
					}
					$form->formData->data['id_box'] = $id_box;
				}
            })->process(function(FormHelper $form){
                $data = $form->getValidatedData();
				DB::table('composed_page_composition_tmp')
					->where('id',$data['id_box'])
					->update(['parameters' => serialize($data)]);
				$this->displayMessage('Dati salati con successo!','success');
			

            })->setFields($fields);

        $form->displayPopup();
	}

}



?>