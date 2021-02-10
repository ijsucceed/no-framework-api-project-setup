<?php
class AuthRequest extends Request
{
    /**
	 * Authenticate user email with passwordless signup
	 */
	public function signupWithEmail() : void
	{
		$this->validateJson();
		
        $request = $this->request;
        
		// if the required json key was not sent
		if ( ! app_properties_found( $request, [
			'email',
			'firstname',
			'lastname',
			'phone',
			'country',
			'pin',
			'verify_identity',
        ]) ) 
		{
			app_false_response( 'Required parameter missing' );
		}

		// the user unique value ( phone, username, email, or id )
		$email = app_clean_input( $request->email );
		$phone = app_clean_input( $request->phone );
		$firstname = app_clean_input( $request->firstname );
		$lastname = app_clean_input( $request->lastname );
		$country = app_clean_input( $request->country );
		$pin = app_clean_input( $request->pin );
		$verify_identity = app_clean_input( $request->verify_identity );

		if($verify_identity) {}

		if( empty($email)
			&& empty($phone)
			&& empty($firstname)
			&& empty($lastname)
			&& empty($country)
			&& empty($pin))
		{
			app_false_response('Enter all required fields.');
		}

		if( ! app_validate_username($firstname) ) {
			app_false_response('Enter your firstname');
		}

		if( ! app_validate_username($lastname) ) {
			app_false_response('Enter your lastname');
		}

		if( ! app_validate_password($pin) ) {
			app_false_response('Password must be greater than 4 characters');
		}

		if( ! app_validate_email($email) ) {
			// Validate the phone number
			app_false_response( 'Email not valid', 200 );
		}

		if( ! app_validate_phone($phone) ) {
			// Validate the phone number
 			app_false_response( 'Phone not valid', 200 );
		}
		
		$User = new User(); // user object instance
		$User->setEmail( $email );
		
		if( $User->emailExist() ) {
			app_false_response( 'Email already exist', 200 );
		}

		if( $User->check([
			'user_phone' => $phone
		]) ) {
			app_false_response( 'Phone already exist', 200 );
		}

		$time_now = app_get_datetime();

		$create = $User->create([
			'user_firstname' => $firstname,
			'user_lastname' => $lastname,
			'user_email' => $email,
			'user_phone' => $phone,
			'user_country' => $country,
			'user_pin' => md5($pin),
			'user_status' => 0,
			'user_created_at' => $time_now,
			'user_updated_at' => $time_now,
		]);

		if( !$create ) {
			app_false_response('Fail to create account, try again', 503);
		}

		app_true_response('Account created successfully', [
			'user' => [
				'id' => $User->getId()
			]
		]);
	}

	/**
	 * Authenticate user email with passwordless signup
	 */
	public function emailExist() : void
	{
		$this->validateJson();
		
        $request = $this->request;
        
		// if the required json key was not sent
		if ( ! app_properties_found( $request, [
			'email',
        ]) ) 
		{
			app_false_response( 'Required parameter missing' );
		}

		// the user unique value ( phone, username, email, or id )
		$email = app_clean_input( $request->email );

		if ( ! app_validate_email( $email ) ) {
			// Validate the phone number
			app_false_response( 'Email not valid', 200 );
		}

		$user = new User(); // user object instance
		$user->setEmail( $email );
		
		if( ! $user->emailExist() ) {
			app_false_response( 'Email not found', 200 );
		}

		app_true_response('Email found');
	}

	/**
	 * Authenticate user email with passwordless signup
	 */
	public function phoneExist() : void
	{
		$this->validateJson();
		
        $request = $this->request;
        
		// if the required json key was not sent
		if ( ! app_properties_found( $request, [
			'phone',
        ]) ) 
		{
			app_false_response( 'Required parameter missing' );
		}

		// the user unique value ( phone, username, email, or id )
		$phone = app_clean_input( $request->phone );

		if ( ! app_validate_phone( $phone ) ) {
			// Validate the phone number
			app_false_response( 'Phone not valid', 200 );
		}

		$user = new User(); // user object instance
		
		if( ! $user->check([
			'user_phone' => $phone
		]) ) {
			app_false_response( 'Phone not found', 200 );
		}

		app_true_response('Phone found');
	}

	/**
	 * Authenticate user email with passwordless login
	 * @return
	 */
    public function loginWithEmail() : void
    {
		$this->validateJson();

		$request = $this->request;
		
		// if the required json key was not sent
		if( ! app_properties_found ( $request, [
			'user',
			'pin'
		]) ) 
		{
			app_false_response('Required parameter missing', 401);
		}

		$user = app_clean_input( $request->user );
		$pin = app_clean_input( $request->pin );

		$User = new User();

		if( app_validate_email($user) ) {
			$User->setEmail($user);
			$use_cred = 'Email';
		}
		else {
			$use_cred = 'Phone';
		}

		// if user does not exist
		if ( ! $User->check([
			'OR' => [
				'user_phone' => $user,
				'user_email' => $user,
			]
		]) )
		{
			app_false_response("$use_cred or pin is incorrect", 200);
		}

		$user_info = $User->getInfo( [
			'user_id(id)',
			'user_pin(pin)',
		], [
			'OR' => [
				'user_phone' => $user,
				'user_email' => $user,
			]
		]);

		if( $user_info['pin'] == md5($pin) ) {
			// valid pin
			app_true_response("$use_cred or pin is incorrect", $user_id);
		}

		$types = appGetTokenMeta();
		$type = $types['normal'];

		$jwt = app_jwt_token([
			'user' => $user,
			'type' => 'access'
		], $type['seconds']);

		app_true_response( 'Authenication successful', [
			'type' => $type['value'],
			'token' => $jwt
		] );
	}

	/**
	 * Save the user new password
	 * @return
	 */
	public function resetPassword() 
	{
		$this->secureJwtHeader();

		$token_type = $this->decoded->data->type ?? ''; // token type
		$token_email = (string) $this->decoded->data->user; // token email

		$User = new User();
		$User->setEmail( $token_email );
		
		// if the token type is not for setting up a new password
		if( 'password' != $token_type ) {
			app_false_response( 'Invalid token type', 400 );
		}

		// if the email is match with a user
		if( ! $User->emailExist() ) {
			app_false_response( 'No email record found', 404 );
		}

		// set the user id
		$User->setId( $User->getIdByEmail() );

        $req = $this->request; // the JSON request body

		// if password key was found in the request body
		if ( ! app_properties_found( $req, [
			'password'
		]) ) 
        {
            app_false_response ( 'Required parameters missing', 400 );
		}
		
		$password = app_clean_input( $req->password ); // password body
		
		if( ! app_validate_password($password) ) {
			app_true_response('New password should be atleast 4 characters');
		}

        $set = [
            'user_pin' => md5( $password )
		];

		if( $User->update( $set ) ) {
            app_true_response( 'Password changed successfully' );
		}
		else {
			app_false_response( 'No changes made', 201 );
		}
	}
	
	/**
	 * Request a new token based on token type
	 * @return
	 */
    public function requestEmailToken() : void
    {
		$this->validateJson();

		$request = $this->request;
		
		// if the required json key was not sent
		if( ! app_properties_found ( $request, [
			'email',
			'type'
		]) ) 
		{
			app_false_response('Required parameter missing', 401);
		}

		$email = app_clean_input( $request->email );
		$type = app_clean_input( $request->type );

		$types = appGetTokenMeta();

		if( ! array_key_exists($type, $types) ) {
			app_false_response( 'No token type found', 400);
		}

		$User = new User();
		$User->setEmail($email);

		// if user does not exist
		if ( ! $User->emailExist() )
		{
			app_false_response("Email not found", 200);
		}

		$type = $types[$type];
		$request = $User->makeRequest( $type['value'], (time() + $type['seconds']), app_ott() );

		if( ! $request['success'] )
		{
			app_false_response('Fail to request to send passcode');
		}

		$mail = new Mail();
		$mail->subject = sprintf( '%s: is your %s OTT', $request['code'], getenv('APP_NAME') );
		$mail->to = $email;
		$mail->body = '';
		$mail->set_default_template_header();
		$mail->append_html( 'Hello,' );
		$mail->append_html(sprintf(
			'Please, use the One Time Token (OTT) below to proceed on %s.',
			getenv('APP_NAME'))
		);
		$mail->append_html( '' );
		$mail->append_html( sprintf('<strong style="font-size:20px;">%s</strong>', $request['code']) );
		$mail->append_html( '' );
		$mail->append_html( sprintf('This link will expire in %s and can only be use once.', $type['expiresIn']) );
		$mail->append_html( '' );
		$mail->append_html( '' );
		$mail->append_html( 'Cheers,', 'newline' );
		$mail->set_default_template_footer();
		$mail->send();

		app_true_response('A magic link has been sent to your email');
	}

	/**
	 * Verify a token and authenticate
	 * @return
	 */
    public function verifyToken() : void
    {
		$this->validateJson();

		$request = $this->request;
		
		// if the required json key was not sent
		if ( ! app_properties_found ( $request, [
			'token',
		]) ) 
		{
			app_false_response('Required parameter missing', 401);
		}

		$token = app_clean_input( $request->token );
		
		$user = new User();

		// check if token is found
		if ( ! $user::checkRequestToken( $token ) ) {
			// if the token record not found
			$user->deleteRequest($token);
			app_false_response ( 'Token has expired', 401);
		}

		$data = $user::getRequestTokenInfo( $token ); // token data
		$type = $data['type'];
		$email = $data['user'];

		$user->setEmail($email);
		$user->deleteRequest($token);

		$types = appGetTokenMeta();

		// if the token type is supported
		if( ! array_key_exists($type, $types) ) {
			app_false_response( 'No token type found', 400);
		}

		$type = $types[$type]; // token type meta

		$jwt = app_jwt_token([
			'user' => $email,
			'type' => $type['value']
		], $type['seconds']);

		app_true_response( 'Token successful', [
			'type' => $type['value'],
			'token' => $jwt
		] );
	}

    /** 
	 * Create a new user account 
	 * 
	 * @return 
	 */
	public function onboarding(): void
    {
		$this->validateJson();

        $req = $this->request;
		
		if ( ! app_properties_found ( $req, [
				'fullname',
				'email'
			]) ) 
        {
            app_false_response ( 'Object key (fullname, email) are required' );
        }

		/** Request data */
		$fullname = app_clean_input( $req->fullname );
		$email = app_clean_input( $req->email );
		
		// check against empty inputs
		if ( empty( $fullname ) )
		{
			app_false_response ( 'Please, enter fullname' );
		}
	
		$token_available = isset( $_SESSION['token']['email'] );
		// if the token email is available
		if ( $token_available ) {
			$token_email = $_SESSION['token']['email'];
			// if the email in token don't match the email sent!
			if ( $token_email != $email ) {
				app_false_response('Token Error: token data failed' );
			}
		}
		else {
			app_false_response( 'Token Error: token has expired' );
		}
		
		$user = new user( 0, $email );

		// if the email exist already
		if ( $user->user_exist() ) {
            app_false_response( 'Email already exist' );
        }

		// create a new user
		$insert = $user->create_user( [
			'email' => $email,
			'fullname' => $fullname
		]);

		if ( $insert ) {
			$_SESSION['ID'] = $user->get_user_id(); // set login session
			$this->response['status'] = true; // set response status
			unset( $_SESSION['token'] );
		}
		else {
			app_false_response ( "Server Error: Fail to insert new entry" );
		}

		app_echo ( $this->response );
	}
}