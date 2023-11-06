<?php
namespace Marion\Controllers;
use Marion\Controllers\Elements\UrlButton;
use Marion\Controllers\Elements\ListContainer;
use JasonGrimes\Paginator;
use Marion\Support\Form\Traits\FormHelper;
use Marion\Support\Pdf;

class AdminController extends Controller{
	use FormHelper;
	
	public $bulk_action = '';
	public $bulk_ids = array();
	public $bulk_form = array();

	public $_page_id;
	public $_per_page;
	public $_id;

	private $_list_container; //oggetto list container



	/* LIST ATTRIBUTES */
	public $_limit_list = 25;
	public $_list_options = array(
		'html_template' => '@core/layouts/list/list.htm',
		'search' => true, //abilita la ricerca
		'export' => true, //abilita l'esportazione
		'export_types' => array('excel','csv','pdf'),
		'bulk_actions' => array(
			'enabled' => true,
			'field_id' => 'id',
			'actions' => array(
				'delete' => array(
					'text' => 'elimina',
					'icon_type' => 'icon',
					'icon' => 'fa fa-trash-o',
					'img' => '',
					'confirm' => true,
					'confirm_message' => 'Sicuro di voler procedere con questa operazione?',
					'ajax_content' => '', //metodo del controller che si occupa di creare il contenuto del form bulk
					'custom_fields' => array() //campi aggiuntivi inoltrati al controller quando viene sottomessa l'azione bulk
					),
				),
		),
		'row_actions' => array(
			'enabled' => true,
			'field_id' => 'id',
			'actions' => array(
				'edit' => array(
					'text' => 'modifica',
					'target_blank' => 0,
					'icon_type' => 'icon',
					'icon' => 'fa fa-pencil',
					'img' => '',
					'url' => '{{script_url}}&action=edit&id={{field_id}}',
					),
				'duplicate' => array(
					'text' => 'duplica',
					'target_blank' => 0,
					'icon_type' => 'icon',
					'icon' => 'fa fa-copy',
					'img' => '',
					'url' => '{{script_url}}&action=duplicate&id={{field_id}}',
				),
				'delete' => array(
					'text' => 'elimina',
					'target_blank' => 0,
					'icon_type' => 'icon',
					'icon' => 'fa fa-trash-o',
					'img' => '',
					'url' => '{{confirm_delete_url}}&id={{field_id}}',
				),
			),
					
		),
		'fields' => array(),
		'total_items' => 0,
		'per_page' => 25,
		'max_pages_to_show' => 4
	);
	public $_data_list = array();
	public $_list = array();
	public $_header_list = array();


	/*


	function enableBulkActions($bool){
		$this->_list_options['bulk_actions']['enabled'] = $bool;
	}
	function removeListBulkAction($name){
		if( isset($this->_list_options['bulk_actions']['actions'][$name]) ){
			unset($this->_list_options['bulk_actions']['actions'][$name]);
		}
	}
	function removeListRowAction($name){
		if( isset($this->_list_options['row_actions']['actions'][$name]) ){
			unset($this->_list_options['row_actions']['actions'][$name]);
		}
	}

	function addListBulkAction($name,$options){
		$this->_list_options['bulk_actions']['actions'][$name] = $options;
	}

	function addListRowAction($name,$options){
		$this->_list_options['row_actions']['actions'][$name] = $options;
	}
	*/


	/*function setListOption($key,$value){
			
			if( array_key_exists($key,$this->_list_options) ){
				$options = $this->_list_options[$key];
				
				if( is_array($value) && !in_array($key,array('fields'))){
				
					if( is_array($options) ){

						foreach($value as $k => $v){
							if( array_key_exists($k,$options) ){
								$this->_list_options[$key][$k] = $v;
							}
						}
					}
				}else{
					
					$this->_list_options[$key] = $value;
					
				}
			}
			
			
	}

	function getListOption($key){
			if( array_key_exists($key,$this->_list_options) ){
				return $this->_list_options[$key];
			}
	}*/

	function setDataList($data){
		$this->_data_list = $data;
	}
	//restituisce il pager della lista a partire dal totale degli elementi che ne fanno parte
	function buildPaginator(){
		$totalItems = $this->_list_options['total_items'];
		$itemsPerPage = $this->_list_options['per_page'];
		$currentPage = $this->_page_id;
		//$urlPattern = '/foo/page/(:num)';
		$urlPattern = $this->getUrlCurrent();
		$urlPattern = preg_replace("/&pageID=([0-9]+)/",'',$urlPattern);
		
		$urlPattern .= "&pageID=(:num)";
	
		$paginator = new Paginator($totalItems, $itemsPerPage, $currentPage,$urlPattern);
		$paginator->setMaxPagesToShow($this->_list_options['max_pages_to_show']);
		return $paginator;

	}


	function buildList(){
		$this->setVar('_list_title',$this->_list_options['title']);
		$this->setVar('_order_by_list',_var('orderBy'));
		$this->setVar('_order_type_list',_var('orderType'));
		$this->setVar('_total_list',$this->_list_options['total_items']);
		$this->setVar('_limit_list',$this->_list_options['per_page']);
		
		
		
		$current_url = $this->getUrlCurrent();
		$this->setVar('_current_url_list',$current_url);
		$current_url = preg_replace('/&orderBy=(.*)&orderType=DESC/','',$current_url);
		$current_url = preg_replace('/&orderBy=(.*)&orderType=ASC/','',$current_url);
		
		$this->setVar('_current_url_ordered_list',$current_url);
		
		$search_fields = array('submitted_search','reset','bulk_success','pageID');
		foreach($this->_list_options['fields'] as $v){
			if( isset($v['searchable']) && $v['searchable'] ){
				if( isset($v['search_name']) ){
					$search_fields[] = $v['search_name'];
				}
				if( isset($v['search_name1']) ){
					$search_fields[] = $v['search_name1'];
				}
				if( isset($v['search_name3']) ){
					$search_fields[] = $v['search_name2'];
				}
				
			}
		}
		$get_parameters = $_GET;
	
		
		foreach($get_parameters as $key => $val){
			if( in_array($key,$search_fields) ){
				unset($get_parameters[$key]);
			}else{
				$get_parameters[$key] = urlencode($get_parameters[$key]);
			}
		}
		
		//debugga($get_parameters);exit;
		$this->setVar('_list_parameters',$get_parameters);
		
		$url_list = 'index.php?';
		foreach($get_parameters as $k => $v){
			$url_list .= "{$k}={$v}&";
		}
		$url_list = preg_replace('/&$/','',$url_list);
		
		$this->setVar('_list_url',$url_list);
		

		//EXPORT
		$this->setVar('_export_list',$this->_list_options['export']);

		foreach($this->_list_options['export_types'] as $t){
			$export_type = "_export_{$t}_list";
			$this->setVar($export_type,1);

		}
		

		$fields = $this->_list_options['fields'];
		$search_enabled = $this->_list_options['search'];
		if( $search_enabled ){
			$this->setVar('_search_enabled',1);
			if( _var('submitted_search') ){
				$this->setVar('_search_submitted',1);
			}
		}
		$bullk_action = $this->_list_options['bulk_actions']['enabled'];
		$row_action = $this->_list_options['row_actions']['enabled'];
		if( $bullk_action && !_var('export') ){
			$this->setVar('_bulk_actions_enabled',1);
			$this->setVar('_bulk_actions',$this->_list_options['bulk_actions']['actions']);
			
			$this->_header_list[] =
			 array(
				'type' => 'value',
				'value' => "<input type='checkbox' id='bulk_action_all' value='1'>"
			);	
		}
		
		foreach($fields as $k =>  $v){
			$this->_header_list[] = 
			 array(
				'type' => 'value',
				'value' => $v['name'],
				'sortable' => isset($v['sortable'])?$v['sortable']:0,
				'sort_id' => isset($v['sort_id'])?$v['sort_id']:'',
				'searchable' => isset($v['searchable'])?$v['searchable']:0,
				'search_value' => isset($v['search_value'])?$v['search_value']:'',
				'search_value1' => isset($v['search_value1'])?$v['search_value1']:'',
				'search_value2' => isset($v['search_value2'])?$v['search_value2']:'',
				'search_name' => isset($v['search_name'])?$v['search_name']:'',
				'search_name1' => isset($v['search_name1'])?$v['search_name1']:'',
				'search_name2' => isset($v['search_name2'])?$v['search_name2']:'',
				'search_type' => isset($v['search_type'])?$v['search_type']:'',
				'search_type_value' => isset($v['search_type_value'])?$v['search_type_value']:'',
				'search_type_value1' => isset($v['search_type_value1'])?$v['search_type_value1']:'',
				'search_type_value2' => isset($v['search_type_value2'])?$v['search_type_value2']:'',
				'search_options' => isset($v['search_options'])?$v['search_options']:array(),
			);	
			
		}
		if( $row_action ){
			$this->_header_list[] =
			 array(
				'type' => 'actions',
				'value' => "Azioni"
			);	
		}


		if( okArray($this->_data_list) ){
			foreach($this->_data_list as $row){
				$data = array();
				if( $bullk_action && !_var('export')){
					$field_id = $this->_list_options['bulk_actions']['field_id'];
					if( is_object($row) ){
						$field_id = $row->$field_id;
					}else{
						$field_id = $row[$field_id];
					}
					$data[] = array(
						'type' => 'value',
						'value' => "<input type='checkbox' class='bulk_action_items' name='bulk_action_items[]' value='{$field_id}'>"
					);
				}
				foreach($fields as $k =>  $v){
						
						$field_value = '';
						$value = '';
						if( isset($v['field_value']) && $v['field_value'] ){
							$field_value = $v['field_value'];
							if( is_object($row) ){
								$value = $row->$field_value;
							}else{
								$value = $row[$field_value];
							}
						}
						
						if( array_key_exists('function',$v) ){
							$function = $v['function'];
							if( is_callable($function) ){
								if( $v['function_type'] == 'value'){
									$value = $function($value);
								}elseif($v['function_type'] == 'row'){
									$value = $function($row);
								}
							}else{
								if( method_exists($this,$function) ){
									if( $v['function_type'] == 'value'){
										$value = $this->$function($value);
									}elseif($v['function_type'] == 'row'){
										$value = $this->$function($row);
									}
								}else{
								
									if( function_exists($function) ){
										if( $v['function_type'] == 'value'){
											$value = $function($value);
										}elseif($v['function_type'] == 'row'){
											$value = $function($row);
										}
									}
								}

							}
							
						}

						$data[] = array(
							'type' => 'value',
							'value' => $value
						);
					}
				
				if( $row_action ){
					$field_id = $this->_list_options['row_actions']['field_id'];
					if( is_object($row) ){
						$field_id = $row->$field_id;
					}else{
						$field_id = $row[$field_id];
					}
					$actions = array();
					foreach( array_values($this->_list_options['row_actions']['actions']) as $k => $v){
						if( array_key_exists('enable_value',$v) ){
							$check = 1;
							$check_value = $v['enable_value'];
							if( is_object($row) ){
								if( property_exists($row,$check_value)){
									$check = $row->$check_value;
								}
									
							}else{
								if( array_key_exists($check_value,$row) ){
									$check = $row[$check_value];
								}
								
							}
							if( !$check ){
								continue;
							}
						}
						if( array_key_exists('enable_function',$v) ){
							if( is_callable($v['enable_function'])){
								$check_function = $v['enable_function'];
								$url = $check_function($row);
							}else{
								if( method_exists($this,$v['enable_function']) ){
									$check_function = $v['enable_function'];
									$check = $this->$check_function($row);
									if( !$check ){
										continue;
									}
								}
							}
						}
						
						if( array_key_exists('url_function',$v) && $v['url_function'] ){
							if( is_callable($v['url_function'])){
								$url_function = $v['url_function'];
								$url = $url_function($row);
							}else{
								if( method_exists($this,$v['url_function']) ){
									$url_function = $v['url_function'];
									$url = $this->$url_function($row);
									
								}
							}
							
							
						}else{
							$url = preg_replace("/{{confirm_delete_url}}/",$this->getUrlConfirmDelete(),$v['url']);
							$url = preg_replace("/{{field_id}}/",$field_id,$url);
							$url = preg_replace("/{{script_url}}/",$this->getUrlScript(),$url);
						}
						$v['url'] = $url;
						$actions[] = $v;
					}
					$data[] = array(
						'type' => 'actions',
						'actions' => $actions
					);
				}

				$this->_list[] = $data;
			}
		}
		//debugga($this->_list);exit;
		$paginator = $this->buildPaginator();
		
		$this->setVar('_paginator',$paginator);
		$this->setVar('_header_list',$this->_header_list);
		$this->setVar('_list',$this->_list);
	}


	function getBulkAction(){
		return $this->bulk_action;
	}

	function getBulkIds(){
		return $this->bulk_ids;
	}

	function getBulkForm(){
		return $this->bulk_form;
	}
	

	function setListToolButtons(){
		if($this->getAction() == 'list'){
			
			$this->addToolButton(
				(new UrlButton('add'))
				->setText(_translate('list.add'))
				->setUrl($this->getUrlAdd())
				->setIconType('icon')
				->setClass('btn btn-principale')
				->setIcon('fa fa-plus')
			);
		}
		if(in_array($this->getAction(),array('edit','add','duplicate'))){
			
			$this->addToolButton(
				(new UrlButton('back'))
				->setText(_translate('list.back'))
				->setUrl($this->getUrlList())
				->setIconType('icon')
				->setClass('btn btn-secondario')
				->setIcon('fa fa-arrow-left')
			);
		}
	}

	
	function init($options=array()){

		
		parent::init($options);

		$this->setListToolButtons();

		
		
		$this->_page_id = _var('pageID');
		$this->_per_page = _var('perPage');
		$this->bulk_action = _var('bulk_action');
		
		$this->bulk_ids = (array)json_decode(_var('bulk_ids'));
		$bulk_form = _var('bulk_formdata');
		if( $bulk_form ){
			parse_str($bulk_form, $params);
			$this->bulk_form = $params['formdata'];
		}
		
		if( $this->_per_page ){
			$this->_list_options['per_page'] = $this->_per_page;
			$_SESSION['admin_pager_list'] = $this->_per_page;
		}else{
			if( !$this->_per_page && isset($_SESSION['admin_pager_list']) && $_SESSION['admin_pager_list']){
				$this->_per_page = $_SESSION['admin_pager_list'];
				$this->_list_options['per_page'] = $this->_per_page;
			}
		}
		

		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			$this->_id =  $formdata['id'];
		}else{
			$this->_id = _var('id');
		}
		
		

		
		
	}
	/******************************************************** METODI SET ********************************************************************/
	
	function setID($id){
		$this->_id = $id;
	}
	
	function setLimitList($val){
		$this->_limit_list = $val;
	}


	/******************************************************** METODI GET ********************************************************************/

	//restituisce l'id dell'oggetto gestito dal controller
	function getID(){
		return $this->_id;
	}
	
	

	// restituisce il limite di oggetti da caricare nella lista utile per effettuare le query 
	function getLimitList(){
		if( $this->_per_page ){
			return $this->_per_page;
		}else{
			return $this->_limit_list;
		}
		
	}
	
	// restituisce l'offset della lista utile per effettuare le query 
	function getOffsetList(){

		if( $this->_page_id ){
			return ($this->_page_id-1)*$this->_limit_list;
		}
		return 0;
	}

	
	
	//restituisce l'url per duplicare un oggetto
	function getUrlDuplicate(){
		if( $this->getID()){
			$url = $this->getUrlScript()."&action=duplicate&id=".$this->getID();
		}else{
			$url = $this->getUrlScript()."&action=duplicate";
		}
		if( $this->_page_id ){
			$url .= "&pageID=".$this->_page_id;
		}
		return $url;
	}
	

	//restituisce l'url per modificare un oggetto
	function getUrlEdit(){
		if( $this->getID()){
			$url = $this->getUrlScript()."&action=edit&id=".$this->getID();
		}else{
			$url =  $this->getUrlScript()."&action=edit";
		}
		if( $this->_page_id ){
			$url .= "&pageID=".$this->_page_id;
		}
		
		return $url;
	}
	
	//restituisce l'url per aggiungere un oggetto
	function getUrlAdd(){
		$url = $this->getUrlScript()."&action=add";
		
		if( $this->_page_id ){
			$url .= "&pageID=".$this->_page_id;
		}
		return $url;

	}
	//restituisce l'url di conferma per eliminare un oggetto
	function getUrlConfirmDelete(){
		if( $this->getID()){
			$url = $this->getUrlScript()."&action=confirm_delete&url_back=".urlencode($this->getUrlCurrent())."&id=".$this->getID();
		}else{
			$url = $this->getUrlScript()."&action=confirm_delete&url_back=".urlencode($this->getUrlCurrent());
		}
		$url .= "&last_action=".$this->getAction();
		if( $this->_page_id ){
			$url .= "&pageID=".$this->_page_id;
		}

		return $url;
	}
	//restituisce l'url per eliminare un oggetto
	function getUrlDelete(){
		if( $this->getID()){
			$url = $this->getUrlScript()."&action=delete&id=".$this->getID();
		}else{
			$url = $this->getUrlScript()."&action=delete";
		}
		if( $this->_page_id ){
			$url .= "&pageID=".$this->_page_id;
		}
		return $url;
		
	}
	//restituisce l'url della lista oggetti
	function getUrlList(){
		$url = $this->getUrlScript()."&action=list";
		if( $this->_page_id ){
			$url .= "&pageID=".$this->_page_id;
		}
		return $url;
	}


	//restituisce l'url della pagina precedente
	function getUrlBack(){
		return urldecode(_var('url_back'));

	}


	// restituisce una variabile di template
	function getVar($var){
		return $this->_tmpl_obj->$var;
	}
	


	function getBaseUrlBackend(){
		return _MARION_BASE_URL_.'backend/';
	}
	function getBaseUrl(){
		return _MARION_BASE_URL_;
	}
	
	

	/******************************************************* FINE FUNZIONI DI TEMPLATE **************************************************************/

	function display(){
		if( _var('serialized_errors') ){
			$this->errors = unserialize(_var('serialized_errors'));
		}
		switch($this->_action){
			case 'list':
				$this->displayList();
				break;
			case 'duplicate':
			case 'add':
			case 'edit':
				$this->displayForm();
				break;
			case 'confirm_delete':
				$this->confirmDelete();
				exit;
			case 'delete':
				$this->delete();
				break;
			case 'bulk':
				$this->bulk();
				break;
			case 'bulk_form':
				$this->bulkForm();
				break;
			default:
				$this->displayContent();
				break;
		}
	}

	public function displayContent(){

	}

	public function displayForm(){

	}

	/*function notLogged(){
		$this->output('login.htm');
		exit;
	}

	function notAuth(){
		$this->output('not_auth.htm');
		exit;
	}*/

	function setTemplateVariables(){
		/*$this->setVar('locales',Marion::getConfig('locale','supportati'));
		$this->setVar('javascript_head',$this->_javascript['head']);
		$this->setVar('javascript_end',$this->_javascript['end']);
		$this->setVar('css',$this->_css);*/
		parent::setTemplateVariables();
		$this->setVar('url_script',$this->getUrlScript());
		$this->setVar('url_list',$this->getUrlList());
		$this->setVar('url_duplicate',$this->getUrlDuplicate());
		$this->setVar('url_edit',$this->getUrlEdit());
		$this->setVar('url_add',$this->getUrlAdd());
		$this->setVar('url_confirm_delete',$this->getUrlConfirmDelete());
		$this->setVar('url_delete',$this->getUrlDelete());
		$this->setVar('url_back',$this->getUrlBack());

		$this->setVar('pageID',$this->_page_id);
		
		
		
		
	}

	

	//redirige alla pagina della lista prodotti
	function redirectToList($parameters=array()){
		$url = '';
		if( _var('url_list') ) {
			$url = _var('url_list');
		}
		if( preg_match('/bulk_success/',$url)){
			$url = preg_replace('/&bulk_success=([a-zA-Z_0-9]+)/','',$url);
		}
		
		if( $this->_page_id ){
			$url .= "&pageID={$this->_page_id}";
		}
		
		if( okArray($parameters) ){
			foreach($parameters as $k => $v){
				$url .= "&{$k}={$v}";
			}
		}
		if( _var('url_list') ) {
			header('Location: '.$url);
			exit;
		}
		
		if( $url ){
			header('Location: '.$this->getUrlList().$url);
		}else{
			header('Location: '.$this->getUrlList());
		}
		exit;
	}
	

	function bulk(){
		$params = array();
		if( okarray($this->errors )){
			$params = array('serialized_errors'=> serialize($this->errors));
		}else{
			$params = array(
				'bulk_success'=> $this->getBulkAction()
			);	
		}
		$this->redirectToList($params);
	}

	function bulkForm(){
		$function = _var('bulk_function');
		
		$html = '';
		if( method_exists($this,$function) ){
			
			ob_start();
			$this->$function();
			$html =  ob_get_contents();
			ob_end_clean();

		}
		
		$response = array(
			'result' => 'ok',
			'html' => $html
		);
		echo json_encode($response);
	}

	function displayList(){
			
		//codice che mostra la lista

		$this->buildList();
		if( _var('export') ){
			$filename=$this->_title;
			switch(_var('type_export')){
				case 'excel':
					/*header("Cache-Control: ");
					header("Pragma: ");
					header("Accept-Ranges: bytes");
					header("Content-type: application/vnd.ms-excel");
					header("Content-Language: eng-US");
					header('Content-Disposition: attachment; filename="'.$filename.'.xls"');
					header("Content-Transfer-Encoding: binary");*/
					header("Cache-Control: ");
					header("Pragma: ");
					header('Content-Encoding: UTF-8');
					header("Accept-Ranges: bytes");
					header("Content-type: application/vnd.ms-excel; charset=UTF-8");
					//header("Content-Language: eng-US");
					header('Content-Disposition: attachment; filename="'.$filename.'.xls"');
					echo "\xEF\xBB\xBF"; // UTF-8 BOM
				
					//header("Content-Encoding: gzip");
				
					ob_start();
					$this->output('common/list_excel.htm');
					$size=ob_get_length();
					$now = time();
					$diff = date('Z', $now);
					$gmt_mtime = date('D, d M Y H:i:s', $now-$diff).' GMT';
				
					header("Last-Modified: ".$gmt_mtime);
					header("Expires: ".$gmt_mtime);
					header("X-Server: angela");
					header("Content-Length: $size");
					sleep(1);
					ob_end_flush();

					break;
				case 'csv':
					$delimiter = ';';
					$f = fopen('php://memory', 'w'); 
					
					$header = array();
					foreach($this->_header_list as $v){
						if( $v['type'] == 'value' ){
							$header[] = $v['value'];
						}


					}
					fputcsv($f, $header, $delimiter); 
					foreach($this->_list as $row){
						$data = array();
						foreach($row as $v){
							if( $v['type'] == 'value' ){
								$data[] = $v['value'];
							}
						}
						fputcsv($f, $data, $delimiter); 
					}
					
					fseek($f, 0);
					// tell the browser it's going to be a csv file
					header('Content-Type: application/csv');
					// tell the browser we want to save it instead of displaying it
					header('Content-Disposition: attachment; filename="'.$filename.'.csv";');
					// make php send the generated csv lines to the browser
					fpassthru($f);

					break;
				case 'pdf':
					
					$this->setVar('root_dir',_MARION_ROOT_DIR_);
					ob_start();
					$this->output('layouts/list/list_pdf.htm');
					$html = ob_get_contents();
					ob_end_clean();
					ob_flush();

					

					$pdf = Pdf::html($html);
					$pdf->send($filename.".pdf");
					
					
					break;
			}
			
		    
			
		}else{
			$this->output($this->_list_options['html_template']);
		}
	}


	function setListOptions(){

	}


	function delete(){
		//codice che cancella l'oggetto in esame

		if( okArray($this->errors) ){
			$this->deleteErrorMessages();
		}else{

			$this->redirectToList(array('deleted' => 1));
		}
	}

	function deleteErrorMessages(){
		$last_action = _var('last_action');

		if( $last_action == 'edit' ){
			$url = $this->getUrlEdit();
			$url .= "&serialized_errors=".serialize($this->errors);
			header('Location: '.$url);
		}else{
			$this->redirectToList(array('serialized_errors'=> serialize($this->errors)));
		}
	}

	function confirmDelete(){
		$this->setVar('confirm_delete_title',$this->getConfirmDeleteTitle());
		$this->setVar('confirm_delete_message',$this->getConfirmDeleteMessage());
		$this->setVar('last_action',_var('last_action'));
		$id = $this->getID();
		
		$this->output('layouts/confirm_delete.htm');
	}

	function getConfirmDeleteTitle(){
		return _translate('general.confirm_delete_title');
	}

	function getConfirmDeleteMessage(){
		return _translate('general.confirm_delete_message');
	}


	function getListContainer():ListContainer{
		if( !is_object($this->_list_container) ){
			$this->_list_container =  new ListContainer($this);
		}
		return $this->_list_container;
	}


}



?>