<?php

/**
 * @package controller
 * @classe home
 *
 */

class Upcms extends BaseController
{
	function __construct( $context )
	{
		parent::__construct( $context );

		$this->loadModel( 'Upcms' );
		// $this->header['pageTitle']  = ' - Home';
	}
	
	public function index( $vars )
	{
		
	}

	public function addUser()
	{
		$this->model->upcms->addUser( $_POST['name'], $_POST['user'], $_POST['password'] );
	}
}

?>