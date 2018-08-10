<?php

/**
 * @package model
 * @classe home
 *
 */

load_lib_file( 'cms/fields' );
load_lib_file( 'cms/request' );
load_lib_file( 'webdefault/db/mysql/matrixquery' );

class EditModel extends BaseModel
{
	function __construct( $context )
	{
		parent::__construct( $context );
	}

	public function prepareFields( $mapName, $map, $path, $ids )
	{
		$request = Request::createRequestFromTarget( $mapName, $map, $path, $ids, $this->db );
		return array( 'values' => array(), 'request' => $request );
	}

	function get( $mapName, $map, $path, $ids )
	{
		$request = Request::createRequestFromTarget( $mapName, $map, $path, $ids, $this->db );
		$mql = $request->matrixQueryForSelect();
		$mql['slice'] = array( 0, 1 );
		
		// print_r( $mql );exit;
		// MatrixQuery::printQuery( $mql ); exit;
		// echo MatrixQuery::select( $mql ); exit;
		
		$list = MatrixQuery::select( $mql, $this->db );
		// print_r( MatrixQuery::select( $mql ) );
		if( count( $list ) == 0 && $this->db->error() && Config::ENV == 'development' )
		{
			$result = $this->db->error();
			CMS::exitWithMessage( 'error', $result, array( 'mql' => $mql, 'sql' => MatrixQuery::select( $mql ) ) );
		}
		
		$result = array( 
			'item' => $list[0],
			'request' => $request
			);
		
		return $result;
	}

	function update( $mapName, $map, $path, $ids, $values, $columns = NULL )
	{
		$request = Request::createRequestFromTarget( $mapName, $map, $path, $ids, $this->db );
		$mql = $request->matrixQueryForUpdate( $values, $columns );
		// MatrixQuery::printQuery( $mql );exit;
		
		if( @$map->before || @$map->after )
			CMSProcedures::setup( $mapName, $map, $path, $ids, $this->db, $values );
		
		// before save
		if( @$map->before && @$map->before->save )
		{
			if( is_array( $map->before->save ) )
			{
				foreach( $map->before->save AS $item )
				{
					$procedure = CMS::procedures( $item );
					CMSProcedures::apply( $procedure );
				}
			}
			else
			{
				$procedure = CMS::procedures( $map->before->save );
				CMSProcedures::apply( $procedure );
			}
		}

		$ids = MatrixQuery::update( $mql, $this->db );
		
		// after save
		if( @$map->after && @$map->after->save )
		{
			if( is_array( $map->after->save ) )
			{
				foreach( $map->after->save AS $item )
				{
					$procedure = CMS::procedures( $item );
					CMSProcedures::apply( $procedure );
				}
			}
			else
			{
				$procedure = CMS::procedures( $map->after->save );
				CMSProcedures::apply( $procedure );
			}
		}
		
		return array( 
			'ids' => $ids,
			'request' => $request
			);
	}
	
	public function validation( $mapName, $map, $path, $ids, $values, $currentValues = array(), $fullValidation = true )
	{
		load_lib_file( 'cms/validators' );
		// CMSValidation::setup( $mapName, $map, $path, $ids, $this->db );

		$request = Request::createRequestFromTarget( $mapName, $map, $path, $ids, $this->db );

		$validation = array();
		$columns = $request->columnsName();
		foreach( $columns AS $kcol )
		{
			$result = $request->column( $kcol )['field']->validate( $currentValues, $values, $fullValidation );
			
			if( $result != NULL && $result ) 
				$validation[$kcol] = $result;
		}
		
		$rsearch = $request->search;
		if( @$search )
		{
			$columns = $rsearch->columnsName();
			// print_r( $values );
			foreach( $columns AS $kcol )
			{
				$result = $rsearch->column( $kcol )['field']->validate( $currentValues, $values, $fullValidation );
				
				if( $result != NULL && $result ) 
					$validation[$kcol] = $result;
			}
		}
		
		if( count( $validation ) == 0 )
		{
			if( @$map->validation )
			{
				foreach( $map->validation->rules AS $key => $rule )
				{
					if( $rule->procedure )
					{
						CMSProcedures::setup( $mapName, $map, $path, $ids, $this->db, $values );
						$result = CMSProcedures::apply( CMS::procedures( $key ) );
						// print_r( $result );
					}
					else
						$result = CMSValidate( $key, $this->mapName, $this->map, $this->path, $this->ids, $this->db, $this->fieldName, $this, $value, $rule );
						// CMSValidation::validate( $key, $mapName, $currentValues, $rule );

					if( !$result )
					{
						$validation[] = array(
							'icon' => @$rule->icon,
							'class' => @$rule->class,
							'message' => $rule->message );
					}
				}
			}
		}
		
		return array( 'request' => $request, 'validation' => $validation );
	}
}

?>