<?php
namespace Catalogo;
class ProductTabAdminController{
	public $_ctrl_admin; //ProductAdminCtrl
	public $_ctrl; //ProductAdminCtrl clonato
	private $_twig_vars; //variabili twig
	
	

	//Il costruttore riceve in input il Controller che gestisce i prodotti
	function __construct($ProductAdminCtrl){
		$this->_ctrl_admin = $ProductAdminCtrl;
		$this->_ctrl = clone $ProductAdminCtrl;
		$this->_ctrl->_twig_vars = array();
		
		$path_class = new \ReflectionClass(get_class($this));
		//debugga('../modules/'.basename(dirname($path_class->getFileName())).'/templates_twig/admin');exit;
		if( file_exists('../modules/'.basename(dirname($path_class->getFileName())).'/templates_twig/admin') ){
			$this->_ctrl->addTwingTemplatesDir('../modules/'.basename(dirname($path_class->getFileName())).'/templates_twig/admin');
		}
		
		
	}


	//metodo che statbilisce disabilitare la tab nel form prodotto
	function isEnabled():bool{
		return true;
	}
	
	//metodo che statbilisce se ricaricare il contenuto della tab dopo il salvataggio 
	function reloadContent():bool{
		return false;
		
	}

	//metodo che statbilisce se ricaricare la pagina del form dopo il salvataggio
	function reloadPage():bool{
		return false;
		
	}

	//metodo che carica nel form del prodotto i file css e javascript necessari per il funzionamento della tab
	function setMedia(){
		
	}


	/* BEGIN METODI EREDITATI DAL PRODUCT CONTROLLER */

	function registerJS($link,$position='head',$priority=99){
		$this->_ctrl_admin->registerJS($link,$position,$priority);
	}

	function registerCSS($link){
		$this->_ctrl_admin->registerCSS($link);
	}
	function getFormdata(){
		return $this->_ctrl->getFormdata();
	}

	function getID(){
		return $this->_ctrl->getID();
	}

	function getAction(){
		return $this->_ctrl->getAction();
	}


	//verifica se il form è stato sottomesso
	function isSubmitted(){
		return $this->_ctrl->isSubmitted();
		
	}

	//genera i dati del form a partire dal nome del form e dei dati
	function getDataForm($nameform=null,$data=null){
		return $this->_ctrl->getDataForm($nameform,$data,$this);
	}
	//controlla i dati del form a partire dal nome del form e dei dati
	function checkDataForm($nameform=null,$data=null,$override=null){
		
		return $this->_ctrl->checkDataForm($nameform,$data,$override,$this);
	}

	// imposta una variabile nel template
	function setVar($key,$val=NULL){
		$this->_ctrl->setVar($key,$val);
	}


	// metodo che stampa la pagina html
	function output($page){

		
		$this->_ctrl->output($page);
	}


	/* END METODI EREDITATI DAL PRODUCT CONTROLLER */

	

	



	/* BEGIN METODI PER IL DISPLAY DELLA TAB NEL FORM PRODODTTO */
	//restituisce il titolo della tab da aggiungere nel form prodotto
	public function getTitle(): string{

		return '';
	}

	//restituisce il tag univoco della tab da aggiungere nel form prodotto
	public function getTag():string{
		
		return '';
	}


	//restituisce il contenuto della tab
	public function getContent(){
		
	}

	
	function checkData(){
		
		return 1;
	}

	// metodo che viene richiamato quando il form è sottomesso dopo il salvataggio del prodotto
	function process($product=null){
		
	}


	/* END METODI PER IL DISPLAY DELLA TAB NEL FORM PRODODTTO */







}



?>