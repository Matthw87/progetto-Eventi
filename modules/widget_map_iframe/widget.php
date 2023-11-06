<?php
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
class WidgetMapIframeComponent extends  PageComposerComponent{
	
	private $template_html = '@widget_map_iframe/widgets/render.htm'; //html del widget

	function registerJS($data=null){
		/*
			se il widget necessita di un file js allora occorre registralo in questo modo
			
			PageComposer::registerJS("url del file"); // viene caricato alla fine della pagina
			PageComposer::registerJS("url del file",'head'); // viene caricato nel head 
			

		*/
		PageComposer::registerJS("modules/widget_map_iframe/js/script.js");
	}
	function registerCSS($data=null){
		/*
			se il widget necessita di un file css allora occorre registralo in questo modo
			
			PageComposer::registerCSS("url del file"); 
			

		*/
		PageComposer::registerCSS("modules/widget_map_iframe/css/style.css");
	}

	function build($data=null){
			
			
			/*$parameters: parametri di configurazione del widget
			  Questo array contiene i parametri di configurazione del widget
			*/
			$parameters = $this->getParameters();

			if($parameters){
				$this->setVar('titolo',$parameters['title'][$GLOBALS['activelocale']]);
			
				$this->setVar('dati',$parameters);
			
			} 
			

			
			$this->output($this->template_html);
				
		
	}


	//questo metodo stabilisce se per un determiato box della pagina il widget è disponibile
	function isAvailable($box){
		$available = true;
		switch($box){
			case 'col-100':
				//$available = false;
				break;
			case 'col-75':
				//$available = false;
				break;
			case 'col-33':
				//$available = false;
				break;
			case 'col-25':
				//$available = false;
				break;
			default:
				//$available = false;
				break;

		}
		
		
		return $available;
	}
}






?>