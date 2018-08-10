<?php

/**
 * @package controller
 * @classe home
 *
 */

// ListContent could be just List, but it's a reserved word
class UserManager extends BaseController
{
	function __construct( $context )
	{
		parent::__construct( $context );
		
		$this->loadModel( 'UserManager' );
		
		$uri = substr( $context->uri['short_uri'], 0, -40 );
		$token = substr( $context->uri['short_uri'], -40 );
		
		if( !$this->model->userManager->checkAccess( $_SERVER["REMOTE_ADDR"], $uri, $token ) )
			$this->noUserLogged( $context->uri['request_uri'] );
		// $this->loadModel( 'Edit' );
		// $this->loadModel( 'MinhasLojas' );
		// $this->loadModel( 'Util' );
		// $this->header['pageTitle']  = ' - Home';
	}

	protected function noUserLogged( $requestUri )
	{
		$this->viewVars['status'] = false;
		$this->viewVars['error'] = "PERMISSION DENIED";

		// header_redirect( '/'.Config::PATH_NAME.'admin/session' );

		$this->loadView( 'JSON', array( 'json' => $this->viewVars ) );
		exit();
	}
	
	public function index( $vars )
	{
		// $this->viewVars[''];

		// $this->viewVars['lojas'] = $this->model->minhasLojas->getList( $this->user['id'] );
		/*$this->viewVars['success'] = true;
		
		if( $this->viewVars['success'] )
			$this->loadView( 'MinhasLojas', $this->viewVars );
		else
			$this->loadView( 'ValidationError', $this->viewVars );*/
	}

	public function update( $args )
	{
		$name = base64_decode( $args[0] );
		$email = base64_decode( $args[1] );
		$password = base64_decode( $args[2] );
		$token = base64_decode( $args[3] );

		$this->loadModel( 'UserManager' );
		$result = $this->model->userManager->update( $name, $email, $password, $token );

		$this->viewVars['status'] = true;

		$this->loadView( 'JSON', array( 'json' => $this->viewVars ) );
	}
}

?>