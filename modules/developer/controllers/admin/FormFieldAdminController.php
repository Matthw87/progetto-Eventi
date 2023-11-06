<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use Marion\Support\Form\Form;
use Marion\Controllers\Elements\UrlButton;
use Marion\Support\ListWrapper\{DataSource, ListHelper};
use Illuminate\Database\Capsule\Manager as DB;
class FormFieldAdminController extends AdminModuleController{
	public $_auth = 'superadmin';

	
	
	
	function array_type(){
		
		$database = Marion::getDB();
		$list = $database->select('*','form_type',"1=1");
		foreach($list as $v){
			$select[$v['codice']] = $v['etichetta'];
		}
		//$select[41] = 'multicheckbox';
		
		return $select;
	}

	function array_tipi_data(){
		$typeData = Form::getTipoData();
		//debugga($typeTextarea);exit;
		$array_typeData = array();
		$array_typeData[0] = 'Seleziona...';
		foreach( $typeData as $v){
			$array_typeData[$v['codice']] = $v['etichetta'];
		}
		return $array_typeData;
	}


	function array_tipi_textarea(){
		$typeTextarea = Form::getTipoTextArea();
		//debugga($typeTextarea);exit;
		$array_typeTextArea = array();
		$array_typeTextArea[0] = 'Seleziona...';
		foreach( $typeTextarea as $v){
			$array_typeTextArea[$v['codice']] = $v['etichetta'];
		}
		return $array_typeTextArea;
	}

	function array_tipi(){
		
		$database = Marion::getDB();
		$list = $database->select('*','form_tipo',"1=1");
		$select[0] = 'Seleziona...';
		foreach($list as $v){
			$select[$v['codice']] = $v['etichetta'];
		}
		
		return $select;
	}

	function array_tipo_timestamp(){

		$typeTimestamp = Form::getTipoTimestamp();
		//debugga($typeTextarea);exit;
		$array_typeTimestamp = array();
		$array_typeTimestamp[0] = 'Seleziona...';
		foreach( $typeTimestamp as $v){
			$array_typeTimestamp[$v['codice']] = $v['etichetta'];
		}
		return $array_typeTimestamp;
	}

	function array_tipo_time(){


		$typeTime = Form::getTipoTime();
		
		$array_typeTime = array();
		$array_typeTime[0] = 'Seleziona...';
		foreach( $typeTime as $v){
			$array_typeTime[$v['codice']] = $v['etichetta'];
		}
		return $array_typeTime;
	}

	function array_tipo_file(){


		$typeFile = Form::getTipoFile();
		//debugga($typeTextarea);exit;
		$array_typeFile = array();
		//$array_typeFile[0] = 'Seleziona...';
		foreach( $typeFile as $v){
			$array_typeFile[$v['codice']] = $v['etichetta'];
		}
		return $array_typeFile;
	}


	function array_resize_image(){
		
		$image_conf = getConfig('image','options');
		$resize_image = $image_conf['resize'];
		$array_resize_image = [];
		if( okArray($resize_image)){
			foreach($resize_image as $v){
				$array_resize_image[$v] = $v;
			}
		}
		return $array_resize_image;
	}
	

	function getFieldsOption($multilocale=false){
		$campi =array(
			'valore' => array(
				'campo' => 'valore',
				'type' => 'text',
				'default' => '',
				'obbligatorio' => 1,
				'etichetta' => 'valore',
				'multilocale' => $multilocale,
			),
			'etichetta' => array(
				'campo' => 'etichetta',
				'type' => 'text',
				'default' => '',
				'obbligatorio' => 1,
				'etichetta' => 'etichetta',
				'multilocale' => $multilocale,
			),
			'multilocale' => array(
				'campo' => 'multilocale',
				'type' => 'hidden',
				'default' => $multilocale,
				'obbligatorio' => 0,
				'etichetta' => 'multilocale',
			),
			'campo' => array(
				'campo' => 'campo',
				'type' => 'hidden',
				'default' => '',
				'obbligatorio' => 0,
				'etichetta' => 'campo',
			),
		);
		return $campi;
	}
	
	function getFields($type='text'){
		$campi_campo =array(
			'codice' => array(
				'campo' => 'codice',
				'type' => 'hidden',
				'default' => '',
				'obbligatorio' => 0,
				'etichetta' => 'codice campo',
			),
			'dropzone' => array(
				'campo' => 'dropzone',
				'type' => 'hidden',
				'default' => 0,
				'obbligatorio' => 0,
				'etichetta' => 'dropzone',
			),
			'form' => array(
				'campo' => 'form',
				'type' => 'hidden',
				'default' => '',
				'obbligatorio' => 1,
				'etichetta' => 'codice form',
			),
			'type' => array(
				'campo' => 'type',
				'type' => 'hidden',
				'default' => '',
				'obbligatorio' => 1,
				'etichetta' => 'codice form',
			),
			'campo' => array(
				'campo' => 'campo',
				'type' => 'text',
				'default' => '',
				'lunghezzamin' => 2,
				//'postfunction' => 'strtolower',
				'obbligatorio' => 1,
				'etichetta' => 'campo',
			),
			'tipo'=>array(
				'campo'=>'tipo',
				'type'=>'select',
				'options' => $this->array_tipi(),
				'obbligatorio'=>0,
				'default'=>'0',
				'etichetta'=>'tipologia valore'
			),
			 'ordine'=>array(
				'campo'=>'ordine',
				'type'=>'text',
				'tipo' => 'Integer',
				'obbligatorio'=>0,
				'default'=>'',
				'etichetta'=>'ordine di controllo'
			),
			'obbligatorio'=>array(
				'campo'=>'obbligatorio',
				'type'=>'checkbox',
				'unique_value' => 1,
				'switch' => 1,
				'options' => array(1=>'SI'),
				'obbligatorio'=>0,
				'default'=>'0',
				'etichetta'=>'obbligatorio?',
				'ifisnull' => 2,
				'value_ifisnull' => 0,
			),
			'checklunghezza'=>array(
				'campo'=>'checklunghezza',
				'type'=>'checkbox',
				'unique_value' => 1,
				'switch' => 1,
				'options' => array(1=>'SI'),
				'obbligatorio'=>0,
				'default'=>'0',
				'ifisnull' => 2,
				'value_ifisnull' => 0,
				'etichetta'=>'controllo lunghezza',
			),
			'descrizione' => array(
				'campo' => 'descrizione',
				'type' => 'textarea',
				'default' => '',
				'etichetta' => 'descrizione',
			),
			'placeholder' => array(
				'campo' => 'placeholder',
				'type' => 'text',
				'default' => '',
				'etichetta' => 'placeholder',
			),
			'campo' => array(
				'campo' => 'campo',
				'type' => 'text',
				'default' => '',
				'lunghezzamin' => 2,
				//'postfunction' => 'strtolower',
				'obbligatorio' => 1,
				'etichetta' => 'name',
			),
			'etichetta' => array(
				'campo' => 'etichetta',
				'type' => 'text',
				'default' => '',
				'lunghezzamin' => 2,
				//'prefunction' => 'strtolower',
				//'postfunction' => 'strtolower',
				'obbligatorio' => 1,
				'etichetta' => 'etichetta',
				'descrizione' => 'nome del campo mostrato a video nel caso si sia verificato un errore'
			),
			'class' => array(
				'campo' => 'class',
				'type' => 'text',
				'default' => 'form-control',
				'obbligatorio' => 0,
				'etichetta' => 'classi css',
				'descrizione' => 'classi css da applicare al campo. I nomi delle classi devono essere separati da uno spazio.'
			),
			'tipo_textarea'=>array(
				'campo'=>'tipo_textarea',
				'type'=>'select',
				
				'options' => $this->array_tipi_textarea(),
				'obbligatorio'=>0,
				'default'=>'0',
				'etichetta'=>'javascript editor'
			),
			'tipo_data'=>array(
				'campo'=>'tipo_data',
				'type'=>'select',
				/*'origine_dati' => 'php',
				'function_php' => 'array_type',*/
				'options' => $this->array_tipi_data(),
				'obbligatorio'=>0,
				'default'=>'0',
				'etichetta'=>'javascript'
			),
			'tipo_timestamp'=>array(
				'campo'=>'tipo_timestamp',
				'type'=>'select',
				/*'origine_dati' => 'php',
				'function_php' => 'array_type',*/
				'options' => $this->array_tipo_timestamp(),
				'obbligatorio'=>0,
				'default'=>'0',
				'etichetta'=>'javascript'
			),
			'tipo_file'=>array(
				'campo'=>'tipo_file',
				'type'=>'select',
				/*'origine_dati' => 'php',
				'function_php' => 'array_type',*/
				'options' => $this->array_tipo_file(),
				'obbligatorio'=>0,
				'default'=>'0',
				'etichetta'=>'tipo'
			),
			'tipo_time'=>array(
				'campo'=>'tipo_time',
				'type'=>'select',
				/*'origine_dati' => 'php',
				'function_php' => 'array_type',*/
				'options' => $this->array_tipo_time(),
				'obbligatorio'=>0,
				'default'=>'0',
				'etichetta'=>'tipo'
			),

			'tipo_valori'=>array(
				'campo'=>'tipo_valori',
				'type'=>'checkbox',
				'unique_value' => 1,
				'switch' => 1,
				'options' => array(1=>'SI'),
				'obbligatorio'=>0,
				'default'=>'0',
				'ifisnull' => 2,
				'value_ifisnull' => 0,
				'etichetta'=>'Opzioni statiche?',
			),
			
			'gettext'=>array(
				'campo'=>'gettext',
				'type'=>'checkbox',
				'unique_value' => 1,
				'switch' => 1,
				'options' => array(1=>'SI'),
				'obbligatorio'=>0,
				'default'=>'0',
				'ifisnull' => 2,
				'value_ifisnull' => 0,
				'etichetta'=>'gettext',
				'descrizione' => "permette di parametrizzare l'etichetta in più lingue. Se viene selezionato il campo <b>etichetta</b> non potrà ammettere spazi."
			),
			'default_value' => array(
				'campo' => 'default_value',
				'type' => 'text',
				'default' => '',
				'obbligatorio' => 0,
				'etichetta' => 'valore di default',
				'descrizione' => 'valore di default assunto dal campo quando il form viene istanziato'
			),
			'multilocale'=>array(
				'campo'=>'multilocale',
				'type'=>'checkbox',
				'unique_value' => 1,
				'switch' => 1,
				'options' => array(1=>'SI'),
				'obbligatorio'=>0,
				'default'=>'0',
				'ifisnull' => 2,
				'value_ifisnull' => 0,
				'etichetta'=>'gestione piu\' lingue',
				'descrizione' => "il campo verrà ripetuto tante volte quante lingue sono gestite dal sito."
			),
			'lunghezzamin' => array(
				'campo' => 'lunghezzamin',
				'type' => 'text',
				'default' => '',
				'tipo' => 'Integer',
				'obbligatorio' => 0,
				'etichetta' => 'lunghezza minima valore',
			),
			'lunghezzamax' => array(
				'campo' => 'lunghezzamax',
				'type' => 'text',
				'default' => '',
				'tipo' => 'Integer',
				'obbligatorio' => 0,
				'etichetta' => 'lunghezza massima valore',
			),
			 'unique_value'=>array(
				'campo'=>'unique_value',
				'type'=>'checkbox',
				'unique_value' => 1,
				'switch' => 1,
				'options' => array(1=>'SI'),
				'obbligatorio'=>0,
				'default'=>'0',
				'ifisnull' => 2,
				'value_ifisnull' => 0,
				'etichetta'=>'Assume un unico valore?',
				'descrizione' => "Stabilisce se la checkbox assume un solo valore. Un esempio può essere il campo per l'accettazione della privacy/consenso. In caso contrario il campo riuslta essere una multichecbox"
			),
			'post_function' => array(
				'campo' => 'post_function',
				'type' => 'text',
				'default' => '',
				//'postfunction' => 'strtolower',
				'obbligatorio' => 0,
				'etichetta' => 'funzioni applicate al campo dopo il controllo',
				'placeholder' => 'strtolower,strtoupper,unserialize,serailize,etc',
				'descrizione' => 'funzioni php applicate al campo dopo il controllo dei dati. Le funzioni devono essere separate da una virgola e devono essere dichiarate nello scope.'
			),
			'function_template'=>array(
				'campo'=>'function_template',
				'type'=>'text',
				'obbligatorio'=>0,
				'etichetta'=>'funzione che genera le opzioni',
				'descrizione' => "N.B. la funzione deve essere dichiarata all'interno dello scope in cui il form è richiamato oppure deve essere un metodo del controller"
			),
			 'pre_function' => array(
				'campo' => 'pre_function',
				'type' => 'text',
				'default' => '',
				//'postfunction' => 'strtolower',
				'obbligatorio' => 0,
				'etichetta' => 'funzioni applicate al valore del campo prima di mostrare il form',
				'placeholder' => 'strtolower,strtoupper,unserialize,serialize,etc',
				'descrizione' => 'funzioni php applicate al quando il form viene istanziato. Le funzioni devono essere separate da una virgola e devono essere dichiarate nello scope.'
			),
			'ext_image'=>array(
				'campo'=>'ext_image',
				'type'=>'checkbox',
				'options'=>array('gif'=>'gif','png'=>'png','jpeg'=>'jpeg','jpg'=>'jpg'),
				'default'=>array('gif','png','jpeg','jpg'), //i campi selezionati default vengono messi in un array
				'obbligatorio'=>0,
				'postfunction' => 'serialize',
				'prefunction' => 'unserialize',
				'etichetta'=>'estensioni immagini consetite',
			),
			'dimension_resize_default'=>array(
				'campo'=>'dimension_resize_default',
				'type'=>'checkbox',
				'unique_value' => 1,
				'switch' => 1,
				'options' => array(1=>'SI'),
				'obbligatorio'=>0,
				'default'=>'0',
				'ifisnull' => 2,
				'value_ifisnull' => 0,
				'etichetta'=>'dimensioni resize immagini default',
			),
			'resize_image'=>array(
				'campo'=>'resize_image',
				'type' => 'multiselect',
				'options'=> $this->array_resize_image(),
				'default'=>$this->array_resize_image(), //i campi selezionati default vengono messi in un array
				'obbligatorio'=>0,
				'postfunction' => 'serialize',
				'prefunction' => 'unserialize',
				'etichetta'=>'resize immagini',
			),
			'ext_attach'=>array(
				'campo'=>'ext_attach',
				'type'=>'checkbox',
				'options'=>array('gif'=>'gif','png'=>'png','jpeg'=>'jpeg','jpg'=>'jpg','zip'=>'zip','tar'=>'tar','doc'=>'doc','docx'=>'docx','xls'=>'xls','txt'=>'txt','rar'=>'rar','csv'=>'csv','pdf'=>'pdf'),
				'default'=>array('gif','png','jpeg','jpg','zip','tar','doc','docx','xls','txt','rar','csv','pdf'), //i campi selezionati default vengono messi in un array
				'obbligatorio'=>0,
				'postfunction' => 'serialize',
				'prefunction' => 'unserialize',
				'etichetta'=>'estensioni allegati consetite',
			),
			'value_ifisnull' => array(
				'campo' => 'value_ifisnull',
				'type' => 'text',
				'default' => '',
				'obbligatorio' => 0,
				'etichetta' => 'valore che se il campo è nullo',
			),
			 'number_files' => array(
				'campo' => 'number_files',
				'type' => 'text',
				'default' => '0',
				'tipo' => 'Integer',
				'obbligatorio' => 0,
				'etichetta' => 'numer massimo di file',
				'descrizione' => "il valore <b>0</b> indica infinito"
			),
					
			

		);
		
		return $campi_campo;
	}



	function displayContent(){
		$this->setMenu('developer_forms');
		if( $this->isSubmitted() ){
			
			$dati = $this->getFormdata();
			$fields = $this->getFieldsOption($dati['multilocale']);
			$form = new Form();
			$form->addElements($fields);
			$array = $form->checkData($dati);
			if( $array[0] == 'ok'){
				$database = Marion::getDB();
				if($dati['multilocale']){
					$id_valore_campo = null;
					foreach($array['_locale_data'] as $loc => $v){
						$data = array(
							'valore' => $v['valore'],
							'etichetta' => $v['etichetta'],
							'campo' => $array['campo'],
							'locale' => $loc
						);
						
						 $database->insert('form_valore',$data);

					}
				}else{
					//$database->insert()
				}
			}else{
				$this->errors[] = $array[1];
			}
		}else{
			$fields = $this->getFieldsOption(_var('multilocale'));
			$fields['campo']['default'] = _var('campo');
			$form = new Form();
			$form->addElements($fields);
			
		}

		$dataform = $form->prepareData($dati,$this);
		$this->setVar('dataform',$dataform);
		$this->output('@developer/admin/form/form_option.htm');
	}


	function displayFormNewField(){
		$fields = $this->array_type();

		

		$this->setVar('select',$fields);
		$this->output('@developer/admin/form_new_form_field.htm');
	}


	function getDati(): array{
		$id = $this->getID();
		if( $id ){
			$database = Marion::getDB();
			$dati = $database->select('*','form_campo',"codice={$id}");
			
			return $dati[0];
		}else{
			return [];
		}
		
	}

	function displayFormField($type){
		$fields = $this->getFields('text');
		
		
		$form = new Form();
		$form->addElements($fields);
		$database = Marion::getDB();

		
		
		if( $this->isSubmitted()){
			
			$dati = $this->getFormdata();
			if( $dati['checklunghezza'] ){
				$form->campi_aggiunti['lunghezzamin']['obbligatorio'] = 1;
				$form->campi_aggiunti['lunghezzamax']['obbligatorio'] = 1;
			}
			
			
			

			
			$array = $form->checkData($dati);
			//debugga($form);exit;
			$action = $this->getAction();
			$resize_aviable = $dati['resize_image'];
			if( array_key_exists('dimension_image',$dati) && okArray($dati['dimension_image']) ){
				foreach($dati['dimension_image'] as $k => $v){
					$chiave = preg_replace('/_[xy]/','',$k);
					$_dimension = preg_replace('/(.*)_/','',$k);
					
					if( in_array($chiave,$resize_aviable) ){
						if( $v ){
							if( !is_numeric($v) || (float)$v < 0 ){
								$array[0] = 'nak';
								$array[1] = "La dimensione {$_dimension} del resize {$chiave} non è corretta";
								break;
							}
						}else{
							$array[0] = 'nak';
							$array[1] = "La dimensione {$_dimension} del resize {$chiave} non è specificata";
							break;
						}
					}

				}
			}

			
			
			$this->setVar('dimension_image',$dati['dimension_image']);
			if( $array[0] == 'ok'){
				$array['dimension_image'] = serialize($dati['dimension_image']);

				
				
			}
			
			if( $array[0] == 'ok'){
				unset($array[0]);
				if( $action== 'edit' ){
					
					$database->update('form_campo',"codice={$array['codice']}",$array);
					$this->saveOptionsField($array['codice'],$array['multilocale']);
				}else{
					$id = $database->insert('form_campo',$array);
					$this->saveOptionsField($id,$array['multilocale']);
				}
				
				$this->redirectToList(array('saved'=>1,'id_form'=>$array['form']));
				

			}else{
				$this->errors[] = $array[1];
				
			}

		}else{
			$dati = $this->getDati();
			
			/*if($type == 41 ){ 
				//caso della multicheckbox
				$multicheckbox =true;
				$type = 4;
			}*/
			if( !okArray($dati) ){
				$dati['type'] = $type;
				$dati['form'] = _var('form');
				if( $type == 5 ){
					$dati['dropzone'] = 1;
					$options_image = Marion::getConfig('image','options');
					
					$this->setVar('dimension_image',$options_image);
				}

			}else{
				$options_image = unserialize($dati['dimension_image']);
				$this->setVar('dimension_image',$options_image);
			}
			
			if($dati['codice']){
				if($dati['multilocale']){
					$form_options = $this->getOptionsFieldMultilocale($dati['codice']);
					$this->setVar('form_options_multilocale',$form_options);
				}else{
					$form_options = $this->getOptionsField($dati['codice']);
					$this->setVar('form_options',$form_options);
				}
				
			}

			if(	okArray($dati) && $this->getAction() == 'duplicate' ){
				unset($dati['codice']);
			}
			
			
			
			
			
			
		}
		
		
		$dataform = $form->prepareData($dati,$this);
		$this->setVar('dataform',$dataform);
		

		
		//$options = $database->select('*','form_valore',"1=1");
		//debugga($dati);exit;
		if( $dati['type'] == 5 ){
			$this->output('@developer/admin/form/form_file.htm');
		}elseif ($dati['type'] == 4 || $dati['type'] == 2 || $dati['type'] == 3 || $dati['type'] == 9 ){
			if($dati['type'] == 4){
				//caso della checkbox
				$this->setVar('checkbox',true);
			}
			$this->output('@developer/admin/form/form_select.htm');
		}else{
			$this->output('@developer/admin/form/form_text.htm');
		}

		
	}

	function displayForm(){
			$this->setMenu('developer_forms');
			$action = $this->getAction();
			$type = _var('type');
			
			$this->resetToolButtons();
			
			
			$dati = $this->getDati();
			if( okArray($dati) ){
				$id_form  = $dati['form'];
				$type = $dati['type'];
			}else{
				$id_form = _var('id_form');
				
				$formdata = $this->getFormdata();
				if( !$type ) $type = $formdata['type'];
				if( $action == 'add' && !$type && ! $this->isSubmitted()){
					$this->setVar('id_form',$id_form);
					$this->displayFormNewField();
					exit;
				}
			}
			if( $id_form ){
				$this->addToolButton(
					(new UrlButton('back'))
						->setText('Indietro')
						->setIcon('fa fa-arrow-left')
						->setUrl('index.php?mod=developer&ctrl=FormFieldAdmin&action=list&id_form='.$id_form)
				);
			}
			
			
			$this->displayFormField($type);
			
			/*$database = Marion::getDB();

			$fields = $this->getFields();
			$form = new Form();
			$form->addElements($fields);
			$id = $this->getID();
			if( $this->isSubmitted()){
				$formdata = $this->getFormdata();
				
				$array = $form->checkData($formdata);
				if( $array[0] == 'ok'){
					unset($array[0]);
					if( $formdata['codice'] ){
						$database->update('form',"codice={$id}",$array);
					}else{
						$database->insert('form',$array);
					}
					$this->redirectToList(array('saved'=>1));
				}else{
					$this->errors[] = $array[1];
					$dati = $formdata;
				}
			}else{
				$database = Marion::getDB();
				
				$dati = $database->select('*','form',"codice={$id}");
				if( okArray($dati) ) $dati = $dati[0];
			}


			$action = $this->getAction();
			if( $action == 'edit'){
				$dati['id'] = $dati['codice'];
			}else{
			}
			
			if( $action == 'duplicate'){
				unset($dati['codice']);
			}
			
			$dataform = $form->prepareData($dati,$this);
			
			$this->setVar('dataform',$dataform);
			$this->output('form_form_field.htm');*/
	}


	function getForm($id){
		$database = Marion::getDB();
		$form = $database->select('*','form',"codice={$id}");
		return $form[0];
	}
	

	function switchOrder($field1,$field2){
		
		$database = Marion::getDB();
		$a =$database->select('*','form_campo',"codice={$field1}");
		$b = $database->select('*','form_campo',"codice={$field2}");
		$a = $a[0]['ordine'];
		$b = $b[0]['ordine'];

		
		$database->update('form_campo',"codice={$field1}",array('ordine'=>$b));
		$database->update('form_campo',"codice={$field2}",array('ordine'=>$a));
	}


	
	/**
	 * Display List Forms
	 *
	 * @return void
	 */
	function displayList(){
		
		$this->setMenu('developer_forms');

		$id_form = _var('id_form');

		$form = $this->getForm($id_form);
		$this->setTitle(_translate(['Campi del form <b>%s</b>',$form['nome']],'developer'));

		if( _var('updated') ){
			$this->displayMessage(_translate('form_updated','developer'));
		}
		if( _var('inserted') ){
			$this->displayMessage(_translate('form_added','developer'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('form_deleted','developer'));
		}

		$dataSource = new DataSource('form_campo');
		$dataSource->queryBuilder()->where('form_campo.form',_var('id_form'));
		$dataSource->addFields(['form_campo.codice','form_campo.campo','form_campo.etichetta','form_campo.type','form_campo.obbligatorio']);


		$type_list = $this->getTypeList();
		$fields = array(
			array(
				'name' => 'ID',
				'field_value' => 'codice',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'codice',
				'search_name' => 'codice',
				'search_value' => '',
				'search_type' => 'input',
			),
			array(
				'name' => 'Name',
				'field_value' => 'campo',
				'sortable' => true,
				'sort_id' => 'campo',
				'searchable' => true,
				'search_name' => 'campo',
				'search_value' => _var('campo'),
				'search_type' => 'input',
			),
			array(
				'name' => 'Etichetta',
				'field_value' => 'etichetta',
				'sortable' => true,
				'sort_id' => 'etichetta',
				'searchable' => true,
				'search_name' => 'etichetta',
				'search_value' => _var('etichetta'),
				'search_type' => 'input',
			),
			array(
				'name' => 'Tipo',
				'function_type' => 'row',
				'function' => function($row) use ($type_list){
					if( array_key_exists($row->type,$type_list) ){
						return $type_list[$row->type];
					}
					return '';
				},
				'sortable' => true,
				'sort_id' => 'type',
				'searchable' => true,
				'search_name' => 'type',
				'search_value' => _var('type'),
				'search_type' => 'select',
				'search_options' => $type_list
			),
			array(
				'name' => 'Obbligatorio',
				'function_type' => 'row',
				'function' => function($row){
					if( _var('export') ){
						if( $row->obbligatorio ){
							$html = strtoupper(_translate('general.yes'));
						}else{
							$html = strtoupper(_translate('general.no'));
						}
					}else{
						if( $row->obbligatorio ){
							$html = "<span class='label label-success'  id='field_{$row->codice}_online' style='cursor:pointer;' onclick='change_mandatory({$row->codice}); return false;'>".strtoupper(_translate('general.yes'))."</span>";
							$html .= "<span class='label label-danger' id='field_{$row->codice}_offline' style='cursor:pointer; display:none;' onclick='change_mandatory({$row->codice}); return false;'>".strtoupper(_translate('general.no'))."</span>";
						}else{
							$html = "<span class='label label-danger' id='field_{$row->codice}_offline' style='cursor:pointer;' onclick='change_mandatory({$row->codice}); return false;'>".strtoupper(_translate('general.no'))."</span>";
							$html .= "<span class='label label-success'  id='field_{$row->codice}_online' style='cursor:pointer;display:none;' onclick='change_mandatory({$row->codice}); return false;' >".strtoupper(_translate('general.yes'))."</span>";
						}
					}
			
					return $html;
				},
				'searchable' => true,
				'search_name' => 'obbligatorio',
				'search_value' => (isset($_GET['obbligatorio']))? _var('obbligatorio'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => _translate('general.select..'),
					0 => 'NO',
					1 => 'SI',
					
				),
			),
			array(
				'name' => '',
				'function_type' => 'row',
				'function' => 'getOrderButtons',
			),
			


		);


		$this->resetToolButtons();
		$this->addToolButton(
			(new UrlButton('add'))
				->setUrl('index.php?mod=developer&ctrl=FormFieldAdmin&action=add&id_form='._var('id_form'))
				->setIcon('fa fa-plus')
				->setClass('btn btn-principale')
				->setText('aggiungi')
		);
		
		
		ListHelper::create('form_fields_list_developer',$this)
			->setRowId('codice')
			->enableExport(true)
			->setDataSource($dataSource)
			->setExportTypes(['pdf','csv','excel'])
			->enableBulkActions(true)
			->enableSearch(true)
			->setFieldsFromArray($fields)
			->addEditActionRowButton()
			->addCopyActionRowButton()
			->addDeleteActionRowButton(function($row){
				return _translate(['confirm_delete_form_field',$row->campo],'developer');
			})
			->onDelete(function($id){
				//eliminazione del form
				if( DB::table('form_campo')->where('codice',$id)->exists() ){
					DB::table('form_campo')->where('codice',$id)->delete();
				}
				$this->displayMessage(_translate('form_field_deleted','developer'));
				
			})
			->onSearch(function(\Illuminate\Database\Query\Builder $query){
				if( $name = _var('name') ){
					$query->where('campo','like',"%{$name}%");
				}
				if( $etichetta = _var('etichetta') ){
					$query->where('etichetta','like',"%{$etichetta}%");
				}
				if( $id = _var('codice') ){
					$query->where('codice',$id);
				}
			})->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
				if( in_array($field,['campo','codice','etichetta','type'])){
					$query->orderBy($field,$order);
				}
			})->enableBulkActions(false)
			->display();

			
			
	}

	function displayListOld(){
		$this->setMenu('developer_forms');
		$id_form = _var('id_form');
		

		$move = _var('switch');
		if( $move ){
			$field1 = _var('field1');
			$field2 = _var('field2');
			$this->switchOrder($field1,$field2);
		}
		
		
		

		$form = $this->getForm($id_form);
		
		
		if( _var('saved') ){
			$this->displayMessage(_translate('form_field_saved'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('form_field_deleted'),'success');
		}
		
		$database = Marion::getDB();
		$select_types = $database->select('*','form_type');
		
		$list_type = array('--SELECT--');
		foreach($select_types as $v){
			$list_type[$v['codice']] = $v['etichetta'];
		}
		
		$this->form_types = $list_type;
		$fields = array(
			
			0 => array(
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',
			),
			1 => array(
				'name' => 'Name',
				'field_value' => 'name',
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			),
			2 => array(
				'name' => 'Etichetta',
				'field_value' => 'etichetta',
				'sortable' => true,
				'sort_id' => 'etichetta',
				'searchable' => true,
				'search_name' => 'etichetta',
				'search_value' => _var('etichetta'),
				'search_type' => 'input',
			),
			3 => array(
				'name' => 'Tipo',
				'function_type' => 'row',
				'function' => 'getTipoHtml',
				'sortable' => true,
				'sort_id' => 'type',
				'searchable' => true,
				'search_name' => 'type',
				'search_value' => _var('type'),
				'search_type' => 'select',
				'search_options' => $list_type
			),
			4 => array(
				'name' => 'Obbligatorio',
				'function_type' => 'row',
				'function' => 'isObbligatorio',
				'searchable' => true,
				'search_name' => 'obbligatorio',
				'search_value' => (isset($_GET['obbligatorio']))? _var('obbligatorio'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => '--SELECT--',
					0 => 'NO',
					1 => 'SI',
					
				),
			),
			5 => array(
				'name' => '',
				'function_type' => 'row',
				'function' => 'getOrderButtons',
			),
			
			

		);
	
		$this->resetToolButtons();
		$this->addToolButton(
			(new UrlButton('add'))
			->setText(_translate('add'))
			->setIcon('fa fa-plus')
			->setUrl($this->getUrlAdd()."&id_form={$form['codice']}")
			->setClass('btn btn-principale')
		)->addToolButton(
			(new UrlButton('back'))
			->setText(_translate('back'))
			->setIcon('fa fa-arrow-left')
			->setUrl("index.php?mod=developer&ctrl=FormAdmin&action=list")
			->setClass('btn btn-secondario')
	
		);
		

		$this->setTitle(_translate(['Campi del form <b>%s</b>',$form['nome']],'developer'));

		//$this->setListOption('fields',$fields);
		//$this->getList();


		

		parent::displayList();
	}

	

	function getOrderButtons($row){
		$html = '';
		if ($row['prec']){
			$html .= "<button onclick=\"document.location.href='index.php?ctrl=FormFieldAdmin&mod=developer&action=list&switch=up&field1={$row['id']}&field2={$row['prec']}&id_form={$row['form']}'\"><i class='fa fa-arrow-up'></i></button>";
		}

		if ($row['succ']){
			$html .= "<button onclick=\"document.location.href='index.php?ctrl=FormFieldAdmin&mod=developer&action=list&switch=down&field1={$row['id']}&field2={$row['succ']}&id_form={$row['form']}'\"><i class='fa fa-arrow-down'></i></button>";
		}
		return $html;
	}

	function bulk(){
		$action = $this->getBulkAction();
		$ids = $this->getBulkIds();
		$database = Marion::getDB();
		switch($action){
			
			case 'delete':
				foreach($ids as $id){
					
					$database->delete('form_campo',"codice={$id}");
					
				}
				break;
			case 'rendi_obbligatorio':
				foreach($ids as $id){
					
					$database->update('form_campo',"codice={$id}",array('obbligatorio' => 1));
					
				}
				break;
			case 'rendi_non_obbligatorio':
				foreach($ids as $id){
					
					$database->update('form_campo',"codice={$id}",array('obbligatorio' => 1));
					
				}
				break;

			
		}
		parent::bulk();
	}

	function delete(){
		$id = $this->getID();
	
		$database = Marion::getDB();

		$data = $database->select('*','form_campo',"codice={$id}");
	
		if( okArray($data) ){
			$database->delete('form_campo',"codice={$id}");
			$id_form = $data[0]['form'];
		}
		parent::delete();
		//$this->redirectToList(array('deleted'=>1,'id_form'=>$id_form));
		

		
	}


	function setMedia(){
			$this->registerJS($this->getBaseUrl().'modules/developer/js/form.js?v=4','end');
			$this->registerCSS($this->getBaseUrl().'plugins/iziModal/css/iziModal.min.css');
			$this->registerJS($this->getBaseUrl().'plugins/iziModal/js/iziModal.min.js','end');
	}



	function ajax(){
		$action = $this->getAction();
		switch($action){
			case 'mandatory':
				$id = $this->getID();
				$database = Marion::getDB();
				$check = $database->select('*','form_campo',"codice={$id}");
				//debugga($database->lastquery);exit;
				if( okArray($check) ){
					$mandatory = $check[0]['obbligatorio'];
					$database->update('form_campo',"codice={$id}",array('obbligatorio'=>(int)!$mandatory));

					$risposta = array(
						'result' => 'ok',
						'status' => (int)!$mandatory
					);
				}else{
					$risposta = array(
						'result' => 'nak'
					);
				}
				
				
			break;
		}


		echo json_encode($risposta);
	}
	

	function getOptionsFieldMultilocale($codice){
		$database = Marion::getDB();
		$options = $database->select('*','form_valore',"campo={$codice} order by ordine");
		$values = array();
		if(okArray($options)){
			foreach($options as $v){
				$values[$v['locale']][$v['valore']] = $v['etichetta'];
			}
		}
		return $values;
	}
	function getOptionsField($codice){
		$database = Marion::getDB();
		$options = $database->select('*','form_valore',"campo={$codice} order by ordine");
		$values = array();
		if(okArray($options)){
			foreach($options as $v){
				$values[$v['valore']] = $v['etichetta'];
			}
		}
		return $values;
	}


	function saveOptionsField($codice,$multilocale){
		$database = Marion::getDB();
		if($multilocale){
			$options_form = _var('formdata1');
			if(okArray($options_form)){
				foreach($options_form as $loc  => $values){
					
					foreach($values as $campo => $dati){
						foreach($dati as $k => $v){
							$_form_valore[$loc][$k][$campo] = $v;
							$_form_valore[$loc][$k]['locale'] = $loc;
							$_form_valore[$loc][$k]['campo'] = $codice;
						}
						
					}
				}
			
			}
			$database->delete('form_valore',"campo={$codice}");
			foreach($_form_valore as $loc => $values){
				foreach($values as $dati){
					$database->insert('form_valore',$dati);
				}
			}
		}else{
			$options_form = _var('formdata2');
			
			if(okArray($options_form)){
				
				
				foreach($options_form as $campo => $dati){
					foreach($dati as $k => $v){
						$_form_valore[$k][$campo] = $v;
						$_form_valore[$k]['locale'] = $GLOBALS['activelocale'];
						$_form_valore[$k]['campo'] = $codice;
					}
					
				}
				
			
			}
			//debugga($_form_valore);exit;
			$database->delete('form_valore',"campo={$codice}");
			if( okArray($_form_valore) ){
				foreach($_form_valore as $dati){
					$database->insert('form_valore',$dati);
					
				}
			}
			

		}

		
		
	}

	private function getTypeList(): array{
		$database = Marion::getDB();
		$select_types = $database->select('*','form_type');
		
		$list_type = array(_translate('general.select..'));
		foreach($select_types as $v){
			$list_type[$v['codice']] = $v['etichetta'];
		}
		return $list_type;
	}


}