<?php
use \Marion\Core\Marion;
use Marion\Entities\Cms\PageComposer;
define('_PAGE_COMPOSER_DASHBOARD_PAGE_ID_',1);



function pagecomposer_duplicate_page($id_old,$id_new){
	$composer = new PageComposer($id_old);
	$composer->duplicate($id_new);
	
}

function pagecomposer_delete_page($data){
	
}

function pagecomposer_init(){
	
	if (defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_) {
		$composer = new PageComposer(_PAGE_COMPOSER_DASHBOARD_PAGE_ID_);
		
		$GLOBALS['pagecomposer_dashboard'] = $composer;
	}else{
		$database = Marion::getDB();
		$sel = $database->select('*','footer','active=1');
		if( okArray($sel) ){
			$preview_page = 0;
			if( _var('preview_page') ){
				$preview_page = 1;
			}
			$options['no_current'] = 1;
			
			$composer = new PageComposer($sel[0]['id_page'],$preview_page,$options);
			$GLOBALS['pagecomposer_footer'] = $composer;
			
		}
	}
	
}

function pagecomposer_action_register_media_front($ctrl){
	if(isset($GLOBALS['pagecomposer_current']) && $GLOBALS['pagecomposer_current']){
		$composer = $GLOBALS['pagecomposer_current'];
		$composer->addDataToCtrl($ctrl);
	}
}



function pagecomposer_action_register_twig_function_front($ctrl){
	
	$ctrl->addTemplateFunction(
		new \Twig\TwigFunction('page_composer', function ($block,$composer_name=null) {
			return page_composer($block,$composer_name);
		})
	);
}





function page_composer($block,$object=NULL){
		
	
	if( $object ){
		if( isset($GLOBALS['pagecomposer_'.$object]) ){
			$composer = $GLOBALS['pagecomposer_'.$object];
		}
	}else{
		$composer = PageComposer::getCurrent();
	}

	

	if(isset($composer) && is_object($composer)){
		$composer->build($block);
	}
	
	
	
}
/*
require_once('wordpress.php');
function page_composer_parse_html(&$content){
	

	/*
	

	preg_match_all("/\[\[(.*)\]\]/",$cont,$matches);
	foreach($matches[1] as $k => $v){
		$list = explode(' ',$v);
		foreach($list as $k2 => $t){
			if( $k2 == 0 ){
				$function[$k] = $t;
			}else{
				$params[$k][] = $t;
			}

		}
	}

	

	foreach($matches[0] as $k => $v){
		if( function_exists($function[$k]) ){
			ob_start();
			
			if( okArray($params[$k]) ){
				call_user_func_array($function[$k],$params[$k]);
			}else{
				call_user_func($function[$k]);
			}
			$html = ob_get_contents();
			ob_end_clean();
			
			
			$change[$v] = $html;
		}
	}

	
	
	
	foreach($change as $k => $v){
		$chiave = preg_replace('/\[/','\[',$k);
		$chiave = preg_replace('/\]/','\]',$chiave);
		
		$cont = preg_replace("/{$chiave}/",$v,$cont);
	}
	
	
	return true;

	// Find all registered tag names in $content.
    preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
   // $tagnames = array_intersect( array_keys( $shortcode_tags ), $matches[1] );
	
    if ( empty( $matches[1] ) ) {
        return $content;
    }

	
 
    $content = do_shortcodes_in_html_tags( $content, $ignore_html, $tagnames );
 
    /*$pattern = get_shortcode_regex( $tagnames );
    $content = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $content );
 
    // Always restore square braces so we don't break things like <!--[if IE ]>.
    $content = unescape_invalid_shortcodes( $content );
	
    return $content;
}
*/


Marion::add_action('pagecomposer_duplicate','pagecomposer_duplicate_page');
Marion::add_action('pagecomposer_delete','pagecomposer_delete_page');
/*
	Questa azione viene lanciata in fase di avvio dell'applicazione.
	In particolare viene caricato il pagecomposer del footer nel lato frontend mentre il pagecomposer della dashboard lato admin

*/
Marion::add_action('init','pagecomposer_init');
/*
	Questa azione viene lanciata in fase di caricamento dei media (js,css) del controller.
	In particolare vengono caricati nel controller i file js e css relattivi alle componenti del pagecomposer

*/
Marion::add_action('action_register_media_front','pagecomposer_action_register_media_front');

/*
	Questa azione viene lanciata prima del render della pagina twig del controller
	In particolare viene caricata una funzione di template in TWIG

*/
Marion::add_action('action_register_twig_function_front','pagecomposer_action_register_twig_function_front');



/*
	Questa azione viene lanciata su un copntenuto html in maniera tale stampare in corrispondenza di alcuni schortcode del contenuto html

*/

Marion::add_action('action_parse_html','page_composer_parse_html');




function pagecomposer_build_button(){
	
	echo "<button>ciao</button>";
}




?>