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

	function token( $vars )
	{
		$this->loadModel( 'Session' );
		
		$token = $this->model->session->getUser(
			@$_POST['username'],
			@$_POST['password'] );

		if( $token )
		{
			$this->content = array( 'status' => true, 'token' => $token );
		}
		else
		{
			$this->content = array( 'status' => false );
		}
		
		$this->loadView( 'JSON', array( 'json' => $this->content ) );
		/*
		$user = SessionLogin::login( $_POST['user'], $_POST['password'], Options::get('cms', 'password_hash') );

		if( $user == NULL )
		{
			$this->loadView( 'Login', array('error'=>'loginFail') );
		}
		else
		{
			if( isset( $_SESSION['cms-login-redirect'] ) )
				header_redirect( $_SESSION['cms-login-redirect'] );
			else
				header_redirect( '/'.Config::PATH_NAME.'admin' );
		}
		*/
	}
	
	function login()
	{
		$this->loadModel( 'Session' );
		
		if( $this->model->session->loginUser( $_POST['username'], @$_POST['token'] ) )
			$this->content = array( 'status' => true, 'logged' => true );
		else
			$this->content = array( 'status' => true, 'logged' => false );
		
		$this->loadView( 'JSON', array( 'json' => $this->content ) );
	}

	function logout()
	{
		SessionLogin::logout();

		$this->loadView( 'JSON', array( 'json' => array( 'status' => true, 'logged' => false ) ) );
	}

	function index($vars)
	{
		$this->loadView( 'JSON', array( 'json' => array( 'status' => true, 'logged' => $this->user ? true : false ) ) );
	}
}

?>
