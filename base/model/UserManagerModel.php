<?php

/**
 * @package model
 * @classe home
 *
 */

class UserManagerModel extends BaseModel
{
	public function checkAccess( $addr, $uri, $token )
	{
		return ( !defined( 'CMSConfig::FRONT_IP' ) || CMSConfig::FRONT_IP == $addr ) && sha1( $uri.CMSConfig::CONNECTION_HASH ) == $token;
	}
	
	public function update( $name, $email, $password, $token )
	{
		$localToken = sha1(microtime(true).mt_rand(10000,90000));

		$result = $this->db->select( 'SELECT * FROM users WHERE email = ?', array( $email ) );
		
		if( count( $result ) > 0 )
		{
			$user = $result[0];
			
			$this->db->update(
				'users',
				array(
					'name' => $name,
					'password' => $password
				),
				array( 'id' => $user['id'] ) );
		}
	}
}

?>