<?php
/**
 * Class help to create, read, update, delete, and perform related function about a user.
 */
class User
{
    public const TABLE_USER = 'app_users'; // the user database table
    public const TABLE_REQUEST = 'app_user_requests';

    private int $id; // the user id
    private string $email; // the user id

    public function __construct( int $id = 0, string $email = '' )
    {
        $this->id = $id;
        $this->email = $email;
    }

    /**
     * Set the class
     */
    public function start( int $id, string $email ) : void
    {
        $this->setId( $id );
        $this->setEmail( $email );
    }

    /**
     * Stop the class by reseting values
     */
    public function stop() : void
    {
        $this->setId( 0 );
        $this->setEmail( '' );
    }

    /**
     * Set the user id
     * @param int $id is the unique integer
     */
    public function setId( int $id ) : void
    {
        $this->id = $id;
    }
    
    /**
     * Set the user user email
     * @param string $email is the user email
     */
    public function setEmail( string $email ) : void
    {
        $this->email = $email;
    }

    /**
     * Get the user id
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Get the user user email
     */
    public function getEmail() : string
    {
        return $this->email;
    }

    /**
     * Get user info based on users columns
     * @param $col is user column to get
     */
    public function getInfo( $col, array $where = [] )
    {
        $get = app_db()->get( self::TABLE_USER, $col, $where);

        if ( is_string( $get ) || is_array( $get ) ) {
            return $get;
        }

        return '';
    }

    /**
     * Get user id based on email
     * @param $email is the email
     */
    public function getIdByEmail( string $email = '' ) : int
    {
        if ( empty( $email ) ) {
            $email = $this->getEmail();
        }

        $get = app_db()->get( self::TABLE_USER, 'user_id', [
            'user_email' => $email
        ]);

        if ( is_numeric( $get ) ) {
            return $get;
        }

        return 0;
    }

    /**
     * Check if a user record exist in the database
     * @param array $where column(s) 
     */
    public function check( array $where ) : bool
    {
        $has = app_db()->has( self::TABLE_USER, $where );

        if ( is_bool( $has ) ) {
            return $has;
        }

        return false;
    }

    /**
     * Check if a user record exist
     * @param $user is a unique user entry
     */
    public function exist( int $user = 0 ) : bool
    {
        if ( $user == 0 ) {
            $user = $this->getId();
        }

        $has = app_db()->has( self::TABLE_USER, [
            'user_id' => $user
        ]);

        // if it is bool
        if ( is_bool( $has ) )
        {
            return $has; // true or false
        }
        return false;
    }

    /**
     * Check if a user email exist
     * @param $user is a unique user email
     */
    public function emailExist( string $email = '' ) : bool
    {
        if ( empty( $user ) ) {
            $email = $this->getEmail();
        }

        $has = app_db()->has( self::TABLE_USER, [
            'user_email' => $email
        ]);

        // if it is bool
        if ( is_bool( $has ) )
        {
            return $has; // true or false
        }
        return false;
    }

    /**
     * Select user from the database based on some conditions
     * @param $where is the SQL condition
     * @return array
     */
    static function select( array $where ) : array 
    {
        $db = app_db();
        // select the results
        $results = $db->select ( self::TABLE_USER, [
            'user_email(email)',
            'user_phone(phone)',
            'user_id(id)',
            'user_registered_on(registered_on)'
        ], $where );
        // if there is result
        if ( is_array( $results ) ) 
        {
            return $results;
        }
        // return an empty result
        return [];
    }

    /**
     * Get users from the database
     * @param $start is the starting point
     * @param $end is the end limit
     * @return array
     */
    public function get( $where ) : array 
    {
        $db = app_db();
        // get the user entry
        $result = $db->get( self::TABLE_USER, [
            'user_email(email)',
            'user_phone(phone)',
            'user_id(id)',
            'user_registered_on(registered_on)'
        ], $where);
        // if there is result
        if ( is_array( $result ) && 1 <= count( $result ) ) 
        {
            return $result;
        }
        // return an empty result
        return [];
    }

    /**
     * Add a new user record
     * @param $data is the user information
     * @return bool
     */
    public function create( array $col_data ) : bool 
    {
        $db = app_db(); // db instance
        // insert a new user record
        $stmt = $db->insert( self::TABLE_USER, $col_data); // insert the data

        // if it was good i.e if user was created
        if ( $stmt->rowCount() > 0 )
        {
            $this->setId( $db->id() );
            return true;
        }
        // return an array response
        return false;
    }

    /**
     * Update a user record in the database
     * @param array $col is the columns
     */
    public function update( array $col ) : bool
    {
        $db = app_db();
        // update the user entry
        $stmt = $db->update( self::TABLE_USER, $col, [
            'user_id' => $this->getId()
        ] );
        // if it was good
        if ( $stmt->rowCount() > 0 ) {
            return true;
        }
        return false;
    }

    /**
     * Delete a current user record in the database
     */
    public function delete() : bool 
    {
        self::delete_request( (string) $this->getEmail() );

        // update the user entry
        $stmt = app_db()->delete( self::TABLE_USER, [
            'user_id' => $this->getId()
        ] );
        // if it was good
        if ( $stmt->rowCount() > 0 ) {
            return true;
        }
        return false;
    }

    /**
     * Make a request e.g forgot password or withdrawal
     * @param $type is the type of request
     */
    public function makeRequest( string $type, int $time = 3600, $code = '' ) : array {
        $db = app_db();
        $user = $this->getEmail();
        if( empty($code) ) {
            $code = uniqid() . md5( time() ); // highly unique random code
        }

        $dt = app_datetime();
        $dt->setTimeStamp($time);

        // insert a new request
        $stmt = $db->insert( self::TABLE_REQUEST, [
            'code' => $code,
            'time' => app_get_datetime($dt),
            'user' => $user,
            'type' => $type
        ]);

        // make user request
        if ( $stmt->rowCount() > 0 ) {
            return [ 'success' => true, 'code' => $code ];
        }

        return  [ 'success' => false ];
    }

    /**
     * Check if a token exists
     * @param $type is the type of request
     * @param $token is the token of request
     */
    public static function checkRequestToken( string $token ) : bool
    {
        $db = app_db();

        // insert a new request
        if ( ! $db->has( self::TABLE_REQUEST, [
            'code' => $token
        ]) )
        {
            return false;
        }

        $token_data = $db->get( self::TABLE_REQUEST, [
            'user',
            'time'
        ], [
            'code' => $token
        ]);

        $token_time = app_get_timestamp( $token_data['time'] );
        $time = time();

        if ( $time < $token_time ) {
            return true;
        }

        return false;
    }

    /**
     * Get request token type
     * @param $token is the token of request
     */
    static function getRequestTokenInfo( string $token ) : array
    {
        $token_data = app_db()->get( self::TABLE_REQUEST, [
            'user',
            'time',
            'type'
        ], [
            'code' => $token
        ]);

        if ( is_array( $token_data ) ) {
            return $token_data;
        }

        return [];
    }

    /**
     * Delete request token
     * @param $token is the token of request or user id
     */
    static function deleteRequest( string $token ) : void
    {
        $stmt = app_db()->delete( self::TABLE_REQUEST, [
            'OR' => [
                'code' => $token,
                'user' => $token
            ]
        ]);
    }
}