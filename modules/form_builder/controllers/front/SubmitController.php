<?php
use Marion\Controllers\FrontendController;
use Marion\Core\Marion;
use FormBuilder\{ReCaptcha,FormBuilder,FormBuilderElement,FormBuilderAction};
use Marion\Support\Mail;
class SubmitController extends FrontendController
{
	public $other_elements = array();
	public $other_actions = array();
	
	public $message_invalid_field;
	public $message_mandatory_field;


	function loadStrings(){
		$this->message_invalid_field = _translate('invalid_field_message','form_builder');
		$this->message_mandatory_field = _translate('mandatory_field_message','form_builder');
	}

	function loadExtensions(){
		
		Marion::do_action('action_form_builder_register_element');
		
		
		$FormBuilderElements  = array();
		foreach(FormBuilder::$elements as $class){
			if( !is_object($class) ){
				$class = new $class();
			}
			if(is_subclass_of($class,'FormBuilder\FormBuilderElement')) $FormBuilderElements[] = $class;
		}
		foreach($FormBuilderElements as $v){
			$this->other_elements[$v::getID()] = $v;
		}
	

	
		$FormBuilderActions  = array();
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
	function checkNullField($name,$formdata){
		return !trim($formdata[$name]);
	}
	function ajax()
	{
		$this->loadExtensions();
		$this->loadStrings();
		$errors = [];
		$field_errors = [];
		$formdata = $_POST;
		//debugga($formdata);exit;
		$database = Marion::getDB();
		$data = $database->select('*', 'composed_page_composition', "id={$formdata['form_builder_id']}");
		$params = unserialize($data[0]['parameters']);
		$redirect_url = '';

		//debugga($params);exit;
		if( $params['enable_redirect'] ){
			$redirect_url = $params['redirect_url'];
		}
		$actions_submit = $params['action_submit'];
		
		//if ($this->recaptcha($formdata['g-recaptcha-response'], $params)) {
		if (okArray($data)) {
			$campi = $params['fields'];
			$template_mail = $params['email']['message'][$GLOBALS['activelocale']];
			$email_from = $params['email']['email_mittente'];
			$email_to = $params['email']['email_destinatari'];
			$subject = $params['email']['subject'][$GLOBALS['activelocale']];
			$field_form_sender = '';
			if(  $params['email']['use_field_form'] ){
				$field_form_sender = $params['email']['field_form_sender'];
			}
		}

		/*
			controllo se i file sono obbligatori
		*/
		
		foreach ($campi as $row) {
			if ($row['type'] == 'file' && $row['mandatory'] == 1) {
				$name = $row['name'];

				if ($_FILES) {
					$flag = false;

					foreach ($_FILES as $k => $v) {
						if ($k == $name) {
							if($v['name'] != '') {
								$flag = true;
							}
						}
					}

					if (!$flag) {
						$field_errors[] = $name;
						$errors[] = $this->messageError($name,'mandatory');
					}
				} else {
					$field_errors[] = $name;
					$errors[] = $this->messageError($name,'mandatory');
				}
			}
		}
		//debugga($campi);
		foreach ($campi as $row) {
			$check_error = false;

			if($row['type'] == 'checkbox'){
				//se il campo è di tipo checkbox allora se non viene passato nessun valore impostiamo un valore nullo
				if( !isset($formdata[$row['name']])) $formdata[$row['name']] = '';
			}
			if ($row['type'] != 'file' && $row['type'] != 'checkboxes' && $row['type'] != 'multiselect' && $row['mandatory'] == 1) {
				$name = $row['name'];

				$check_error = $this->checkNullField($name,$formdata);
				if( $check_error ){
					$field_errors[] = $name;
					$errors[] = $this->messageError($name,'mandatory');
				}
			}

			if( !$check_error ){
				if( trim($formdata[$name]) && $row['type'] == 'email'){
					if (!filter_var($formdata[$name], FILTER_VALIDATE_EMAIL)) {
						$field_errors[] = $name;
						$errors[] = $this->messageError($name,'invalid');
					}
				}
			}
				
		}
		if( !okArray($errors)){
			if( isset($formdata['g-recaptcha-response']) ){
				if(!$this->recaptcha($formdata['g-recaptcha-response'], $params)){
					$errors[] = $this->messageError('CAPTCHA','invalid');
				}
			}
			
		}
		
		

		if( !okArray($errors) ){

			foreach($this->other_elements as $key => $element){
				foreach ($campi as $row) {
						if( $row['type'] == $key ){
							$check = $element::check($formdata[$row['name']]);
							if( $check != 1 ){
								$field_errors[] = $row['name'];
								$errors[] = $check;
							}
						}
				}
				
			}
			
		}
		
		foreach ($formdata as $key => $value) {
			$optionsToCheck = $this->getOptions($key, $campi);
			
			if( $optionsToCheck ){
				if (!$this->check($value, $optionsToCheck)) {
					$errors[] = $this->messageError($key);
				};
			}
			
		}
		
		if (okArray($errors)) {
			$res = [
				'ok' => false,
				'errors' => array($errors[0]),
				'field_errors' => array_unique($field_errors)
			];
		} else {
			
			$messages = array();
			
			foreach($actions_submit as $action){
				switch($action){
					case 'send_mail':
						if( $field_form_sender ){
							$email_from = $formdata[$field_form_sender];
						}
						$check = $this->sendMail($template_mail, $formdata, $email_from, $email_to, $subject);
						if( $check != 1){
							$errors[] = $check;
						}else{
							$messages[] =  $params['email']['success_message'][$GLOBALS['activelocale']];
						}
						break;
					default:
						foreach($this->other_actions as $class){
							$actions_class = array_keys($class::register());
							if( in_array($action,$actions_class) ){
								
								$check = $class::execute($action,$formdata,$params,$this);
								if( $check != 1){
									$errors[] = $check;
								}else{
									$messages[] = $class::successMessage($action);
								}
							}
						}
						break;
				}	
				
			}
			if ($errors) {
				$res = [
					'ok' => false,
					'errors' => $errors
				]; 
			}else{
				$message = '';
				
				foreach($messages as $m){
					$message .= $m."<br>";
				}
				$res = [
					'ok' => true,
					'message' => $message,
					'redirect_url' => $redirect_url
				];
			}
		}

		echo json_encode($res);
		/*} else {
			$errors = [
				'Captcha non corretto'
			];

			$res = [
				'ok' => false,
				'errors' => $errors
			];

			echo json_encode($res);
		}*/
	}

	function check($value, $options)
	{

		if( is_array($value)  ){
			
			
			if(  $options['mandatory'] == 1 ){
				foreach ($options['options'] as $option) {
					$valori[] = trim($option);
				}
				return okArray(array_intersect($value,$valori));
			}
			return true;

		}else{
			if ($value == '' && $options['mandatory'] == 1) {
				return false;
			}
			if( isset($options['options']) ){
				if ($options['options'][0] == '') {
					return true;
				} else {
					foreach ($options['options'] as $option) {
						$option = trim($option);
						if ($option == $value) {
							return true;
						}
					}
					return false;
				}
			}
			
		}
	}

	function getOptions($key, $campi)
	{
		$result = null;
		foreach ($campi as $row) {
			if ($row['name'] == $key) {
				$toDeserialize = $row['options'][$GLOBALS['activelocale']];
				$result['options'] = explode("\n", $toDeserialize);
				$result['mandatory'] = $row['mandatory'];
			}
		}

		if (is_array($result)) {
			return $result;
		} else {
			return false;
		}
	}

	function stringToArray()
	{
		$string = _var('formdata');
		$first = explode("&", $string);

		foreach ($first as $v) {
			$second = explode("=", $v);
			$array[$second[0]] = $second[1];
		}

		return $array;
	}

	function messageError($label,$type='invalid')
	{	
		if( $type == 'invalid' ){
			$message = $this->message_invalid_field;
		}else{
			$message = $this->message_mandatory_field;
		}
		
		return sprintf($message, $label);
	}

	function sendMail($template_mail, $formdata, $email_from, $email_to, $subject)
	{
		foreach ($formdata as $k => $v) {
			$patterns[] = "/\[\[{$k}\]\]/";

			if( is_array($v) ){
				$string = '';
				foreach($v as $v1){
					$string .= "{$v1}, "; 
				}
				$v = preg_replace('/\, $/','',$string);
			}
			$replacements[] = $v;
		}

		$data = preg_replace($patterns, $replacements, $template_mail);

		$this->setVar('content_mail', $data);
		ob_start();
		$this->output('@form_builder/mail.htm');
		$html = ob_get_contents();
		ob_end_clean();

		

		

		
		$to = $email_to;
		$from = $email_from;


		$mail = Mail::from($from)
			->setHtml($html)
			->setSubject($subject)
			->setTo($to);
		foreach($_FILES as $files) {
			if($files['name'] != '') {
				$mail->attachFromPath($files['tmp_name']);
			}
		}
		$mail->send();

		return true;
	}

	function recaptcha($response, $data)
	{
		if ($data['recaptcha'] == 1) {
			require_once('modules/form_builder/recaptcha_options.php');

			$re = new ReCaptcha($data['key_secret_recaptcha']);
			$resp = $re->verifyResponse($_SERVER["REMOTE_ADDR"], $response);

			if (!$resp->success) {
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
}
