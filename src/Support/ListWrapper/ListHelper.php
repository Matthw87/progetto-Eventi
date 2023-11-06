<?php
namespace Marion\Support\ListWrapper;
use Marion\Support\Pdf;
use Closure;

class ListHelper extends ListCore{
	private $actionBulkButtons = [];
	private $exportTypes = [];
	private $actionBulkEnabled = true;
	private $exportEnabled = true;
	private $searchEnabled = true;
	private $sortEnabled = true;

	protected string $html_template = '@core/layouts/list/list.htm';


	/**
	 * variabile utilizzate per la stampa csv
	 *
	 * @var array
	 */
	private $_header_list = [];

	/**
	 * variabile utilizzate per la stampa csv
	 *
	 * @var array
	 */
	private $_data_list = [];


	public Closure $delete_function;
	public Closure $action_bulk_function;
	


   
	/**
	 * Set export types
	 *
	 * @param Array $types
	 * @return static
	 */
	function setExportTypes(Array $types): static{
		$this->exportTypes = $types;
		return $this;
	}
	
	/**
	 * Enable disable search
	 *
	 * @param boolean $bool
	 * @return static
	 */
	function enableSearch(bool $bool): static{
		$this->searchEnabled = $bool;
		return $this;
	}

	/**
	 * enable/disable sort
	 *
	 * @param boolean $bool
	 * @return static
	 */
	function enableSort(bool $bool): static{
		$this->sortEnabled = $bool;
		return $this;
	}

	/**
	 * enable/disable export
	 *
	 * @param boolean $bool
	 * @return static
	 */
	function enableExport(bool $bool): static{
		$this->exportEnabled = $bool;
		return $this;
	}


	/**
	 * enabled/disable bulk actions
	 *
	 * @param boolean $bool
	 * @return static
	 */
	function enableBulkActions(bool $bool): static{
		$this->actionBulkEnabled = $bool;
		return $this;
	}


	/**
	 * Add bulk action button
	 *
	 * @param ListActionBulkButton $button
	 * @return static
	 */
	function addActionBulkButton(ListActionBulkButton $button): static{
		$this->actionBulkButtons[] = $button;
		return $this;
	}
	
	/**
	 * Add multiple bulk action buttons
	 *
	 * @param array $buttons
	 * @return static
	 */
	function addActionBulkButtons(array $buttons): static{
		foreach($buttons as $btn){
			$this->addActionBulkButton($btn);
		}
		
		return $this;
	}


	/**
	 * Add delete button
	 *
	 * @param Closure|null $function
	 * @param Closure|null $enableFunction
	 * @return static
	 */
	function addDeleteActionRowButton(Closure $function=null,?Closure $enableFunction=null): static{

		$btn = new ListActionRowButton('delete');
		$btn->setConfirm(true)
			->setConfirmMessage($function?$function:_translate('list.confirm_delete_message'))
			->setText(_translate('list.delete'))
			->setIcon('fa fa-trash-o');
		if( $enableFunction ){
			$btn->setEnableFunction($enableFunction);
		}
		return $this->addActionRowButton(
			$btn
		);
		
	}

	/**
	 * Add edit button
	 *
	 * @param Closure|null $enableFunction
	 * @return static
	 */
	function addEditActionRowButton(?Closure $enableFunction=null): static{

		$btn = new ListActionRowButton('edit');
		$btn->setText(_translate('list.edit'))
			->setIcon('fa fa-edit')
			->setUrl("{{script_url}}&action=edit&id={{field_id}}");

		if( $enableFunction ){
			$btn->setEnableFunction($enableFunction);
		}

		
		return $this->addActionRowButton(
			$btn
		);
	}

	/**
	 * Add copy button
	 *
	 * @param Closure|null $enableFunction
	 * @return static
	 */
	function addCopyActionRowButton(?Closure $enableFunction=null): static{
		$btn = new ListActionRowButton('duplicate');
		$btn->setText(_translate('list.duplicate'))
			->setIcon('fa fa-copy')
			->setUrl("{{script_url}}&action=duplicate&id={{field_id}}");
		if( $enableFunction ){
			$btn->setEnableFunction($enableFunction);
		}
		return $this->addActionRowButton(
			$btn
		);
	}


	/**
	 * Get bulk action button from tag
	 *
	 * @param string $action
	 * @return ListActionBulkButton|null
	 */
	function getActionBulkButton(string $action): ?ListActionBulkButton{
		foreach($this->actionBulkButtons as $b){
			if( $b->getAction() == $action ){
				$btn = $b;
				break;
			}
		}
		return isset($btn)?$btn:null;
	}

	
	
	
	function runActions(): void{
		
		$action = _var('action_list');
		$id = _var('id_row_list');
		
		if( $action == 'delete' ){
			if( isset($this->delete_function) ){
				call_user_func($this->delete_function,$id);
			}
		}else{
			parent::runActions();
		}
		
	}
	

	
	/**
	 * Build bulk action buttons
	 *
	 * @return void
	 */
	private function runBulkActions(): void{
		$action = _var('bulk_action_list');
		if( $action ){
			$ids = (array)json_decode(_var('bulk_ids'));
			$bulk_form = _var('bulk_formdata');
			if( $bulk_form ){
				parse_str($bulk_form, $params);
				$bulk_form = $params['formdata'];
			}
			if( isset($this->action_bulk_function) ){
				call_user_func($this->action_bulk_function,$action,$ids,$bulk_form);
			}
		} 
			
	}

	

	/**
	 * override build method
	 *
	 * @return void
	 */
	function build(): void{
		parent::build();
		$this->runBulkActions();
		
		$template_data = [];

		$template_data['export'] = $this->exportEnabled;
		$template_data['search'] = $this->searchEnabled;
		$template_data['enable_bulk_actions'] = $this->actionBulkEnabled;
		

		$bulk_actions = [];
		if( $this->actionBulkEnabled ){
			foreach($this->actionBulkButtons as $btn){
				$bulk_actions[$btn->getAction()] = $btn->getData();
			}
		}

		$template_data['bulk_actions'] = $bulk_actions;
		
		if(okArray($this->exportTypes)){
			$export_types = [];
			foreach($this->exportTypes as $type){
				if( in_array($type,array('excel','csv','pdf'))){
					$export_types[] = $type;
				}
			}
			$template_data['export_types'] =$export_types;
		}
		$this->template_data = array_merge($this->template_data,$template_data);
	}


   


    function display(): void{
		$this->build();

		$this->buildList();
		if( $this->ctrl->_module ){
			$this->ctrl->setVar('module',$this->ctrl->_module );
		}
		if( _var('confirm_message') ){
			$this->ctrl->setVar('confirm_message_list',_var('confirm_message'));
			$this->ctrl->setVar('url_confirm_action_list',_var('confirm_url'));
		}
        if( _var('export') ){
			$filename = $this->ctrl->_title;
			switch(_var('type_export')){
				case 'excel':
					header("Cache-Control: ");
					header("Pragma: ");
					header('Content-Encoding: UTF-8');
					header("Accept-Ranges: bytes");
					header("Content-type: application/vnd.ms-excel; charset=UTF-8");
					header('Content-Disposition: attachment; filename="'.$filename.'.xls"');
					echo "\xEF\xBB\xBF"; // UTF-8 BOM
				
					ob_start();
					$this->ctrl->output('@core/layouts/list/list_excel.htm');
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
					if( okArray($this->_header_list)){
						foreach($this->_header_list as $v){
							if( $v['type'] == 'value' ){
								$header[] = $v['value'];
							}
						}
					}
					
					fputcsv($f, $header, $delimiter); 
					if( okArray($this->_data_list)){
						foreach($this->_data_list as $row){
							$data = array();
							foreach($row as $v){
								if( $v['type'] == 'value' ){
									$data[] = $v['value'];
								}
							}
							fputcsv($f, $data, $delimiter); 
						}
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
					
					$this->ctrl->setVar('root_dir',_MARION_ROOT_DIR_);
					ob_start();
					$this->ctrl->output('@core/layouts/list/list_pdf.htm');
					$html = ob_get_contents();
					ob_end_clean();
					ob_flush();

					

					$pdf = Pdf::html($html);
					$pdf->send($filename.".pdf");
					
					
					break;
			}
			
		    
			
		}else{
			parent::display();
		}
    }

    function buildList(){
		$header_list = [];
		$list = [];
		$this->ctrl->setVar('_order_by_list',_var('orderBy'));
		$this->ctrl->setVar('_order_type_list',_var('orderType'));
		$this->ctrl->setVar('_total_list',$this->template_data['total_items']);
		//$this->ctrl->setVar('_limit_list',$this->template_data['per_page']);
		
		
		
		$current_url = $this->ctrl->getUrlCurrent();
		$this->ctrl->setVar('_current_url_list',$current_url);
		$current_url = preg_replace('/&orderBy=(.*)&orderType=DESC/','',$current_url);
		$current_url = preg_replace('/&orderBy=(.*)&orderType=ASC/','',$current_url);
		
		$this->ctrl->setVar('_current_url_ordered_list',$current_url);
		
		$search_fields = array('submitted_search','reset','bulk_success','pageID','confirm_message','confirm_url');
		
		foreach($this->template_data['fields'] as $v){
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
		$this->ctrl->setVar('_list_parameters',$get_parameters);
		
		$url_list = 'index.php?';
		foreach($get_parameters as $k => $v){
			$url_list .= "{$k}={$v}&";
		}
		$url_list = preg_replace('/&$/','',$url_list);
		
		$this->ctrl->setVar('_list_url',$url_list);
		

		//EXPORT
		$this->ctrl->setVar('_export_list',$this->template_data['export']);

		foreach($this->template_data['export_types'] as $t){
			$export_type = "_export_{$t}_list";
			$this->ctrl->setVar($export_type,1);

		}
		

		$fields = $this->template_data['fields'];
		$search_enabled = $this->template_data['search'];
		if( $search_enabled ){
			$this->ctrl->setVar('_search_enabled',1);
			if( _var('submitted_search') ){
				$this->ctrl->setVar('_search_submitted',1);
			}
		}
		$bullk_action = $this->actionBulkEnabled;
		$row_action = $this->actionRowEnabled;
		if( $bullk_action && !_var('export') ){
			$this->ctrl->setVar('_bulk_actions_enabled',1);
			$this->ctrl->setVar('_bulk_actions',$this->template_data['bulk_actions']);
			
			$header_list[] =
			 array(
				'type' => 'value',
				'value' => "<input type='checkbox' id='bulk_action_all' value='1'>"
			);	
		}
		
		foreach($fields as $k =>  $v){
			$header_list[] = 
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
			$header_list[] =
			 array(
				'type' => 'actions',
				'value' => "Azioni"
			);	
		}
		

		
		$data_list = $this->data_list;
		if( okArray($data_list) ){
			foreach($data_list as $row){
				$data = array();
				if( $bullk_action && !_var('export')){
					$field_id = $this->row_id;
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
				//debugga($fields);exit;
				foreach($fields as $k =>  $v){
						
						$field_value = '';
						$value = '';
						if( isset($v['field_value']) && $v['field_value'] ){
							$field_value = $v['field_value'];
							if( is_object($row) ){
								$value = isset($row->$field_value)?$row->$field_value:'';
							}else{
								$value = isset($row[$field_value])?$row[$field_value]:'';
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
					$field_id = $this->row_id;
					if( is_object($row) ){
						$field_id = $row->$field_id;
					}else{
						$field_id = $row[$field_id];
					}
					$actions = array();
					foreach( $this->template_data['row_actions'] as $action_key => $v){
						if( array_key_exists('enable_value',$v) ){
							$check = 1;
							$check_value = $v['enable_value'];
							if( is_object($row) ){
								if( $check_value && property_exists($row,$check_value)){
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
								$check = $check_function($row);
								if( !$check ){
									continue;
								}
							}else{
								if( $v['enable_function'] && method_exists($this,$v['enable_function']) ){
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
							if( $v['url'] ){
								$url = preg_replace("/{{confirm_delete_url}}/",$this->getUrlConfirmDelete(),$v['url']);
								$url = preg_replace("/{{field_id}}/",$field_id,$url);
								$url = preg_replace("/{{script_url}}/",$this->ctrl->getUrlScript(),$url);
							}else{
								$row_id = $this->row_id;
                            
								if(is_object($row) ){
									$id_row = $row->$row_id;
								}else{
									$id_row = $row[$row_id];
								}
								$url_action = $current_url."&action_list=".$action_key."&id_row_list=".$id_row;
								
								if($v['confirm']){
									if( $v['confirm_message'] instanceof Closure ){	
										$confirm_message = call_user_func($v['confirm_message'],$row);
									}else{
										$confirm_message = $v['confirm_message'];
									}
									$url = $current_url."&confirm_message=".urlencode($confirm_message)."&confirm_url=".urlencode($url_action);
								}else{
									$url = $url_action;
								}
			
							}
							
						}
						$v['url'] = $url;
						$actions[] = $v;
					}
					$data[] = array(
						'type' => 'actions',
						'actions' => $actions
					);
				}

				$list[] = $data;
			}
			
		}

		$this->_header_list = $header_list;
		$this->_data_list = $list;
		$this->ctrl->setVar('_header_list',$header_list);
		$this->ctrl->setVar('_list',$list);

    }


	 /**
	  * Get url confirm delete 
	  *
	  * @return string
	  */
    function getUrlConfirmDelete(): string {
		$url_back = $this->ctrl->getUrlCurrent()."&action_list=delete";
		$url = $this->ctrl->getUrlScript()."&action=confirm_delete&url_back=".urlencode($url_back);
		$url .= "&last_action=".$this->ctrl->getAction();
		return $url;
	}


	/**
     * Delete action row callback
     *
     * @param Closure $function
     * @return static
     */
    function onDelete(Closure $function): static{
        $this->delete_function = $function;
        return $this;
    }


	/**
     * Action bulk callback
     *
     * @param Closure $function
     * @return static
     */
    function onBulkAction(Closure $function): static{
        $this->action_bulk_function = $function;
        return $this;
    }



	public static function extend(string $id, $obj){

	}

}