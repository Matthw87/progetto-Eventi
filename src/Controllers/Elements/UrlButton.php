<?php
namespace Marion\Controllers\Elements;
class UrlButton{
	private $action;
	private $text;
	private $iconType = 'icon';
	private $icon;
	private $img;
	private $url;
	private $class = 'btn btn-default';
    private $targetBlank = false;

    public $data_button = [];
    

    public function __construct($action)
	{
		$this->action = $action;
		
	}

	/**
	 * Get the value of action
	 */ 
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Set the value of action
	 *
	 * @return  self
	 */ 
	public function setAction($action):self
	{
		$this->action = $action;

		return $this;
	}
	

	/**
	 * Get the value of text
	 */ 
	public function getText()
	{
		return $this->text;
	}

	/**
	 * Set the value of text
	 *
	 * @return  self
	 */ 
	public function setText($text):self
	{
		$this->text = $text;

		return $this;
	}

	/**
	 * Get the value of icon_type
	 */ 
	public function getIconType()
	{
		return $this->iconType;
	}

	/**
	 * Set the value of icon_type
	 *
	 * @return  self
	 */ 
	public function setIconType($iconType):self
	{
		$this->iconType = $iconType;

		return $this;
	}

	/**
	 * Get the value of icon
	 */ 
	public function getIcon()
	{
		return $this->icon;
	}

	/**
	 * Set the value of icon
	 *
	 * @return  self
	 */ 
	public function setIcon($icon):self
	{
		$this->icon = $icon;

		return $this;
	}

	/**
	 * Get the value of img
	 */ 
	public function getImg()
	{
		return $this->img;
	}

	/**
	 * Set the value of img
	 *
	 * @return  self
	 */ 
	public function setImg($img):self
	{
		$this->img = $img;

		return $this;
	}

	/**
	 * Get the value of url
	 */ 
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Set the value of url
	 *
	 * @return  self
	 */ 
	public function setUrl($url):self
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * Get the value of class
	 */ 
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * Set the value of class
	 *
	 * @return  self
	 */ 
	public function setClass($class):self
	{
		$this->class = $class;

		return $this;
	}

	/**
	 * Get the value of target_blank
	 */ 
	public function getTargetBlank()
	{
		return $this->targetBlank;
	}

	/**
	 * Set the value of target_blank
	 *
	 * @return  self
	 */ 
	public function setTargetBlank($targetBlank):self
	{
		$this->targetBlank = $targetBlank;

		return $this;
	}


	function getData(){
		$this->data_button = array(
			'text' => $this->text,
			'target_blank' => $this->targetBlank,
			'icon_type' => $this->iconType,
			'icon' => $this->icon,
			'img' => $this->img,
			'url' => $this->url,
        );

        return $this->data_button;
    }
    
    public static function create(string $action){
		return new UrlButton($action);
	}
}

?>