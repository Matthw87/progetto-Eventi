<?php

use Api\Controllers\Controller;
use OpenApi\Annotations as OA;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Core\Marion;
use Marion\Entities\User;
use Marion\Support\Form\FormData;
use Marion\Support\Mail;
use Marion\Traits\ApiResponse;
use Firebase\JWT\JWT;

class AuthController extends Controller{
    use ApiResponse;
    /**
     * @OA\Post(
     *   path="/api/v1/auth/login",
     *   security={{"apiKey": {}}},
     *   summary="Login",
     *   tags={"Authentication"},
     *   @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Parametro in ISO 639-1 <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>",
     *         @OA\Schema(
     *             type="string",
     *             default="it"
     *         ),
     *   ),
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"username", "password"},
     *               @OA\Property(property="username", type="string"),
     *               @OA\Property(property="password", type="string")
     *            ),
     *        ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Bearer Token",
     *     content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="code",
     *                         type="integer",
     *                         description="The response code"
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         description="The response data"
     *                     ),
     *                     example={
     *                         "code": 200,
     *                         "data": {
     *                              "token": "ABCDEFT12345566HHHH",
     *                              "token_type": "bearer"
     *                         }
     *                     }
     *                 )
     *             )
     *         }
     *   )
     * )
     */
    function login(): void{
        
		$fields = [
			'username' => [
				'type' => 'username',
                'label' => _translate('username'),
				'validation'=> 'required'
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
				
				$user = User::login($data['username'],$data['password']);
				
				if(is_object($user) ){
					//debugga($user->hasPasswordExpired());exit;
					//controllo se l'utente deve resettare la password
					if( $user->hasPasswordExpired() ){
                        $this->response(_translate('auth.errors.password_expired','api'),400);
						exit;
						
					}else{
    

                        $this->successResponse(
                            [
                                'token' => $this->jwt($user),
                                'token_type' => 'bearer'
                            ]
                        );
                    }


		
				}else{
                    $this->response(_translate($user),400);
				}
			}else{
				$this->response($form->errors,400);
			}
		}
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/forgot-password",
     *   security={{"apiKey": {}}},
     *   summary="Forgot password",
     *   tags={"Authentication"},
     *   @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Parametro in ISO 639-1 <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>",
     *         @OA\Schema(
     *             type="string",
     *             default="it"
     *         ),
     *   ),
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="email", type="string"),
     *            ),
     *        ),
     *   ),
      *   @OA\Response(
     *     response=200,
     *     description="Bearer Token",
     *     content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="code",
     *                         type="integer",
     *                         description="The response code"
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="string",
     *                         description="The response data"
     *                     ),
     *                     example={
     *                         "code": 200,
     *                         "data": "Success"
     *                     }
     *                 )
     *             )
     *         }
     *   )
     * )
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
                    $this->successResponse("Email di recupero inviata all'indirizzo ".$data['email']);
				}else{
                    $this->response(_translate('user_not_exists'),400);
				}
			}else{
                $this->response($form->errors,400);
			}
		}
	}


    /**
     * @OA\Get(
     *   path="/api/v1/auth/me",
     *   summary="Get profile data user",
     *   security={{"apiKey": {}}, { "bearerAuth":{} }},
     *   tags={"Authentication"},
     *   @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Parametro in ISO 639-1 <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>",
     *         @OA\Schema(
     *             type="string",
     *             default="it"
     *         ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Bearer Token",
     *     content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="code",
     *                         type="integer",
     *                         description="The response code"
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         description="The response data"
     *                     ),
     *                     example={
     *                         "code": 200,
     *                         "data": {
     *                              "id": 1,
     *                              "name": "Mario",
     *                              "surname": "Rossi",
     *                              "username": "mariorossi27",
     *                              "email": "mario@rossi.it"
     *                         }
     *                     }
     *                 )
     *             )
     *         }
     *   )
     * )
     */
    function me(): void{
        $user = Marion::getUser();


        $response = [
            'id' => $user->id,
            'name' => $user->name,
            'surname' => $user->surname,
            'username' => $user->username,
            'email' => $user->email,
        ];
        $this->successResponse($response);
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



    function jwt(User $user){
        $key = 'MARION_API';
        $issuedAt = time();
        // jwt valid for 30 days (60 seconds * 60 minutes * 24 hours * 60 days)
        //$expirationTime = $issuedAt + 60 * 60 * 24 * 30;
        $expirationTime = $issuedAt + $this->getApiKey()->token_duration;
        //$expirationTime = $issuedAt + 10;
        $payload = [
            'id' => $user->id,
            'iat' => $issuedAt,
            'exp' => $expirationTime,
        ];
        //JWT::$leeway = 60; //60 seconds
        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }

}
?>