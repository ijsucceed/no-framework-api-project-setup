<?php declare(strict_types = 1);

use \Firebase\JWT\JWT;

function app_clean_input( string $str ) : string 
{
    // This clean up user input and data sent.
    $str = stripslashes( strip_tags( $str ) );
    return trim( htmlentities( $str ) );
}

function app_time_zone (string $zone = "Africa/lagos") 
{
    /**
     * Set and return time_zone
     * @param string $zone The country time zone.
     * @return string;
     */
    $time_zone = new DateTimeZone ( $zone );
    return $time_zone;
}

/**
 * Set the datetime object, and also time zone.
 * @param $datetime
 */
function app_datetime( DateTime $datetime = null ) : DateTime
{
    if ( $datetime == null )
        $datetime = new Datetime();

    $datetime->setTimeZone( app_time_zone() );

    return $datetime;
}

/**
 * This first get the _datetime object and the timezone.
 * Then returns the date in this
 * format year-month-day H:i:s
 */
function app_get_datetime( DateTime $datetime = null ) : string
{
    if ( $datetime == null ) {
        $datetime = app_datetime(); // datetime object
    }
    
    return $datetime->format('Y-m-d H:i:s'); // return
}

function app_get_date( DateTime $datetime = null ) : string
{
    /**
     * This first get the _datetime object and the timezone.
     * Then returns the date in this
     * format year-month-day H:i:s
     */
    if ( $datetime == null ) {
        $datetime = app_datetime(); // datetime object
    }
    
    return $datetime->format('Y-m-d'); // return
}
/**
 * Get a date from a now date by number of month
 * @param $date is starting date
 * @param $duration is the number of month
 * @return string
 */
function app_datetime_by_duration( string $date, int $duration ) {
    $date = app_datetime( new DateTime( $date ) );
    $date->add( new DateInterval('P'. $duration));
    return app_get_datetime( $date );
}
/**
 * Return a nice date string
 * @param $datetime is the datetime object
 * @param $format is the readable format
 * @return string
 */
function app_datestring( $datatime, string $format = 'd M, Y \a\t h:m a' ) 
{
    /**
     * return date in readable format
     * @return string
     */
    return date( $format, strtotime($datatime));
}

function app_validate_date($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) === $date;
}

function app_get_timestamp( string $format ) : int
{
    /**
     * Get a date format and return the timestamp.
     * @param $format y-m-d H:i:s
     */
    $datatime = app_datetime( new Datetime ( $format ) );
    return $datatime->getTimestamp();
}

function app_time() : int
{
    /**
     * This is use in place of the php time() to get the currentlocal time stamp
     * @return mixed any type, mostly number strings
     */
    $time = app_get_timestamp( app_get_datetime() );
    return $time;
}

function app_log_error( string $msg ) : void 
{
    /**
     * Log errors to the log file
     * @return void
     */

	file_put_contents( '../error.log', "\n" . $msg, FILE_APPEND | LOCK_EX);
}

function app_json_header() : void 
{
    header('Content-type: application/json');
}

function app_slugify( string  $str ) : string 
{
    /**
     * Turn a 'str ing' into str-ing
     * ex: how you => how-you
     * 
     * @param $str is the string to slug
     * @return string  
     */
    return preg_replace('/[^A-Za-z0-9-]+/', '-', $str);
}

function app_send_mail_with_mailgun(  
    $to, 
    string $subject,
    string $body,
    string $from = 'notify@mg.Remi.cash' ) : void
{
    // First, instantiate the SDK with your API credentials
    $mg = \Mailgun\Mailgun::create(getenv('MAIL_GUN_KEY'), 'https://api.eu.mailgun.net'); // For US servers

    // Now, compose and send your message.
    // $mg->messages()->send($domain, $params);
    $mg->messages()->send( getenv('MAIL_GUN_EMAIL_DOMAIN'), [
    'from'    => 'Remi.cash <' . $from . '>',
    'to'      => $to,
    'subject' => $subject,
    'html'    => $body
    ]);
}

function app_get_notification_emails() {
    $emails = ['coachlovemore@gmail.com', 'lionbrandinvest@gmail.com', 'ikwuje24@gmail.com'];
    return $emails;
}

function app_generate_reference()
{
    $ref = md5( uniqid() . time() ); // reference
    return $ref;
}

function app_request_uri() : string
{
    /**
     * Get the $_SERVER['REQUEST_URI']
     * @return String
     */
    return ltrim($_SERVER['REQUEST_URI'], '/');
}

function app_uri() : string
{
    return getenv('BASE') . app_request_uri();
}

function app_db()
{
    /**
     * Get the db connection
     * @return String
     */
    // require database connection                        
    $db = include('db.php');
    
    return $db;
}

function app_validate_username( string $val ) : bool 
{
    // Check value (if it Numeric type, If it less than 3 letters)
    if( is_numeric( $val ) || strlen( $val ) < 3)
        return false;
   
    return true; // return the result
}

function app_validate_password( string $val ) : bool 
{
    // if length is 6 and above and is integer
    if ( strlen( $val ) >= 4 ) {
        return true;
    }
    return false;
}

function app_validate_email( string $email ) : bool 
{
    if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL) ) {
        return false;
    }
    return true;
}

function app_validate_phone( string $val ) : bool 
{
    /** 
     * Validate local phone number
     * @param string val is the number passed
     * @return Boolean
    */
    // Check value (if it Numeric type, If it up to 11, if it not)
    if( ! is_numeric( $val ) || strlen( $val ) > 13 )
        return false;

    /*
    // Default prefixs
    // You can add as much
    $prefixs = array("080", "081", "070", "090");
    
    $pre = substr( $val, 0, 3 ); // get the first three value
    
    // set valid to false
    $valid = false; 
    
    foreach ( $prefixs as $prefix ) {
        if( $pre == $prefix ) {
            $valid = true;
            break;
        }
    }

    */
    return true; // return the result
}

function app_properties_found( stdClass $object, array $properties ) : bool
{
    /**
     * Iterate through an object 
     * and see if the passed properties is found
     * else return false or true
     */

    foreach ( $properties as $value ) :
        if ( false === property_exists( $object, $value) ) {
            return false;
        break;
        }
    endforeach;

    return true;
}

/**
 * Kill a request and print a JSON
 * @param string $response_text is the text to print out
 * { "status": false, "msg": "fail to process" }
 */
function app_false_response( string $response_text = 'No request', int $code = 401  ) : void 
{
    http_response_code($code); // set http status code
    app_json_header(); // set json header

    echo json_encode([
        'status' => false,
        'code' => $code,
        'msg' => $response_text,
    ]);

    exit();
}

/**
 * echo an array as json
 */
function app_echo( array $array ) : void 
{
    app_json_header(); // json header

    echo json_encode( $array );
}

/**
 * End a request and print a JSON response
 */
function app_true_response( string $response_text = '', array $data = [] ) : void 
{
    http_response_code(200); // set http status code
    app_json_header(); // set http header to json

    echo json_encode( [
        'status' => true,
        'code' => 200,
        'msg' => $response_text,
        'data' => $data
    ] );

    exit();
}

/**
 * Hash any character
 * @param $str is the character
 */
function app_hash( string $str, $action = 'encode' ): string
{
    $output = ''; // the defualt output

    $hashids = new Hashids\Hashids();
    if ( 'encode' == $action )
    {
        $str = str_split( str_replace( ' ', '', $str) ); // split the string to an array
        $output = $hashids->encode( $str );
    }
    else {
        $decode_str = $hashids->decode( $str );
        if ( is_array( $decode_str ) ) 
        {
            $output = implode( $decode_str );
        }
    }

    return $output;
}

function app_str_decode( $str ): string
{
    $hashids = new Hashids();
    $hash_str = $hashids->encode( $str );
    if ( is_string( $hash_str ) )
    {
        return $hash_str;
    }
    return '';
}

/**
 * Get word index in string
 * @param $str is the string (words)
 * @param $index is word position
 */
function app_subword( string $str, int $index = 0 ): string
{
    $str = explode(  ' ', trim( $str ) );
    return $str[ $index ] ?? '';
}

/** 
 * Get header Authorization
 * */
function app_get_authorization_header(): string
{
    $header = '';
    if ( isset($_SERVER['Authorization']) ) {
        $header = trim($_SERVER["Authorization"]);
    }
    else if ( isset($_SERVER['HTTP_AUTHORIZATION']) ) { //Nginx or fast CGI
        $header = trim( $_SERVER["HTTP_AUTHORIZATION"] );
    } elseif ( function_exists('apache_request_headers') ) {
        $requestHeader = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeader = array_combine(array_map('ucwords', array_keys($requestHeader)), array_values($requestHeader));
        //print_r($requestHeaders);
        if (isset($requestHeader['Authorization'])) {
            $header = trim($requestHeader['Authorization']);
        }
    }
    return $header;
}

/**
 * get access token from header
 * */
function app_get_bearer_token(): string {
    $header = app_get_authorization_header();
    // HEADER: Get the access token from the header
    if (!empty($header)) {
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $matches[1];
        }
    }
    return '';
}

/** 
 * Generate JWT token 
 **/
function app_jwt_token( array $data, int $duration = 3600 ) {
    $issuedAt  = time();
    $notBefore = $issuedAt + 10;
    $expireIn  = $notBefore + $duration;

    if ( getenv('ENV') == 'live' ) {
        $payload = array(
            'iss' => getenv('API_DOMAIN'),
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expireIn,
            'data' => $data,
        );
    }
    else {
        $payload = array(
            'iss' => getenv('API_DOMAIN'),
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expireIn,
            'data' => $data
        );
    }

    $jwt = JWT::encode( $payload, getenv('APP_SECRET'), 'HS256' );

    return $jwt;
}

/**
 * Allows cors
 */
function app_allow_cors(): void
{
    // array holding allowed Origin domains
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Headers: Authorization, Content-Type, Origin, no-cache');
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
    header("HTTP/1.1 200 OK");
}

/**
 * Generate a random 4-6 digit code
 */
function app_ott(): int {
    $max = rand(1000, 9999);
    $min = rand(5000, 999999);
    if($max > $min) {
        $code = rand($min, $max);
    }
    else {
        $code = rand($max, $min);
    }

    return $code;
}

/**
 * Get all token templates
 */
function appGetTokenMeta(): array {
    return [
        'cashout' => [
            'value' => 'cashout',
            'expiresIn' => '10 minutes',
            'seconds' => 600, // 10 minutes
        ],
        'transfer' =>  [
            'value' => 'transfer',
            'expiresIn' => '10 minutes',
            'seconds' => 600, // 10 minutes
        ], // 10 minutes 
        'password' =>  [
            'value' => 'password',
            'expiresIn' => '30 minutes',
            'seconds' => 1800, // 30 minutes
        ], // 30 minutes
        'normal' =>  [
            'value' => 'normal',
            'expiresIn' => '2 hours',
            'seconds' => 7200, // 2 hours
        ],
    ];
}

function appEnv(string $key): string {
    return (string) $_ENV[$key];
}