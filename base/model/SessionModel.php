<?php

/**
 * @package model
 * @classe home
 *
 */

class SessionModel extends BaseModel
{
	function __construct( $context )
	{
		parent::__construct( $context );
	}
	
	public function getUser( $username, $password )
	{
		$result = $this->db->select( 
			'SELECT id
			 FROM '.CMSConfig::USERS_TABLE_NAME.' 
			 WHERE '.CMSConfig::USERS_USERNAME_COLUMN.' = \''.$username.'\' AND 
			sha1( concat( '.CMSConfig::USERS_PASSWORD_COLUMN.', \''.CMSConfig::CONNECTION_HASH.'\' ) ) = \''.$password.'\'' );
		
		if( count( $result ) )
			return sha1( $password.date('Y-m-d').$result[0]['id'] );
		else
			return NULL;
	}
	
	public function loginUser( $username, $token )
	{
		$result = $this->db->select( 'SELECT id, sha1( concat( '.CMSConfig::USERS_PASSWORD_COLUMN.', \''.CMSConfig::CONNECTION_HASH.'\' ) ) AS password FROM '.CMSConfig::USERS_TABLE_NAME.' WHERE '.CMSConfig::USERS_USERNAME_COLUMN.' = \''.$username.'\'' );
		
		if( count( $result ) )
		{
			if( sha1( $result[0]['password'].date('Y-m-d').$result[0]['id'] ) == $token )
			{
				$_SESSION[CMSConfig::CONNECTION_SESSION_VAR] = $username;
				return true;
			}
			else
				return false;
		}
		else
			return false;
	}
}

?>