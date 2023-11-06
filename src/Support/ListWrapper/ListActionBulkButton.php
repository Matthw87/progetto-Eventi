<?php
namespace Marion\Support\ListWrapper;
use Marion\Controllers\Elements\UrlButton;
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
	 * @return  static
	 */ 
	public function setConfirm($confirm):static
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
	 * @return static
	 */ 
	public function setConfirmMessage($confirmMessage):static
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
	 * @return static
	 */ 
	public function setAjaxContent($ajaxContent):static
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
	 * @return static
	 */ 
	public function setCustomFields($customFields):static
	{
		$this->customFields = $customFields;

		return $this;
	}

	/**
	 * create button
	 *
	 * @param string $action
	 * @return static
	 */
	public static function create(string $action): static{
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