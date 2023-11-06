<?php
namespace Marion\Controllers\Elements;
use Marion\Controllers\AdminController;
class ListContainer{
	private $ctrl;
	private $actionRowButtons = [];
	private $actionBulkButtons = [];
	private $exportTypes = [];
	private $fields = [];
	private $_array_fields = [];
	private $actionBulkEnabled = true;
	private $actionRowEnabled = true;
	private $exportEnabled = true;
	private $searchEnabled = true;
	private $sortEnabled = true;
    private $totalItems;
    private $perPage = 25;

	public function setDataList(array $list = null){
		$this->ctrl->setDataList($list);
		return $this;
	}


	public function __construct(AdminController $ctrl)
	{
		$this->ctrl = $ctrl;
		
	}

	function setTotalItems(int $totalItems){
		$this->totalItems = $totalItems;
		return $this;
	}

    function setPerPage(int $num){
		$this->perPage = $num;
		return $this;
    }
    
    function getPerPage(){
		return $this->perPage;
		
	}

	function setExportTypes(Array $types){
		$this->exportTypes = $types;
		return $this;
	}

	function enableSearch($bool){
		$this->searchEnabled = $bool;
		return $this;
	}

	function enableSort($bool){
		$this->sortEnabled = $bool;
		return $this;
	}
	function enableExport($bool){
		$this->exportEnabled = $bool;
		return $this;
	}

	function enableRowActions($bool){
		$this->actionRowEnabled = $bool;
		return $this;
	}
	function enableBulkActions($bool){
		$this->actionBulkEnabled = $bool;
		return $this;
	}

	function addActionRowButton(ListActionRowButton $button){
		$this->actionRowButtons[] = $button;
		return $this;
	}
	function addActionBulkButton(ListActionBulkButton $button){
		$this->actionBulkButtons[] = $button;
		return $this;
	}
	function addActionRowButtons(Array $buttons){
		foreach($buttons as $btn){
			$this->addActionRowButton($btn);
		}
		
		return $this;
	}
	function addActionBulkButtons(Array $buttons){
		foreach($buttons as $btn){
			$this->addActionBulkButton($btn);
		}
		
		return $this;
	}

	function addDeleteActionRowButton(){

		$btn = new ListActionRowButton('delete');
		$btn->setText(_translate('list.delete'))
			->setIcon('fa fa-trash-o')
			->setUrl("{{confirm_delete_url}}&id={{field_id}}");
		return $this->addActionRowButton(
			$btn
		);
		
	}

	function addEditActionRowButton(){

		$btn = new ListActionRowButton('edit');
		$btn->setText(_translate('list.edit'))
			->setIcon('fa fa-edit')
			->setUrl("{{script_url}}&action=edit&id={{field_id}}");

		
		return $this->addActionRowButton(
			$btn
		);
	}

	function addCopyActionRowButton(){

		$btn = new ListActionRowButton('duplicate');
		$btn->setText(_translate('list.duplicate'))
			->setIcon('fa fa-copy')
			->setUrl("{{script_url}}&action=duplicate&id={{field_id}}");
		return $this->addActionRowButton(
			$btn
		);
	}


	function getActionRowButton(string $action): ?ListActionRowButton{
		foreach($this->actionRowButtons as $b){
			if( $b->getAction() == $action ){
				$btn = $b;
				break;
			}
		}
		return isset($btn)?$btn:null;
	}

	function getActionBulkButton(string $action): ?ListActionBulkButton{
		foreach($this->actionBulkButtons as $b){
			if( $b->getAction() == $action ){
				$btn = $b;
				break;
			}
		}
		return isset($btn)?$btn:null;
	}

	function setFieldsFromArray(array $fields){
		$this->_array_fields = $fields;
		return $this;
	}

	function setFields(array $fields){
		$this->fields = $fields;
		return $this;
	}

	function getFields(){
		return $this->fields;
	}


	function build(){
		$row_actions = [];
		$bulk_actions = [];
		if( $this->actionRowEnabled ){
			foreach($this->actionRowButtons as $btn){
				$row_actions[$btn->getAction()] = $btn->getData();
			}
		}
		if( $this->actionBulkEnabled ){
			foreach($this->actionBulkButtons as $btn){
				$bulk_actions[$btn->getAction()] = $btn->getData();
			}
		}
		
		if(okArray($this->exportTypes)){
			$export_types = [];
			foreach($this->exportTypes as $type){
				if( in_array($type,array('excel','csv','pdf'))){
					$export_types[] = $type;
				}
			}
			$this->ctrl->_list_options['export_types'] =$export_types;
		}
		$this->ctrl->_list_options['total_items'] = $this->totalItems;
        $this->ctrl->_list_options['per_page'] = $this->perPage;
        $this->ctrl->_limit_list = $this->perPage;
		$this->ctrl->_list_options['export'] = $this->exportEnabled;
		$this->ctrl->_list_options['search'] = $this->searchEnabled;
		$this->ctrl->_list_options['fields'] = $this->_array_fields;
		$this->ctrl->_list_options['row_actions']['enabled'] = $this->actionRowEnabled;
		$this->ctrl->_list_options['bulk_actions']['enabled'] = $this->actionBulkEnabled;
		$this->ctrl->_list_options['row_actions']['actions'] = $row_actions;
        $this->ctrl->_list_options['bulk_actions']['actions'] = $bulk_actions;
       
		
	}
}
?>