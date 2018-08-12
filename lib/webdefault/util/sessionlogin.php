<?php

class SessionLogin
{
	private static $db, $sessionVar, $userTable, $groupTable, $relId, $usernameVar, $passwordVar;

	private static $_instance;

	protected function __clone() { }

	public static function setup( $dbConnection, $sessionVar, $userTable, $groupTable = NULL, $foreignKey = NULL )
	{
		self::$userTable = $userTable;
		self::$groupTable = $groupTable;
		self::$relId = $foreignKey;
		self::$db = $dbConnection;
		self::$sessionVar = $sessionVar;
		self::$usernameVar = 'username';
		self::$passwordVar = 'password';
	}

	public static function setColumns( $username, $password )
	{
		self::$usernameVar = $username;
		self::$passwordVar = $password;
	}
	
	public static function getLoggedUser()
	{
		// error_log( self::$sessionVar );
		
		if( isset( $_SESSION[self::$sessionVar] ) )
		{
			return self::loadUser( $_SESSION[self::$sessionVar] );
		}
		else
		{
			return false;
		}
	}

	protected static function loadUser( $username )
	{
		$result = self::$db->select( 'SELECT * FROM '.self::$userTable.' AS u'.
			( self::$groupTable != NULL ? ( ' JOIN '.self::$groupTable.' AS g ON u.'.self::$relId.' = g.id' ) : '' ).
			' WHERE u.'.self::$usernameVar.' = \''.$username.'\'' );

		return ( $result != NULL && count( $result ) > 0 ) ? $result[0] : NULL;
	}
	
	protected function __construct()
	{
		
	}
	
	/**
	* Do a login.
	* @param string $username username for log in.
	* @param string $password password for log in.
	* @access public
	* @static
	* @return boolean success or not.
	*/
	public static function login( $username, $password, $passhash, $keepLogged = true )
	{
		$sql = 'SELECT * FROM '.self::$userTable.' AS u'.
			( self::$groupTable != NULL ? ( ' JOIN '.self::$groupTable.' AS g ON u.'.self::$relId.' = g.id ' ) : '' ).
			' WHERE u.'.self::$usernameVar.' = \''.$username.'\' AND u.'.self::$passwordVar.' = SHA1(\''.$password.$passhash.'\')';
		// echo $sql; exit;

		$result = self::$db->select( $sql );
		
		if( $result != NULL && count( $result ) > 0 )
		{
			$_SESSION[self::$sessionVar] = $username;

			return $result[0];
		}
		else
			return false;
	}

	public static function checkPassword( $username, $password, $passhash )
	{
		$sql = 'SELECT * FROM '.self::$userTable.' AS u'.
			( self::$groupTable != NULL ? ( ' JOIN '.self::$groupTable.' AS g ON u.'.self::$relId.' = g.id ' ) : '' ).
			' WHERE u.'.self::$usernameVar.' = \''.$username.'\' AND u.'.self::$passwordVar.' = SHA1(\''.$password.$passhash.'\')';
		
		$result = self::$db->select( $sql );

		if( $result != NULL && count( $result ) > 0 )
			return true;
		else
			return false;
	}

	/**
	* Do a login.
	* @param string $username username for log in.
	* @param string $password password for log in.
	* @access public
	* @static
	* @return boolean success or not.
	*/
	public static function loginById( $id, $keepLogged = true )
	{
		$result = self::$db->select( 'SELECT * FROM '.self::$userTable.' AS u'.
			( self::$groupTable != NULL ? ( ' JOIN '.self::$groupTable.' AS g ON u.'.self::$relId.' = g.id ' ) : '' ).
			' WHERE u.id = \''.$id.'\'' );
		
		if( $result != NULL && count( $result ) > 0 )
		{
			if( $keepLogged )
				$_SESSION[self::$sessionVar] = $result[0][self::$usernameVar];

			return $result[0];
		}
		else
			return NULL;
	}

	/**
	* Do a passowrd change.
	* @param string $username username for log in.
	* @param string $password password for log in.
	* @access public
	* @static
	* @return boolean success or not.
	*/
	public static function changePassword( $username, $oldPassword, $newPassword, $passhash )
	{
		$result = self::$db->select( 'SELECT * FROM '.self::$userTable.' AS u'.
			( self::$groupTable != NULL ? ( ' JOIN '.self::$groupTable.' AS g ON u.'.self::$relId.' = g.id ' ) : '' ).
			' WHERE u.'.self::$usernameVar.' = \''.$username.'\' AND u.'.self::$passwordVar.' = SHA1(\''.$oldPassword.$passhash.'\')' );
		
		if( $result != NULL && count( $result ) > 0 )
		{
			self::$db->update( self::$userTable, array( self::$passwordVar => sha1( $newPassword.$passhash ) ), array( 'id' => $result[0]['id'] ) );
			return true;
		}
		else
			return false;
	}
	
	/**
	* Do a logout.
	* @access public
	* @static
	* @return void.
	*/
	public static function logout()
	{
		unset( $_SESSION[self::$sessionVar] );
	}
}

?>