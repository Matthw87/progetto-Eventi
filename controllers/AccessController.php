<?php
use Marion\Core\Marion;
use Marion\Entities\{User};
use Marion\Entities\Cms\Notification;
use Marion\Support\Mail;
use Marion\Controllers\FrontendController;
use Marion\Support\Form\Traits\FormHelper as HelperTrait;
use Marion\Support\Form\FormData;
use Illuminate\Database\Capsule\Manager as DB;

class AccessController extends FrontendController{
	use HelperTrait;
	
	/**
	 * metodo che effettua l'output del template html
	 *
	 * @return void
	 */
	function display(): void{

		
		$action = $this->getAction();
		
		if( $this->isLogged() && $action != 'notauth' && $action != 'logout' ){
			$this->redirectToHome();
		}
		
		switch($action){
		
			case 'notauth':
				$this->notauth();
				break;
		}

	}

	function redirectToHome(){
		if( authUser() ){
			header('Location: '._MARION_BASE_URL_.'account/home');
		}
		
	}


	/**
	 * display logout page
	 *
	 * @return void
	 */
	function logout(): void{
		if( !auth('base') ) {
			header('Location: '._MARION_BASE_URL_."login");
			exit;
		}
		Marion::logout();
		Marion::do_action('action_after_logout');
		header('Location: '._MARION_BASE_URL_."login");
		die();
	}

	/**
	 * Display login page
	 *
	 * @return void
	 */
	public function login(){
		if( auth('base') ) {
			header('Location: '._MARION_BASE_URL_."account/home");
			exit;
		}
		$fields = [
			'email' => [
				'type' => 'email',
                'label' => _translate('email'),
				'validation'=> 'required|email'
			],
			'password' => [
				'type' => 'password',
                'label' => _translate('password'),
				'validation'=> 'required'
			]
		];
		
		$form = new FormData;
		$form->setFields($fields);
		if( okArray($_POST) ){
			
			if( $form->validate($_POST) ){
				$data = $form->validated_data;
				
				$user = User::login($data['email'],$data['password']);
				
				if(is_object($user) ){
					//debugga($user->hasPasswordExpired());exit;
					//controllo se l'utente deve resettare la password
					if( $user->hasPasswordExpired() ){
						$token = $user->createPasswordToken();
						header('Location: '._MARION_BASE_URL_."reset-password/".$token);
						exit;
						
					}


					Marion::setUser($user);
					Marion::do_action('action_after_login');
					$return_location = _var('return_location');
					if( Marion::getConfig('generale','redirect_admin_side') == 1 && authAdminUser()){
						header("Location: "._MARION_BASE_URL_."backend/index.php");
					}else{
						if( $return_location ){
							header("Location: "._MARION_BASE_URL_.$return_location);
						}else{
							header("Location: "._MARION_BASE_URL_."account/home");
						}	
					}
				}else{
					$this->errors[] = _translate($user);
				}
			}else{
				$this->errors = $form->errors;
			}
		}
		$this->output('access/login.htm');
	}

	/**
	 * Display forgot password page
	 *
	 * @return void
	 */
	function forgotPassword(): void{
		$fields = [
			'email' => [
				'type' => 'email',
                'label' => _translate('email'),
				'validation'=> 'required|email|max:100'
			]
		];
		
		$form = new FormData;
		$form->setFields($fields);
		if( okArray($_POST) ){
			
			if( $form->validate($_POST) ){
				$data = $form->validated_data;
				$user = User::prepareQuery()->where('email',$data['email'])->getOne();
				
				if(is_object($user)){
					$this->sendMailForgotPassword($user);
					$this->displayMessage("Email di recupero inviata all'indirizzo ".$data['email'],'success');
				}else{
					$this->errors[] = _translate('user_not_exists');
				}
			}else{
				$this->errors = $form->errors;
			}
		}
		$this->output('access/forgot_password.htm');
	}

	function notauth(){
		$this->output('access/not_auth.htm');
	}

	

	/**
	 * Display signup page
	 *
	 * @return void
	 */
	function signup(): void{
		$fields = [
			'email' => [
				'type' => 'email',
                'label' => _translate('registration.email'),
				'validation'=> 'required|email|max:100'
			],
			'password' => [
				'type' => 'password',
                'label' => _translate('registration.password'),
				'validation'=> [
					'required',
					$GLOBALS['PASSWORD_FORM_RULE']
				]
			],
			'name' => [
				'type' => 'text',
                'label' => _translate('registration.name'),
				'validation'=> 'required|max:100'
			],
			'surname' => [
				'type' => 'text',
                'label' => _translate('registration.surname'),
				'validation'=> 'required|max:100'
			],
			'fiscal_code' => [
				'type' => 'text',
                'label' => _translate('registration.fiscal_code'),
				'validation'=> 'max:100'
			],
			'cellular' => [
				'type' => 'text',
                'label' => _translate('registration.cellular'),
				'validation'=> 'required|phone|max:100'
			],
			'address' => [
				'type' => 'text',
                'label' => _translate('registration.address'),
				'validation'=> 'max:100'
			],
			'city' => [
				'type' => 'text',
                'label' => _translate('registration.city'),
				'validation'=> 'max:100'
			],
			'province' => [
				'type' => 'select',
                'label' => _translate('registration.province'),
				'options' => function(){
					$options = [_translate('select...')];
					$province = DB::table('provincia')
						->orderBy('sigla')
						->select('sigla','nome')
						->get()
						->toArray();
					foreach($province as $pr){
						$options[$pr->sigla] = $pr->nome;
					}
					return $options;
				}
			],
			'country' => [
				'type' => 'select',
                'label' => _translate('registration.country'),
				'options' => function(){
					$options = [_translate('select...')];
					$province = DB::table('country as c')
						->join('countryLocale as l','l.country','=','c.id')
						->where('locale',_MARION_LANG_)
						->orderBy('name')
						->select('id','name')
						->get()
						->toArray();
					foreach($province as $v){
						$options[$v->id] = $v->name;
					}
					return $options;
				}
			],
			'postal_code' => [
				'type' => 'text',
                'label' => _translate('registration.postal_code'),
				'validation'=> 'max:100'
			],
			'vat_number' => [
				'type' => 'text',
                'label' => _translate('registration.vat_number'),
				'validation'=> 'max:100'
			],
			'company' => [
				'type' => 'text',
                'label' => _translate('registration.company'),
				'validation'=> 'max:100'
			],
			'sdi_code' => [
				'type' => 'text',
                'label' => _translate('registration.sdi_code'),
				'validation'=> 'max:100'
			],
			'pec' => [
				'type' => 'email',
                'label' => _translate('registration.pec'),
				'validation'=> 'max:100'
			],
		];
		
		$form = new FormData;
		$form->setFields($fields);
		if( isset($_POST['formdata']) ){
			if( $form->validate($_POST['formdata']) ){
				$data = $form->validated_data;
				$user = User::login($data['email'],$data['password']);
				
				$user = User::create();
				$data['username'] = $data['email'];
				$data['active'] = 0;
				$data['profile_id'] = 0;
				$data['deleted'] = 0;
				$array['password'] = password_hash($data['password'], PASSWORD_DEFAULT); 

				$user->set($data);
				
				$result = $user->save();
				if(is_object($result)){
					Marion::do_action('action_user_registration',$result);
					$this->sendConfirmRegistration($result);
					$this->setVar('email',$result->email);
					$this->output('access/registration_successful.htm');
					exit;

				}else{
					$this->errors[] = _translate($result);
				}

			}else{
				$this->errors = $form->errors;
			}
		}

		$dataform = $form->prepare();
		$this->setVar('dataform',$dataform);
		$this->output('access/signup.htm');
		
	}

	/**
	 * Display activation page
	 *
	 * @param string $token
	 * @return void
	 */
	function activation(string $token): void{

		$user = User::byActivationToken($token);
		if( $user ){
			$user->active = 1;
			$user->save();
			$user->removeActivationToken($token);
			$this->displayMessage(_translate('activation.success'));
		}else{
			$this->errors[] = _translate('activation.error');
		}
		$this->output('access/activation.htm');
	}


	/**
	 * metodo che invia la mail di notifica relativa all'attivazione di un'utente
	 *
	 * @param [type] $user
	 * @return void
	 */
	function sendMailNewUser($user): void{

	}




	function ajax(){

		$action = $this->getAction();
		switch($action){
			case 'login':
				$response = $this->login_ajax();
			break;
		}

		echo json_encode($response);
	}


	function login_ajax(){
		$dati = $this->getFormdata();
		$array = $this->checkDataForm('login',$dati);
		if($array[0] == 'ok'){
			$res = User::login($array['username'],$array['password']);
			
			if(is_object($res) ){
				Marion::setUser($res);
				Marion::do_action('action_after_login');
				
				$response = array(
					'result' => 'ok'
				);
				
			}else{
				$response = array(
					'result' => 'nak',
					'error' => _translate($res)
				);
				
			}
		}else{
			$response = array(
				'result' => 'nak',
				'error' => $array[1]
			);
			
		}
		return $response;
	}



	/**
	 * Reset password page
	 *
	 * @param string $token
	 * @return void
	 */
	function resetPassword(string $token): void{
		$fields = [
			'password' => [
				'type' => 'password',
                'label' => _translate('reset-password.password'),
				'validation'=> [
					'required',
					$GLOBALS['PASSWORD_FORM_RULE']
				]
			],
			'password_confirmation' => [
				'type' => 'password',
                'label' => _translate('reset-password.password_confirmation'),
				'validation'=> 'required'
			],
			'token' => [
				'type' => 'hidden',
                'label' => _translate('reset-password.token'),
				'validation'=> 'required',
			],
		];
		
		$form = new FormData;
		$form->setFields($fields);
		if( isset($_POST['formdata']) && okArray($_POST['formdata']) ){
			
			if( $form->validate($_POST['formdata']) ){
				$data = $form->validated_data;
				if( $data['password'] != $data['password_confirmation'] ){
					$this->errors = _translate('reset-password.password_not_match');
				}
				$user = User::byPasswordToken($data['token']);
				//debugga($data);exit;
				if( $user ){
					$user->changePassword($data['password']);
					$user->removePasswordToken($token);
					$this->displayMessage('Password cambiata con successo!');
				}else{
					$this->errors[] = "Token scaduto o non valido";
				}
			}else{
				$this->errors = $form->errors;
			}
		}else{
			$form->data['token'] = $token;
		}
		$dataform = $form->prepare();
		$this->setVar('dataform',$dataform);
		$this->output('access/reset_password.htm');
	}	




	/**
	 * metodo che invia la mail di recupero password
	 *
	 * @param [type] $user
	 * @return void
	 */
	private function sendMailForgotPassword($user): void{
		$general = Marion::getConfig('general');
		//debugga($general);exit;
		$token = $user->createPasswordToken();
		$url = Marion::getAsboluteBaseUrl()."reset-password/".$token;
		$content = _translate(['forgot-password.email_content',$user->name,$url,$url]);
		$subject = _translate(['forgot-password.email_subject',isset($general['site_name'])?$general['site_name']:'']);
		$this->setVar('content',$content);


		//preparo l'html
		ob_start();
		$this->output('mail/mail_forgot_pwd.htm');
		$html = ob_get_contents();
		ob_end_clean();
		$sender = $general['mail'];
		Mail::from($sender)
			->setHtml($html)
			->setSubject($subject)
			->setTo($user->email)
			->send();
		
	}


	/**
	 * metodo che invia la mail di conferma iscrizione
	 *
	 * @param [type] $user
	 * @return void
	 */
	private function sendConfirmRegistration(User $user): void{
		
		
		$general = Marion::getConfig('general');
		$token = $user->createActivationdToken();

		$url = Marion::getAsboluteBaseUrl()."activation/".$token;
		$content = _translate(['registration.email_content',$user->name,$url,$url]);
		$subject = _translate(['registration.email_subject',$general['site_name']]);

		$this->setVar('content',$content);
		ob_start();
		$this->output('mail/mail_activation.htm');
		$html = ob_get_contents();
		ob_end_clean();

		Mail::from($general['mail'])
			->setHtml($html)
			->setSubject($subject)
			->setTo($user->email)
			->send();
		

	}

}


?>