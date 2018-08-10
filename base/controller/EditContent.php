<?php

/**
 * @package controller
 * @classe home
 *
 */

// ListContent could be just List, but it's a reserved word
class EditContent extends CMS
{
	function __construct( $context )
	{
		parent::__construct( $context );

		$this->loadModel( 'Edit' );
		$this->loadModel( 'Access' );
		$this->loadModel( 'Util' );
	}
	
	public function prepare( $vars, &$mapName, &$path, &$ids, &$fields, &$map, &$page )
	{
		// $vars = str_replace( '-', '_', $vars );
		$path = array_shift( $vars );
		$path = str_replace( '-', '_', $path );
		
		$this->viewVars['current_table'] = $path = explode( ',', $path );
		$mapName = array_shift( $path );
		$this->mapName = $mapName;

		$this->viewVars['raw_map_id'] = array_shift( $vars );
		$ids = $this->parseMapId( explode( '&', $this->viewVars['raw_map_id'] ), $mapName );
		$this->viewVars['mapName'] = $mapName;
		$this->viewVars['layout'] = $page = $this->config['layout'];

		if( count( $path ) > 0 )
		{
			$field = end( $path );
			$fields = $page->subpages->{$field}->list->fields;
			
			$this->viewVars['map'] = $map = $this->config['map']->get($mapName);
			$this->viewVars['page'] = $page = $this->config['pages']->get($mapName);
			
			$this->viewVars['submap'] = &$map->fields->{$field};
			$this->viewVars['subpage'] = $page->subpages->{$field};
		}
		else
		{
			$this->viewVars['map'] = $map = $this->config['map']->get($mapName);
			$this->viewVars['page'] = $page = $this->config['pages']->get($mapName);
		}
	}

	public function index( $vars )
	{
		$this->prepare( $vars, $mapName, $path, $ids, $fields, $map, $page );

		$access = $this->model->access->check( 'get', $mapName, $map, $path, $ids );

		if( count( $access ) > 0 )
		{
			$this->viewVars['invalid_fields'] = $access;
			$this->viewVars['success'] = false;
		}
		else
		{
			$result = $this->model->edit->get( $mapName, $map, $path, $ids );
			CMS::addGlobalValue( 'POST', $result['item'] );

			$this->viewVars['values'] = $result['item'];
			$this->viewVars['request'] = $result['request'];
			$this->viewVars['map_id'] = $ids;
			$this->viewVars['success'] = true;
		}
		
		if( $this->viewVars['success'] )
			$this->loadView( 'Edit', $this->viewVars );
		else
			$this->loadView( 'ValidationError', $this->viewVars );
	}

	public function validate( $vars )
	{
		$this->prepare( $vars, $mapName, $path, $ids, $fields, $map, $page );
		
		CMS::addGlobalValue( 'POST', json_decode( $_POST['__form__'], true ) );
		unset( $_POST['__form__'] );
		
		$result = $this->model->edit->validation( $mapName, $map, $path, $ids, $_POST, array(), false );
		$updates = array();
		
		$temp = array_keys( $_POST );
		$request = $result['request'];
		foreach( $temp AS $name )
		{
			$column = $request->column( $name );
			if( $column )
			{
				$list = $column['field']->validationUpdates();
				
				$updates[] = $name;
				$updates = array_merge( $updates, $list );
			}
		}
		
		$rsearch = @$request->search;
		if( $rsearch )
		{
			foreach( $temp AS $name )
			{
				$column = $rsearch->column( $name );
				if( $column )
				{
					$list = $column['field']->validationUpdates();
					
					$updates[] = $name;
					$updates = array_merge( $updates, $list );
				}
			}
		}
		
		$this->viewVars['updates'] = $updates;
		$this->viewVars['validation'] = $result['validation'];
		$this->viewVars['request'] = $result['request'];
		
		$this->loadView( 'EditUpdate', $this->viewVars );
	}
	
	// That is not an db update. It updates the fields, just like in 
	// validation, except for validation
	public function updateForm( $vars )
	{
		$this->prepare( $vars, $mapName, $path, $ids, $fields, $map, $page );
		
		$POST = json_decode( $_POST['__form__'], true );
		unset( $_POST['__form__'] );
		$POST = array_merge( $POST, $_POST );
		
		CMS::addGlobalValue( 'POST', $POST );
		
		$result = $this->model->edit->validation( $mapName, $map, $path, $ids, $_POST, array(), false );
		$updates = array();
		
		$temp = array_keys( $_POST );
		$request = $result['request'];
		
		foreach( $temp AS $name )
		{
			$column = $request->column( $name );
			if( $column )
			{
				$list = $column['field']->validationUpdates();
				
				$updates[] = $name;
				$updates = array_merge( $updates, $list );
			}
		}
		
		$this->viewVars['updates'] = $updates;
		// $this->viewVars['validation'] = $result['validation'];
		$this->viewVars['request'] = $result['request'];
		
		$this->loadView( 'EditUpdate', $this->viewVars );
	}
	
	// public function 
	public function save( $vars )
	{
		$this->prepare( $vars, $mapName, $path, $ids, $fields, $map, $page );

		$result = $this->model->edit->validation( $mapName, $map, $path, $ids, $_POST );
		
		$this->viewVars['fastEdit'] = false;

		$this->viewVars['map_id'] = $ids;
		$this->viewVars['values'] = $_POST;
		$this->viewVars['invalid_fields'] = array();
		
		if( count( $result['validation'] ) > 0 )
		{
			$this->viewVars['invalid_fields'] = $result['validation'];
			$this->viewVars['success'] = false;
		}
		else
		{
			$result = $this->model->edit->get( $mapName, $map, $path, $ids );
			CMS::addGlobalValue( 'CURRENT_VALUES', $result['item'] );
			
			$this->model->edit->update( $mapName, $map, $path, $ids, $_POST );
			$this->viewVars['success'] = true;
		}
		
		if( $this->viewVars['success'] )
			$this->loadView( 'EditResult', $this->viewVars );
		else
		{
			$this->viewVars['updates'] = true;
			$this->viewVars['validation'] = $result['validation'];
			$this->viewVars['request'] = $result['request'];
			$this->viewVars['error'] = true;
			
			$this->loadView( 'EditUpdate', $this->viewVars );
		}
	}
	
	public function fastSave( $vars )
	{
		$this->prepare( $vars, $mapName, $path, $ids, $fields, $map, $page );
		array_shift( $vars );
		array_shift( $vars );
		
		$values = $this->parseSlashGet( $vars, array() );
		foreach( $values AS $field => $v )
		{
			if( !in_array( $field, $page->{'fast-edit'}->fields ) ) unset( $values[$field] );
		}
		
		$result = $this->model->edit->validation( $mapName, $map, $path, $ids, $values, array(), false );
		
		$this->viewVars['fastEdit'] = true;

		$this->viewVars['map_id'] = $ids;
		$this->viewVars['values'] = $values;
		$this->viewVars['invalid_fields'] = array();
		
		if( count( $result['validation'] ) > 0 )
		{
			$this->viewVars['invalid_fields'] = $result['validation'];
			$this->viewVars['success'] = false;
		}
		else
		{
			$result = $this->model->edit->get( $mapName, $map, $path, $ids );
			CMS::addGlobalValue( 'CURRENT_VALUES', $result['item'] );
			
			$columns = array_keys( $values );
			//print_r( $values );exit;
			$rupdate = $this->model->edit->update( $mapName, $map, $path, $ids, $values, $columns );
			$this->viewVars['request'] = $rupdate['request'];
			$this->viewVars['success'] = true;
			
			$result = $this->model->edit->get( $mapName, $map, $path, $ids );
			$this->viewVars['values'] = $result['item'];
		}
		
		if( $this->viewVars['success'] )
			$this->loadView( 'EditResult', $this->viewVars );
		else
		{
			$this->viewVars['updates'] = true;
			$this->viewVars['validation'] = $result['validation'];
			$this->viewVars['request'] = $result['request'];
			$this->viewVars['error'] = true;
			
			$this->loadView( 'EditUpdate', $this->viewVars );
		}
	}
}

?>