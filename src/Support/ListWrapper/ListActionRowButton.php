<?php
namespace Marion\Support\ListWrapper;
use Marion\Controllers\Elements\UrlButton;
class ListActionRowButton extends UrlButton{


	private $enableValue = null;
	private $enableFunction = null;
	private $urlFunction = null;


	private $confirm = false;
	private $confirmMessage = 'Sicuro di voler procedere con questa operazione?';


	

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
	 * @return static
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
	 * Get the value of enableValue
	 */ 
	public function getEnableValue()
	{
		return $this->enableValue;
	}

	/**
	 * Set the value of enableValue
	 *
	 * @return static
	 */ 
	public function setEnableValue($enableValue): static
	{
		$this->enableValue = $enableValue;

		return $this;
	}

	/**
	 * Get the value of enableFunction
	 */ 
	public function getEnableFunction()
	{
		return $this->enableFunction;
	}

	/**
	 * Set the value of enableFunction
	 *
	 * @return static
	 */ 
	public function setEnableFunction($enableFunction):static
	{
		$this->enableFunction = $enableFunction;

		return $this;
	}

	/**
	 * Get the value of urlFunction
	 */ 
	public function getUrlFunction()
	{
		return $this->urlFunction;
	}

	/**
	 * Set the value of urlFunction
	 *
	 * @return static
	 */ 
	public function setUrlFunction($urlFunction):static
	{
		$this->urlFunction = $urlFunction;

		return $this;
	}

	

	/**
	 * create button
	 *
	 * @param string $action
	 * @return static
	 */
	public static function create(string $action): static{
		return new ListActionRowButton($action);
	}

	function getData(){
		parent::getData();
		$this->data_button['enable_value'] = $this->enableValue;
		$this->data_button['confirm'] = $this->confirm;
		$this->data_button['confirm_message'] = $this->confirmMessage;
		$this->data_button['enable_function'] = $this->enableFunction;
		$this->data_button['url_function'] = $this->urlFunction;
		$this->data_button['class'] = $this->getClass();
		return $this->data_button;
		
	}
}
?>