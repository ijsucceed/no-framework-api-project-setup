<?php
use \Firebase\JWT\JWT;

class Request
{
    /**
	 * This class handles all project endpoints
	 */
	protected $postdata; // request body
	protected $request; // convert request body to stdclass
	protected $decoded;
	
	function __construct() {
		$this->postdata = file_get_contents("php://input");
		$this->request = json_decode($this->postdata);
    }
    
    public function secureJwtHeader() : void
    {
        // get the jwt token
		$jwt = app_subword(app_get_authorization_header(), 1);
		
		if( empty($jwt) ) {
			app_false_response( 'Format is Authorization Bearer [access token]', 401 );
		}
		
		try {
			$this->decoded = JWT::decode($jwt, getenv('APP_SECRET'), array('HS256'));
		}
		catch(\Exception $e) {
			app_false_response( 'Invalid token signature' );
		}
		
		// if user key is not set and null
		$this->decoded->data->user ?? app_false_response( 'Invalid token' );
	}
	
	protected final function validateJson() {
		// if it not an object or listed property not found
		if( ! is_object ( $this->request ) ) {
            app_false_response( 'Invalid object', 401 );
		}
	}
}
