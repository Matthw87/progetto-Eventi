<?php
use Marion\Core\Marion;
use Marion\Entities\Country;
use Marion\Entities\Cms\HomeButton;
use Marion\Controllers\BackendController;
use Marion\Support\Form\Traits\FormHelper;
use Marion\Support\Form\FormData;
use Illuminate\Database\Capsule\Manager as DB;

class AccountController extends BackendController{
	use FormHelper;	
	public $_auth = 'base';


	/**
	 * personal data form
	 *
	 * @return void
	 */
	function personalData(): void{
		$this->setMenu('personal_data');
		$fields = [
			'email' => [
				'type' => 'email',
                'label' => _translate('registration.email'),
				'validation'=> 'required|email|max:100'
			],
			'reset_password' => [
				'type' => 'switch',
                //'label' => _translate('registration.password'),
				//'validation'=> 'required|max:100'
			],
			'password' => [
				'type' => 'password',
                'label' => _translate('registration.password'),
				'validation'=> ['max:100']
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
					$options = [_translate('select..')];
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
					$options = [_translate('select..')];
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
		if( array_key_exists('formdata',$_POST) ){
			$formdata = $_POST['formdata'];
			if( okArray($formdata) ){
				if( $formdata['reset_password'] ){
					$fields['password']['validation'][] = 'required';
				}
			}
		}
		
		
		
		$form->setFields($fields);
		
		if( isset($formdata) && okArray($formdata) ){
			
			if( $form->validate($formdata) ){
				$data = $form->validated_data;
				//debugga($data);exit;

				if( $data['reset_password'] ){
					$_tmp_password = $data['password'];
					$data['password'] = password_hash($_tmp_password, PASSWORD_DEFAULT); 
				}
				$user = Marion::getUser();
				$data['active'] = 1;
				$data['deleted'] = 0;

				$user->set($data);
				
				$res = $user->save();
				if(is_object($res)){
					Marion::setUser($res);
					$this->displayMessage(_translate('personal-data.utente_aggiornato_con_successo'));

				}else{
					$this->errors[] = _translate($res);
				}
				
			}else{
				$this->errors = $form->errors;
			}
		}else{
			$user = Marion::getUser();
			$userdata = $user->getDataForm();
			unset($userdata['password']);
			$form->data = $userdata;
		}

		
		$dataform = $form->prepare();
		$this->setVar('dataform',$dataform);
		$this->output('account/personal_data.htm');
	}


	/**
	 * home page
	 *
	 * @return void
	 */
	function home(): void{
		$this->setMenu('home');
		$buttons = HomeButton::prepareQuery()->where('active',1)->orderBy('orderView','ASC')->get();
		$this->setVar('buttons',$buttons);
		$this->output('account/home.htm');
	}






	
}


?>