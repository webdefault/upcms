<?php

/**
 * @package controller
 * @classe error_404
 *
 */
 
class Error404 extends BaseController
{
	function __construct( $context )
	{
		parent::__construct( $context );
	}

	function index()
	{
		$this->loadView( 'Error404' ); /** VIEW **/
	}

}

?>