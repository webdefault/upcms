<?php

/**
 * @package controller
 * @classe home
 *
 */
// Comentario
Application::loadController( "EditContent" );
// require_once( CONTROLLER_PATH.'EditContent.php' );

// ListContent could be just List, but it's a reserved word
class AddContent extends EditContent
{
	function __construct( $context )
	{
		parent::__construct( $context );

		$this->loadModel( 'Add' );
		// $this->header['pageTitle']  = ' - Home';
	}
	
	public function index( $vars )
	{
		$this->prepare( $vars, $mapName, $path, $ids, $fields, $map, $page );
		
		$access = $this->model->access->check( 'add', $mapName, $map, $path, $ids );
		
		foreach( $ids AS $k => $v )
		{
			if( !$v ) unset( $ids[$k] );
		}
		
		if( count( $access ) > 0 )
		{
			$this->viewVars['invalid_fields'] = $access;
			$this->viewVars['success'] = false;
		}
		else
		{
			if( @$ids[$mapName] )
			{
				$result = $this->model->edit->get( $mapName, $map, $path, $ids );
				
				$this->viewVars['values'] = $result['item'];
				$this->viewVars['request'] = $result['request'];
			}
			else
			{
				$result = $this->model->add->prepareFields( $mapName, $map, $path, $ids );
				
				if( count( $vars ) > 2 )
				{
					array_shift( $vars );
					array_shift( $vars );
					
					$values = CMS::parseSlashGet( $vars );
					
					foreach( $values AS $key => $val )
					{
						$result['values'][$key] = $val;
					}
				}
				
				$this->viewVars['values'] = $result['values'];
				$this->viewVars['request'] = $result['request'];
			}

			$this->viewVars['map_id'] = $ids;
			$this->viewVars['success'] = true;
		}

		if( $this->viewVars['success'] )
			$this->loadView( 'Add', $this->viewVars );
		else
			$this->loadView( 'ValidationError', $this->viewVars );
	}

	public function save( $vars )
	{
		$this->prepare( $vars, $mapName, $path, $ids, $fields, $map, $page );

		$result = $this->model->add->validation( $mapName, $map, $path, $ids, $_POST );
		//print_r( $validation );
		//exit;
		
		foreach( $ids AS $k => $v )
		{
			if( !$v ) unset( $ids[$k] );
		}
		
		$this->viewVars['map_id'] = $ids;
		$this->viewVars['invalid_fields'] = array();

		
		if( count( $result['validation'] ) > 0 )
		{
			$this->viewVars['invalid_fields'] = $result['validation'];
			$this->viewVars['success'] = false;
		}
		else
		{
			$result = $this->model->add->create( $mapName, $map, $path, $ids, $_POST );
			$this->viewVars['success'] = true;
			$this->viewVars['values'] = $_POST;
			$this->viewVars['values']['id'] = $result;
		}
		

		if( $this->viewVars['success'] )
			$this->loadView( 'AddResult', $this->viewVars );
		else
			$this->loadView( 'ValidationError', $this->viewVars );

		// $this->loadView( 'AddResult', $this->viewVars );
	}
}

?>