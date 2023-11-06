<?php
namespace Marion\Controllers\Elements;
class ListActionBulkButton extends UrlButton{

	private $confirm = true;
	private $confirmMessage = 'Sicuro di voler procedere con questa operazione?';
	private $ajaxContent = '';
	private $customFields = [];


	

	/**
	 * Get the value of confirm
	 */ 
	public function getConfirm()
	{
		return $this->confirm;
	}

	/**
	 * Set the value of confirm
	 *
	 * @return  self
	 */ 
	public function setConfirm($confirm):ListActionBulkButton
	{
		$this->confirm = $confirm;

		return $this;
	}

	/**
	 * Get the value of confirmMessage
	 */ 
	public function getConfirmMessage()
	{
		return $this->confirmMessage;
	}

	/**
	 * Set the value of confirmMessage
	 *
	 * @return  self
	 */ 
	public function setConfirmMessage($confirmMessage):self
	{
		$this->confirmMessage = $confirmMessage;

		return $this;
	}

	/**
	 * Get the value of ajaxContent
	 */ 
	public function getAjaxContent()
	{
		return $this->ajaxContent;
	}

	/**
	 * Set the value of ajaxContent
	 *
	 * @return  self
	 */ 
	public function setAjaxContent($ajaxContent):self
	{
		$this->ajaxContent = $ajaxContent;

		return $this;
	}

	/**
	 * Get the value of custom_Fields
	 */ 
	public function getCustomFields()
	{
		return $this->customFields;
	}

	/**
	 * Set the value of custom_Fields
	 *
	 * @return  self
	 */ 
	public function setCustomFields($customFields):self
	{
		$this->customFields = $customFields;

		return $this;
	}

	public static function create(string $action){
		return new ListActionBulkButton($action);
	}
	
	function getData(){
		parent::getData();

		$this->data_button['confirm'] = $this->confirm;
		$this->data_button['confirm_message'] = $this->confirmMessage;
		$this->data_button['ajax_content'] = $this->ajaxContent;
		$this->data_button['custom_fields'] = $this->customFields;

		return $this->data_button;
		
	}
}

?>