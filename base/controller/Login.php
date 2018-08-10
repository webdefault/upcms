<?php

/**
 * @package controller
 * @classe home
 *
 */

class Session extends CMS
{
	function __construct( $context )
	{
		parent:: __construct( $context );
		// $this->header['pageTitle']  = ' - Home';
	}

	protected function noUserLogged( $requestUri )
	{
		
	}

	function login( $vars )
	{
		$user = SessionLogin::login( $_POST['user'], $_POST['password'], Options::get('cms', 'password_hash') );

		if( $user == NULL )
		{
			$this->loadView( 'Login', array( 'error'=>'loginFail' ) );
		}
		else
		{
			if( isset( $_SESSION['cms-login-redirect'] ) )
				header_redirect( $_SESSION['cms-login-redirect'] );
			else
				header_redirect( '/'.Config::PATH_NAME );
		}
	}

	function logout()
	{
		SessionLogin::logout();

		header_redirect( '/'.Config::PATH_NAME );
	}

	function index($vars)
	{
		if( $this->user != NULL )
			header_redirect( '/'.Config::PATH_NAME );
		else
			$this->loadView( 'Login' );
	}
}

?>
