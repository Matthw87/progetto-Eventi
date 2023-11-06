<?php
namespace Marion\Controllers\Elements;
class ListActionRowButton extends UrlButton{


	private $enableValue = null;
	private $enableFunction = null;
	private $urlFunction = null;



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
	 * @return  self
	 */ 
	public function setEnableValue($enableValue)
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
	 * @return  self
	 */ 
	public function setEnableFunction($enableFunction):self
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
	 * @return  self
	 */ 
	public function setUrlFunction($urlFunction):self
	{
		$this->urlFunction = $urlFunction;

		return $this;
	}

	


	public static function create(string $action){
		return new ListActionRowButton($action);
	}

	function getData(){
		parent::getData();
		$this->data_button['enable_value'] = $this->enableValue;
		$this->data_button['enable_function'] = $this->enableFunction;
		$this->data_button['url_function'] = $this->urlFunction;

		return $this->data_button;
		
	}
}
?>