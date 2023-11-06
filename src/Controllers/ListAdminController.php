<?php
namespace Marion\Controllers;
use Marion\Controllers\Elements\UrlButton;

class ListAdminController extends Controller{
	
	public $_page_id;
    /**
     * Override init method
     *
     * @param array $options
     * @return void
     */
    function init($options=array()){
		parent::init($options);
		if( isset($options['module']) ){
			$this->_module = $options['module'];
		}else{
			$this->_module = _var('mod');
		}
		if( $this->_module ){
			$this->_url_script .= "&mod={$this->_module}"; //aggiungo all'url il modulo
		}
		
		$this->setListToolButtons();
    }

    /**
     * Override display method
     *
     * @return void
     */
    function display(){
		switch($this->_action){
			case 'list':
				$this->displayList();
				break;
			case 'duplicate':
			case 'add':
			case 'edit':
				$this->displayForm();
				break;
			default:
				$this->displayContent();
				break;
		}
	}

	public function displayContent(){}

	public function displayForm(){}

    public function displayList(){}


    /**
     * redirect to list
     *
     * @param array $parameters
     * @return void
     */
    function redirectToList($parameters=array()){
		if( _var('url_list') ) {
			$url = _var('url_list');
		}else{
			$url = $this->getUrlList();
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
		
		header('Location: '.$url);
		exit;
		
		
	}

    /**
     * return url list
     *
     * @return string
     */
    function getUrlList(): string{
		$url = $this->getUrlScript()."&action=list";
		if( $this->_page_id ){
			$url .= "&pageID=".$this->_page_id;
		}
		return $url;
	}
    
    /**
     * return edit url
     *
     * @return string
     */
	function getUrlEdit(): string{
		if( $id = _var('id')){
			$url = $this->getUrlScript()."&action=edit&id=".$id;
		}else{
			$url =  $this->getUrlScript()."&action=edit";
		}
		if( $this->_page_id ){
			$url .= "&pageID=".$this->_page_id;
		}
		
		return $url;
	}
	
	/**
     * return add url
     *
     * @return string
     */
	function getUrlAdd(): string{
		$url = $this->getUrlScript()."&action=add";
		
		if( $this->_page_id ){
			$url .= "&pageID=".$this->_page_id;
		}
		return $url;

	}


    private function setListToolButtons(){
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

}