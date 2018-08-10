<?php

/**
 * @package model
 * @classe home
 *
 */

class UpcmsModel extends BaseModel
{
	public function addUser( $name, $email, $localToken )
	{
		$token = sha1(microtime(true).mt_rand(10000,90000));
		$result = $this->db->insert( 'users',
			array( 
				array(
					'name' => $name,
					'email' => $email,
					'password' => $localToken )
				) );
		
		// TODO: GERAR TOKEN
	}
}

?>