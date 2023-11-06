<?php
    use Marion\Core\Marion;
    use Marion\Support\Form\FormHelper;
    use Marion\Entities\User;
    use Marion\Support\Mail;
    use Illuminate\Database\Capsule\Manager as DB;
    use Marion\Controllers\Controller;

    class ChangeCredentialsController extends Controller
    {
        function display()
        {
            $action = $this->getAction();

            switch($action)
            {
                case 'email':
                    $this->emailForm();
                break;
                case 'change_email':
                    $this->changeEmail();
                break;
                case 'pwd':
                    $this->pwdForm();
                break;
            }
        }

        private function pwdForm()
        {
            $this->setMenu('change_credentials_pwd');
            $this->setTitle(_translate('reset-password.admin_title'));

            if(_var('updated')){
                $this->displayMessage(_translate('reset-password.password_success'));
            }

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
                    'validation'=> 'required|max:100|min:6'
                ]
            ];

            $form = FormHelper::create('change_pwd_form', $this)
                ->layoutFile(_MARION_ROOT_DIR_.'backend/templates/admin/forms/form_change_pwd.xml')
                ->init(function(FormHelper $form) {

                })->validate(function(FormHelper $form) {
                    $data = $form->getValidatedData();

                    if($data['password'] != $data['password_confirmation']) {
                        $form->errors[] = _translate('reset-password.password_not_match');
                        $form->error_fields = ['password_confirmation'];
                    }

                })->process(function(FormHelper $form) {
                    $data = $form->getValidatedData();
                    $user = Marion::getUser();
                    $user->changePassword($data['password']);

                    header('Location: index.php?ctrl=ChangeCredentials&action=pwd&updated=1');
                })->setFields($fields);

            $form->display();
        }

        private function emailForm()
        {
            $this->setMenu('change_credentials_email');
            $this->setTitle(_translate('change_email.admin_title'));

            $token = _var('token');

            if ($token)
            {
                $emailByToken = $this->checkEmailToken($token);

                if (is_object($emailByToken))
                {
                    $user = Marion::getUser();
                    $user->changeEmail($emailByToken->email);
                    $this->removeEmailToken($emailByToken->email, $emailByToken->token);
                    $this->displayMessage(_translate('change_email.messages.success'));
                } else {
                    $this->displayMessage(_translate('reset-password.token_expired'), 'danger');
                }
            }

            if(_var('email_sent')){
                $this->displayMessage(_translate('change_email.messages.email_sent'));
            }

            $fields = [
                'email' => [
                    'type' => 'email',
                    'label' => _translate('change_email.form.fields.email'),
                    'validation'=> 'required|email|max:100',
                    'placeholder' => 'Inserisci la tua email'
                ]
            ];

            $form = FormHelper::create('change_email_form', $this)
                ->setTextSubmitButton(_translate('change_email.buttons.send'))
                ->setIconSubmitButton('fa fa-send')
                ->layoutFile(_MARION_ROOT_DIR_.'backend/templates/admin/forms/form_change_email.xml')
                ->init(function(FormHelper $form) {

                })->validate(function(FormHelper $form) {
                    $data = $form->getValidatedData();
                    $user = User::prepareQuery()->where('email', $data['email'])->getOne();

                    if (is_object($user))
                    {
                        $form->errors[] = _translate('change_email.messages.email_already_used');
                        $form->error_fields = ['email'];
                    }

                })->process(function(FormHelper $form) {
                    $data = $form->getValidatedData();
                    $this->sendMailConfirm($data['email']);
                    header('Location: index.php?ctrl=ChangeCredentials&action=email&email_sent=1');
                })->setFields($fields);

            $form->display();
        }

        private function sendMailConfirm($email)
        {
            $config = Marion::getConfig('general');
            $token = $this->createEmailToken($email);
            $url = Marion::getAsboluteBaseUrl() . "backend/index.php?ctrl=ChangeCredentials&action=email&token=".$token;
            $content = _translate(['change_email.email_content', $url]);
            $subject = _translate(['change_email.email_subject', $config['nomesito']]);
            $this->setVar('content', $content);

            ob_start();
            $this->output('@core/admin/access/mail/change_credentials.htm');
            $html = ob_get_contents();
            ob_end_clean();

            $sender = $config['mail'];

            Mail::from($sender)
                ->setHtml($html)
                ->setSubject($subject)
                ->setTo($email)
                ->send();
        }

        private function changeEmail()
        {
            $this->setMenu('change_credentials_email');
            $this->setTitle(_translate('change_email.admin_title'));

            $token = _var('token');
            $emailByToken = $this->checkEmailToken($token);

            if (is_object($emailByToken))
            {
                $user = Marion::getUser();
                $user->changeEmail($emailByToken->email);
                $this->removeEmailToken($emailByToken->email, $emailByToken->token);
                $this->displayMessage(_translate('change_email.messages.success'));
            } else {
                $this->displayMessage(_translate('reset-password.token_expired'));
            }

            $this->output('@core/admin/access/change_credentials/form_email.xml');
        }

        private function checkEmailToken($token)
        {
            $record = DB::table('email_tokens')->where('token', $token)->first();

            if($record)
            {
                if( time() > strtotime($record->expiration_date)) {
                    return null;
                }
                return $record;
            }

            return null;
        }

        private function createEmailToken($email): string{

            $dateTime = new \DateTime();
            $dateTime->modify("+".$_ENV['EMAIL_TOKEN_EXPIRATION_TIME']."ms");

            $toinsert = [
                'email' => $email,
                'token' => base64_encode(uniqid('',true)),
                'expiration_date' => $dateTime->format('Y-m-d h:i:s')
            ];

            DB::table('email_tokens')->insert(
                $toinsert
            );

            return $toinsert['token'];
        }

        private function removeEmailToken(string $email, string $token): void{

            DB::table('email_tokens')->where(
                [
                    'email' => $email,
                    'token' => $token
                ]
            )->delete();
        }
    }