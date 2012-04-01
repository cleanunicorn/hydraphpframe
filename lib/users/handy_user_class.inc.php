<?php

/*

	You can use this SQL Statement to create the table

	CREATE TABLE `Users` (`User_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `Username` VARCHAR(20) NOT NULL, `Password` VARCHAR(32) NOT NULL, `Register_date` DATETIME) ENGINE = MyISAM;

*/

class user_class
{
    /*
            Class can be extended and the column names can be overriden in these variables

            Example:
            $user_instance = new user_class();
            $user_instance->TABLE_NAME = 'DIFFERENT_TABLE_NAME';
            print_r( $user_instance->get_database_row() );

            This means that you can use this class on a different layout of a table.


    */
    public $ERROR = '';
    public $TABLE_NAME = 'Users';
    public $TABLE_COLUMNS = array(
								'username' 	=> 'Username',
								'password' 	=> 'Password',
								'id'		=> 'User_id',

                                                                'registerdate'  => 'Register_date',
								);
    public $ID_COLUMN = 'User_id';

    public $SESSION_ID_USER = 'id_user';

    /*
    	You need to set the USER_ID value from the database to know which row to pull out.

    	Example:
		$user_instance = new user_class();
		$user_instance->USER_ID = 1234 ;
		print_r( $user_instance->get_database_row() );


     *  Unless you retrieve the row for the logged user.
     *
     *  Example:
     *
     *  $user_instance = new user_class();
     *  if ($user_instance->login() )
     *  {
     *      $user_instance->get_database_row();
     *  }
     *
     *

    */
    public $USER_ID = NULL;

    protected $IS_LOGGED = FALSE;
    protected $DB_LINK = NULL;
    public $ENCRYPTION = 'md5';
    protected $ROW_FROM_DATABASE = NULL;

    /*
    	Cookie private key.
    */
    public $COOKIE_PRIVATE_KEY = '';
    public $COOKIE_ENCRYPTION = 'md5';

    function __construct( $dblink )
    {
        if ( $dblink == NULL || !$dblink )
        {
            echo 'You need to specify a database valid link';
            return NULL;
        }

        $this->DB_LINK = $dblink;
        
        $this->is_logged();
    }

    function __destruct()
    {

    }

    public function login( $User_name, $Password, $Remember_with_cookie = false, $Load_info = true )
    {
        if ( empty( $User_name ) )
        {
            $this->ERROR = 'USERNAME_IS_EMPTY';
            return false;
        }
        if ( empty( $Password ) )
        {
            $this->ERROR = 'PASSWORD_IS_EMPTY';
            return false;
        }

        switch ( strtolower( $this->ENCRYPTION ) )
        {
            case 'md5':
                $hashed_password = md5( $Password );
                break;
            case 'sha1':
                $hashed_password = sha1( $Password );
                break;
        }

        $user_info_sql = sprintf( "SELECT * FROM `%s` WHERE `%s`=? ", $this->TABLE_NAME, $this->TABLE_COLUMNS['username'] );
        $user_info_data = $this->DB_LINK->GetRow( $user_info_sql, array( $User_name ) );
		
        if ( $user_info_data )
        {
        	$user_password = $user_info_data[$this->TABLE_COLUMNS['password']];
			
        	if ( $hashed_password == $user_password )
        	{
	        	$this->USER_ID = $user_info_data[$this->TABLE_COLUMNS['id']];
	        	$this->IS_LOGGED = true;

	        	if ( !headers_sent() )
	        	{
	        		$_SESSION[$this->SESSION_ID_USER] = $this->USER_ID;

	        		if ( $Remember_with_cookie )
	        		{
		        		$cookie_user_name = $this->get_user_name();
		        		$cookie_expire = time() + 86400 * 14; //14 days
		        		$cookie_data = $cookie_user_name . $cookie_expire . $this->COOKIE_PRIVATE_KEY;
		        		$cookie_private_key = hash_hmac( $this->COOKIE_ENCRYPTION , $cookie_data, $this->COOKIE_PRIVATE_KEY );
		        		$cookie_hash = hash_hmac( $this->COOKIE_ENCRYPTION, $cookie_data, $cookie_private_key );

		        		setcookie( 'username', $cookie_user_name, $cookie_expire, '/' );
		        		setcookie( 'expire', $cookie_expire, $cookie_expire, '/' );
		        		setcookie( 'hash', $cookie_hash, $cookie_expire, '/' );
	        		}

	        	}

		        if ( $Load_info )
		        {
		        	$this->ROW_FROM_DATABASE = array();
		        	foreach ( $user_info_data as $column=>$data )
		        	{
                                    $this->ROW_FROM_DATABASE[$column] = $data;
		        	}
		        }
		        $this->ERROR = '';
		        return true;
        	}
        	else
        	{
                    $this->ERROR = 'WRONG_PASSWORD';
                    return false;
        	}
        }
        else
        {
            $this->ERROR = 'WRONG_USERNAME';
            return false;
        }
    }

    function get_database_row( $Refresh_data = false, $User_id = 0 )
    {
        $retrieve_for_id = 0;
        if ( $User_id )
        {
            $retrieve_for_id = $User_id;
        }
        elseif ( $this->USER_ID )
        {
            $retrieve_for_id = $this->USER_ID;
        }
    	if ( $retrieve_for_id )
    	{
    		if ( ( $this->ROW_FROM_DATABASE == NULL ) || $Refresh_data || $User_id )
    		{
                    $database_row_sql = sprintf( "SELECT * FROM `%s` WHERE `%s`=?", $this->TABLE_NAME, $this->ID_COLUMN );
                    $database_row = $this->DB_LINK->GetRow( $database_row_sql, array( $retrieve_for_id ) );

                    //If there is no error fetching data, save it.
                    if ( $database_row && ( $retrieve_for_id == $this->USER_ID ) )
                    {
                            $this->ROW_FROM_DATABASE = $database_row;
                    }

                    //Return the result whichever it is
                    return $database_row;
    		}
    		elseif ( $this->ROW_FROM_DATABASE )
    		{
                    return $this->ROW_FROM_DATABASE;
    		}
    	}
    }

    function is_logged()
    {
    	if ( isset( $_SESSION[$this->SESSION_ID_USER] ) )
    	{
    		$this->USER_ID = $_SESSION[$this->SESSION_ID_USER];
    		$this->IS_LOGGED = true;
    		return true;
    	}
    	elseif ( $this->cookie_is_valid() )
    	{
    		$_SESSION[$this->SESSION_ID_USER] = $this->get_user_id_from_user_name( $_COOKIE['username'] );
    		$this->USER_ID = $_SESSION[$this->SESSION_ID_USER];
    		$this->IS_LOGGED = true;
    		return true;
    	}
    	else
    	{
    		return false;
    	}
    }

    public function new_user( $Username, $Password )
    {
    	$user_exists_info = $this->user_exists( $Username );

    	$user = $Username;

    	if ( $user_exists_info == false )
    	{
    		switch ( $this->ENCRYPTION )
    		{
    			case 'md5':
    				$pass_encrypted = md5( $Password );
    				break;
    			case 'sha1':
    				$pass_encrypted = sha1( $Password );
    				break;
    		}

    		$add_user_sql = sprintf( "INSERT INTO `%s` ( `%s`, `%s`, `%s` ) VALUES ( ?, ?, ? )", $this->TABLE_NAME, $this->TABLE_COLUMNS['username'], $this->TABLE_COLUMNS['password'], $this->TABLE_COLUMNS['registerdate'] );
    		$add_user = $this->DB_LINK->Execute( $add_user_sql, array( $user, $pass_encrypted, $_SESSION['datetime_gmt'] ) );
    		if ( $this->DB_LINK->Affected_Rows() == 1 )
    		{
    			return $this->DB_LINK->Insert_ID();
    		}
    	}
    	else
    	{
    		return 'USER_EXISTS';
    	}
    }

    function user_exists( $Username )
    {	
    	$user_exists_sql = sprintf( "SELECT `%s`.`%s` FROM `%s` WHERE `%s`=?", $this->TABLE_NAME, $this->ID_COLUMN, $this->TABLE_NAME, $this->TABLE_COLUMNS['username'] );
    	$user_exists_info = $this->DB_LINK->Execute( $user_exists_sql, array( $Username ) );
		
    	if ( $user_exists_info->RecordCount() >= 1 )
    	{
            return true;
    	}
    	else
    	{
            return false;
    	}
    }

    function get_user_name()
    {
    	if ( isset( $this->ROW_FROM_DATABASE[ $this->TABLE_COLUMNS['username'] ] ) )
    	{
            return $this->ROW_FROM_DATABASE[ $this->TABLE_COLUMNS['username'] ];
    	}
    	else
    	{
            $this->get_database_row();
            return $this->ROW_FROM_DATABASE[ $this->TABLE_COLUMNS['username'] ];
    	}
    }

    function get_user_id_from_user_name( $User_name )
    {
    	if ( empty( $User_name ) )
    	{
    		return false;
    	}

    	$get_user_id_sql = sprintf( "SELECT `%s`.`%s` FROM `%s` WHERE `%s`=?", $this->TABLE_NAME, $this->ID_COLUMN, $this->TABLE_NAME, $this->TABLE_COLUMNS['username'] );
    	$get_user_id_result = $this->DB_LINK->Execute( $get_user_id_sql, array( $User_name ) );
		
    	if ( $get_user_id_result->RecordCount() == 1 )
    	{
    		return $get_user_id_result->fields[$this->ID_COLUMN];
    	}
    }

    function logout()
    {
    	if ( !headers_sent() )
    	{
            unset( $_SESSION[$this->SESSION_ID_USER] );
            setcookie( 'username', null, 0, '/' );
            setcookie( 'expire', null, 0, '/' );
            setcookie( 'hash', null, 0, '/' );
            setcookie( 'PHPSESSID', null, 0, '/' );
            session_destroy();
            return 'OK';
    	}
        else
        {
            my_DBG( 'Cannot logout user, headers already sent', 1 );
        }
    }

    function cookie_is_valid()
    {
        if ( isset( $_COOKIE['username'] ) && isset( $_COOKIE['expire'] ) && isset( $_COOKIE['hash'] ) )
        {
    	    $username = @$_COOKIE['username'];
    	    $expire = @$_COOKIE['expire'];
    	    $hash = @$_COOKIE['hash'];

    	    $data = $username . $expire . $this->COOKIE_PRIVATE_KEY;

    	    $private_key = hash_hmac( $this->COOKIE_ENCRYPTION, $data, $this->COOKIE_PRIVATE_KEY );
    	    $computed_hash = hash_hmac( $this->COOKIE_ENCRYPTION, $data, $private_key );

    	    if ( $computed_hash == $hash )
    	    {
    		    return true;
    	    }
        }
    	return false;
    }
    
}

?>