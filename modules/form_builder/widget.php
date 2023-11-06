<?php
// Rinomimanre l'oggetto MyWidget e riportare lo stesso nel file config.xml nel campo 'function'
use Marion\Components\PageComposerComponent;
use Marion\Core\Marion;
use Marion\Entities\Cms\PageComposer;
use FormBuilder\FormBuilder;
class WidgetForm extends  PageComposerComponent{
	
	public $other_elements = array();
	public $other_actions = array();

	function __construct(){
		parent::__construct();
		Marion::do_action('action_form_builder_register_element');
		$this->registerElements();
		$this->registerActions();
	}


	function registerJS($data=null){
		
		if( okArray($this->other_actions) ){
			foreach( $this->other_actions as $class){
				$class::registerMedia();
			}
		}
		if( okArray($this->other_elements) ){
			foreach( $this->other_elements as $class){
				$class::registerMedia();
			}
		}
		
		PageComposer::registerJS("https://www.google.com/recaptcha/api.js");
		//PageComposer::registerJS("modules/widget_developer/js/script.js");
		PageComposer::registerJS("modules/form_builder/js/send.js");
		PageComposer::registerJS("https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js");
	}
	function registerCSS($data=null){
		
		PageComposer::registerCSS("https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css");
		PageComposer::registerCSS("modules/form_builder/css/style.css");
	}

	function build($data=null){
		
		//$widget = Marion::widget('widget_slider_tag');
		$dati = $this->getParameters();
		
		if( okArray($dati) ){
			

			$html = $dati['html'][$GLOBALS['activelocale']];
			$campi = $dati['fields'];
			//debugga($campi);
			
			$campi[] = array(
				'name'=>'recaptcha',
				'key' => isset($dati['key_site_recaptcha'])?$dati['key_site_recaptcha']:'',
				'id_box'=>$this->id_box
			);
			
			
			
			

		
			
			

			$opzioni = $this->getDataFromHtml($html,$campi);
		
			
			foreach($campi as $campo){
				
				if( !isset($campo['type']) || (isset($campo['type']) &&  $campo['type'] != 'radio')){
					$opz = $opzioni[$campo['name']];
					$opzioni_campo = isset($opz)?$opz:array();
					$render["/\[\[".$campo['name']."\]\]/"] = $this->getHtmlField($campo,$opzioni_campo);
				}else{
					if( isset($campo['options'][$GLOBALS['activelocale']]) ){
						$radio_options =  explode("\n",$campo['options'][$GLOBALS['activelocale']]);
						foreach($radio_options as $radio_value){
							if( $radio_value = trim($radio_value) ){
								$opz = $opzioni[$campo['name'].'_'.$radio_value];
								$opzioni_campo = isset($opz)?$opz:array();
								$render["/\[\[".$campo['name'].'_'.$radio_value."\]\]/"] = $this->getHtmlField($campo,$opzioni_campo);
							}
						}
					}
					
	
				}
				
			}

			//debugga($render);
			//debugga($html);

			$id_box = $this->id_box;
			$output = "<form id='form_builder_{$id_box}' onsubmit='form_builder_submit({$id_box}); return false;' enctype='m'ultipart/form-data'>\r\n";
			$result = preg_replace(array_keys($render), array_values($render), $html);
			$output .= $result;
			$output .= "<input type='hidden' name='form_builder_id' value='{$id_box}'>";
			//$output .= '<button id="submit" type="submit" class="btn btn-primary form-control">Invia</button>';
                        $output .= "<div style='display:none' class='form_builder_error' id='form_builder_error_{$id_box}'></div>";
			$output .= "<div style='display:none' class='form_builder_success' id='form_builder_success_{$id_box}'></div>";
			$output .= "</form>\r\n";
			echo $output;
			
		

		}
	}

	function registerElements(){
		
		if( okArray($this->other_elements) ) return true;
		$FormBuilderElements  = array();
		/*foreach(get_declared_classes() as $class){
			if(is_subclass_of($class,'FormBuilderElement')) $FormBuilderElements[] = $class;
		}*/
		foreach(FormBuilder::$elements as $class){
			if( !is_object($class) ){
				$class = new $class();
			}
			if(is_subclass_of($class,'FormBuilder\FormBuilderElement')) $FormBuilderElements[] = $class;
		}
		foreach($FormBuilderElements as $v){
			$this->other_elements[$v::getID()] = $v;
		}
	}

	function registerActions(){
		if( okArray($this->other_actions) ) return true;
		$FormBuilderActions  = array();
		/*foreach(get_declared_classes() as $class){
			if(is_subclass_of($class,'FormBuilderAction')) $FormBuilderActions[] = $class;
		}*/
		foreach(FormBuilder::$actions as $class){
			if( !is_object($class) ){
				$class = new $class();
			}
			if(is_subclass_of($class,'FormBuilder\FormBuilderAction')) $FormBuilderActions[] = $class;
		}
		

		foreach($FormBuilderActions as $v){
			$this->other_actions[] = $v;
		}
	}


	function getDataFromHtml(&$html,$campi){
		$radio_input = array();
		foreach($campi as $c){
			if( isset($c['type']) ){
				if( $c['type'] == 'radio'){
					$radio_input[] = $c['name'];
				}
				if( $c['type'] == 'checkboxes'){
					$radio_input[] = $c['name'];
				}
			}
			
		}

		
		


		preg_match_all('/\[\[(.*)\]\]/',$html,$res);
		
		
		foreach($res[1] as $k => $v){
			
			$v = preg_replace('!\s+!', ' ', $v);
			
			$tmp = trim(preg_replace("/\"/","'",$v)); //sostituisco i doppi apici con l'apice singolo
			
			$tmp = preg_replace("/(\s+)=/","=",$tmp);
			
			$tmp = preg_replace("/=(\s+)/","=",$tmp); 
			
			
			
			
			
			$match = preg_match_all("/(='([a-zA-z\*-_\.\+\&\s]+)')/",$tmp,$values);

			
			$_pattern = '';
			if( $match ){
				// vado a sostituire momentaneamente il carattere speciale * con la stringa &&asterisk&& e lo spazio con &&concat&&
				foreach($values[0] as $v1){
				
						
						$_pattern = preg_replace('/\*/','\*',$v1);
						$v2 = preg_replace('/\*/','&&asterisk&&',$v1);
						$concat = preg_replace('/\s/','&&concat&&',$v2);
						
						$tmp = preg_replace("/{$_pattern}/",$concat,$tmp);
					
				}
			}

			
			
			
			$explode = explode(' ',$tmp);

			
			$options = array();
			$field = $explode[0];
			
			unset($explode[0]);
			$_options = array_values($explode);
			$valore_attr = '';
			// vado a ripristinare  il carattere speciale * lo spazio precedentemente sostituiti con con la stringa &&asterisk&& e con &&concat&& rispettivamente
			foreach($_options as $option){
				$explode2 = explode('=',$option);
				
				$valore_attr = trim($explode2[1],"'");
				$valore_attr = preg_replace('/&&concat&&/',' ',$valore_attr);
				$valore_attr = preg_replace('/&&asterisk&&/','*',$valore_attr);
				$options[$explode2[0]] = $valore_attr;
			}
			if( in_array($field,$radio_input) ){
				$field = $field."_".$options['value'];
				
			}
			
			
			$toreturn[$field] = $options;
			$pattern = $res[1][$k];
			$pattern = preg_replace('/\*/','\*',$pattern);
			//debugga($pattern,$field);
			

			//$html = preg_replace('#^' . $pattern . '$#',"{$field}",$html);
			$html = preg_replace("/{$pattern}/","{$field}",$html);
			
		}
		
		//debugga($html);

		
		
		return $toreturn;
	}



	function getHtmlField($campo,$_params=array()){
		if( $campo['name'] == 'recaptcha' ){
		
			//$id_box = $_params['id_box'];
			$id_box = $this->id_box;
			return  "<div class='g-recaptcha' id='recaptcha_{$id_box}' data-sitekey='{$campo['key']}'></div>";

		}
		if( okArray($_params) ){
			$params = '';
			foreach($_params as $k=> $v){
				if( in_array(strtolower(trim($k)),array('type','name','multiple')) ){
					continue;
				}
				$params .= "{$k}='{$v}' ";
			}
		}

		$opzioni = explode("\n",$campo['options'][$GLOBALS['activelocale']]);
		
		if( $campo['type'] == 'email' )  $campo['type'] = 'text';

		switch($campo['type']){
			
			case 'hidden':
			case 'text':
			case 'radio':
			case 'checkbox':
			
			
			case 'password':
				$html = "<input type='{$campo['type']}' name='{$campo['name']}' {$params}>";
				break;
			case 'file':
				$placeholder = $_params['placeholder']?$_params['placeholder']:'file';
				$select_message = $_params['select_file']?$_params['select_file']:'select file';


				$html = "<label for='file_{$campo['name']}' class='form-builder-custom-file' id='btn_file_{$campo['name']}'>
				<span class='form-builder-nome-file'>{$placeholder}</span>
				<span class='form-builder-select-file pulsante-pieno-small'>{$select_message}</span>
				</label>
				<input type='{$campo['type']}' id='file_{$campo['name']}' name='{$campo['name']}' {$params} style='display:none'>
				<script>
				$('#file_{$campo['name']}').change(function() {
				  filename = this.files[0].name
				  $('#btn_file_{$campo['name']}').find('.form-builder-nome-file').html(filename);
				});
				</script>
				";
				break;
			case 'textarea':
				$html = "<textarea type='{$campo['type']}' name='{$campo['name']}' {$params}></textarea>";
				
				break;
			case 'checkboxes':
				$html = "<input type='checkbox' name='{$campo['name']}[]' {$params}>";
				break;
			case 'select':
				$html = "<select type='{$campo['type']}' name='{$campo['name']}' {$params}>";
				if( okArray($opzioni) ){
					foreach($opzioni as $v){
						$v = trim($v);
						$html .= "<option value='{$v}'>{$v}</option>";
					}
				}
				$html .="</select>";
				break;
			case 'multiselect':
				$html = "<select type='{$campo['type']}' name='{$campo['name']}[]' {$params} multiple>";
				if( okArray($opzioni) ){
					foreach($opzioni as $v){
						$v = trim($v);
						$html .= "<option value='{$v}'>{$v}</option>";
					}
				}
				$html .="</select>";
				break;
			case 'datepicker':
				$html = "<input type='text' class='datetimepicker_date' name='{$campo['name']}' {$params}>";
				break;
			case 'radio':

				break;
			case 'checkbox':

				break;
			default:
				if( $this->other_elements[$campo['type']] ){
					$object = $this->other_elements[$campo['type']];
					$html = $object::buildHtml($campo,$_params?$_params:array());

				}

				break;
		}

		return $html;
	}
}






?>