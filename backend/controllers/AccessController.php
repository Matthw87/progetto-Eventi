<?php
use Marion\Core\Marion;
use Marion\Entities\User;
use Marion\Controllers\Controller;
use Marion\Support\Form\Traits\FormHelper;
use Marion\Support\Form\FormData;
use Marion\Support\Mail;

class AccessController extends Controller{
	use FormHelper;
    public $_auth = '';
	public $_required_access = false;

	function display()
	{
		$action = $this->getAction();

		switch($action){
			case 'logout':
				$this->logout();
				break;

			case 'pwd_reset_form':
				$this->pwd_reset_form();
				break;

			case 'pwd_forgot_form':
				$this->pwd_forgot_form();
				break;
		}
	}

	function pwd_reset_form()
	{
		$token = _var('token');
		$this->setVar('token', $token);
		$this->output('@core/admin/access/pwd_reset.htm');
	}

	function pwd_forgot_form()
	{
		$this->output('@core/admin/access/pwd_forgot.htm');
	}

	function logout(){
		Marion::logout();
		Marion::do_action('action_after_logout');
		header('Location: index.php');
		//$this->login();
	}


	function ajax(){
		$action = $this->getAction();
		switch($action){
			case 'login':
				$response = $this->login_ajax();
			break;

			case 'pwd_expired':
				$response = $this->pwd_expired();
			break;

			case 'pwd_forgot':
				$response = $this->pwd_forgot();
			break;
		}

		echo json_encode($response);
	}


	function login_ajax(){
		$formData = $this->getFormdata();

		$fields = [
			'email' => [
				'type' => 'email',
                'label' => _translate('email'),
				'validation'=> 'required|email|max:100'
			],
			'password' => [
				'type' => 'password',
                'label' => _translate('password'),
				'validation'=> 'required|max:100'
			]
		];

		$form = new FormData;
		$form->setFields($fields);

		if($form->validate($formData))
		{
			$data = $form->validated_data;
			$user = User::login($data['email'],$data['password']);

			if(is_object($user))
			{
				if($user->hasPasswordExpired())
				{
					$token = $user->createPasswordToken();
					$response = array(
						'result' => 'error_pwd_expired',
						'urlRedirect' => _MARION_BASE_URL_ . "backend/index.php?ctrl=Access&action=pwd_reset_form&token=".$token
					);
				} else {
					Marion::setUser($user);
					Marion::do_action('action_after_login');

					$response = array(
						'result' => 'ok'
					);
				}
			} else {
				$response = array(
					'result' => 'nak',
					'error' => _translate($user)
				);
			}
		} else {
			$response = array(
				'result' => 'nak',
				'error' => $form->errors
			);
		}

		return $response;
	}

	function pwd_expired()
	{
		$formData = $this->getFormdata();

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
				'validation'=> 'required',
			],
		];

		$form = new FormData;
		$form->setFields($fields);

		if($form->validate($formData))
		{
			$data = $form->validated_data;
			if( $data['password'] != $data['password_confirmation'] ){
				$response = array(
					'result' => 'nak',
					'errors' => [_translate('reset-password.password_not_match')]
				);
			} else {
				$user = User::byPasswordToken($data['token']);

				if($user)
				{
					$user->changePassword($data['password']);
					$user->removePasswordToken($data['token']);
					Marion::setUser($user);
					Marion::do_action('action_after_login');
					$response = array(
						'result' => 'ok',
						'message' => [_translate('reset-password.password_success')],
						'urlRedirect' => _MARION_BASE_URL_ . "backend/index.php"
					);
				}else{
					$response = array(
						'result' => 'nak',
						'errors' => [_translate('reset-password.token_expired')]
					);
				}
			}
		} else{
			$response = array(
				'result' => 'nak',
				'errors' => $form->errors
			);
		}

		return $response;
	}

	function pwd_forgot()
	{
		$formData = $this->getFormdata();


		$fields = [
			'email' => [
				'type' => 'email',
                'label' => _translate('email'),
				'validation'=> 'required|email|max:100'
			]
		];

		$form = new FormData;
		$form->setFields($fields);

		if($form->validate($formData))
		{
			$data = $form->validated_data;
			$user = User::prepareQuery()->where('email', $data['email'])->getOne();

			if(is_object($user)){
				$this->sendMailForgotPassword($user);
				$response = array(
					'result' => 'ok',
					'message' => [_translate(['reset-password.email_reset_pwd_message', $data['email']])]
				);
			}else{
				$response = array(
					'result' => 'nak',
					'errors' => [_translate('reset-password.user_not_exists')]
				);
			}

		} else {
			$response = array(
				'result' => 'nak',
				'errors' => $form->errors
			);
		}

		return $response;
	}

	private function sendMailForgotPassword($user)
	{
		$config = Marion::getConfig('general');
		$token = $user->createPasswordToken();
		$url = _MARION_BASE_URL_ . "backend/index.php?ctrl=Access&action=pwd_reset_form&token=".$token;
		$content = _translate(['forgot-password.email_content', $url]);
		$subject = _translate(['forgot-password.email_subject', $config['nomesito']]);
		$this->setVar('content', $content);

		ob_start();
		$this->output('@core/admin/access/mail/change_credentials.htm');
		$html = ob_get_contents();
		ob_end_clean();

		$sender = $config['mail'];

		Mail::from($sender)
			->setHtml($html)
			->setSubject($subject)
			->setTo($user->email)
			->send();
	}
}
?>