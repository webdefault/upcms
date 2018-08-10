<?php

class SimplePage extends BaseController
{
	function __construct( $context )
	{
		parent::__construct( $context );
	}
	
	protected function loadPage( $name )
	{
		$this->loadHeaderView();// HEADER
		$this->loadContentView( $name );// CONTENT
		$this->loadFooterView();// FOOTER
	}

	protected function loadHeaderView()
	{
		$this->loadView( 'Header', $this->header ,  true );// HEADER
	}
	
	protected function loadFooterView()
	{
		$this->loadView( 'Footer', $this->footer );// FOOTER
	}
	
	protected function loadContentView( $name )
	{
		$this->loadView( $name, $this->content );// CONTENT
	}
	
}

?>