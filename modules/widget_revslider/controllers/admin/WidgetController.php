<?php

use Marion\Controllers\Controller;
use Marion\Support\Form\FormHelper;
use Illuminate\Database\Capsule\Manager as DB;

class WidgetController extends Controller{
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
			'slider_id' => [
				'type' => 'select',
                'label' => _translate('form.fields.slider','widget_revslider'),
				'options' => function(){
					$sliders = DB::table('revolution_slider')->get()->toArray();
					$options = [];
					foreach($sliders as $s){
						$options[$s->id] = $s->title;
					}
					return $options;
				},
				'multilang' => true
			]
		];

		
        $form = FormHelper::create('widget_revslider',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'widget_revslider/templates/admin/forms/widget.xml')
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