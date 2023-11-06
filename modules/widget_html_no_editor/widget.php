<?php
use Marion\Components\PageComposerComponent;
class WidgetHtmlNoEditorComponent extends  PageComposerComponent{
	
	function build($data=null){
			$dati = $this->getParameters();
			if( isset($dati['content'][$GLOBALS['activelocale']]) ){
				echo html_entity_decode($dati['content'][$GLOBALS['activelocale']]);
			}
	}
}
?>