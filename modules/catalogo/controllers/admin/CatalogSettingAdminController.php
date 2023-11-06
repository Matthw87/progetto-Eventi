<?php
use Marion\Controllers\Controller;
use Marion\Core\Marion;
use Marion\Support\Form\FormHelper;

class CatalogSettingAdminController extends Controller{
	public $_auth = 'catalog';
	
	
	function display(){
		$this->setMenu('setting_catalog');
		$this->setTitle('Configurazione catalogo');

		$fields = [
			'type_view' => [
				'type' => 'select',
				'label' => "Tipo visualizzazione",
				'options' => [
					2 => '2 colonne',
					3 => '3 colonne',
					4 => '4 colonne',
				]
			],
			'enable_select_view_product' => [
				'type' => 'switch',
				'label' => "Abilita select tipo visualizzazione prodotti",
			],
			'enable_select_order_product' => [
				'type' => 'switch',
				'label' => "Abilita select ordinamento prodotti",
			],
			'enable_select_number_product_page' => [
				'type' => 'switch',
				'label' => "Abilita select numero prodotti per pagina",
			],
			'parameters_select_order_product' => [
				'type' => 'multiselect',
				'label' => "Parametri di ordinamento",
				'options' => [
					'sku' => 'Codice articolo',
					'name' => 'Nome articolo',
					//'low' => 'Prezzo basso-alto',
					//'hight' => "Prezzo alto-basso"
				]
			],
			'type_pager_section' => [
				'type' => 'select',
				'label' => "Tipologia paginazione",
				'options' => [
					'classic' => 'classic',
					'showMoreButton' => 'ajax (click)',
					'onScroll' => 'ajax (onscroll)',
				]
			]
		];

		FormHelper::create('catalogo_config',$this)
			->layoutFile(_MARION_MODULE_DIR_.'catalogo/templates/admin/forms/setting.xml')
			->setFields($fields)
			->init(function( FormHelper $form){
				if( !$form->isSubmitted() ){
					$data = Marion::getConfig('catalogo_setting');
					if( $data ){
						$form->formData->data = $data;
					}
				}
			})
			->process(function(FormHelper $form){

				$data = $form->getSubmittedData();

				foreach($data as $k => $v){
					if( $k == 'parameters_select_order_product' ) $v = serialize($v);
					Marion::setConfig('catalogo_setting',$k,$v);
				}
				Marion::refresh_config();
				$this->displayMessage('Configurazione salvata con successo');
			})
			->display();

	}

}



?>