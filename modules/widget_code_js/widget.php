<?php
// Rinomimanre l'oggetto MyWidget e riportare lo stesso nel file config.xml nel campo 'function'
use Marion\Components\PageComposerComponent;

class WidgetCodeJSComponent extends PageComposerComponent{
		
	function build($data=null){
			/*$parameters: parametri di configurazione del widget
			  Questo array contiene i parametri di configurazione del widget
			*/
			$parameters = $this->getParameters();
			if( isset($parameters['js_box']) ){
				echo "<script>".$parameters['js_box']."</script>";
			}
			
	}
}
?>