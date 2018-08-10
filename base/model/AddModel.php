<?php

/**
 * @package model
 * @classe home
 *
 */

load_lib_file( 'cms/fields' );
load_lib_file( 'cms/request' );

class AddModel extends EditModel
{
	function __construct( $context )
	{
		parent::__construct( $context );
	}
	
	public function create( $mapName, $map, $path, $ids, $values )
	{
		$request = Request::createRequestFromTarget( $mapName, $map, $path, $ids, $this->db );
		$mql = $request->matrixQueryForInsert( $values );
		// MatrixQuery::printQuery( $mql );
		// exit;
		
		if( @$map->before || @$map->after )
			CMSProcedures::setup( $mapName, $map, $path, $ids, $this->db, $values );
		
		// before save
		if( @$map->before && @$map->before->create )
		{
			if( is_array( $map->before->create ) )
			{
				foreach( $map->before->create AS $item )
				{
					$procedure = CMS::procedures( $item );
					CMSProcedures::apply( $procedure );
				}
			}
			else
			{
				$procedure = CMS::procedures( $map->before->create );
				CMSProcedures::apply( $procedure );
			}
		}
		
		// MatrixQuery::printQuery( $mql ); exit;
		$ids = MatrixQuery::insert( $mql, $this->db );
		if( count( $ids ) == 0 && $this->db->error() && Config::ENV == 'development' )
		{
			$result = $this->db->error();
			unset( $mql['select'][0] );
			CMS::exitWithMessage( 'error', $result, array( 'mql' => $mql, 'sql' => MatrixQuery::select( $mql ) ) );
		}
		
		// after save
		if( count( $ids ) > 0 && @$map->after && @$map->after->create )
		{
			CMSProcedures::setup( $mapName, $map, $path, $ids, $this->db, $values );
			
			if( is_array( $map->after->create ) )
			{
				foreach( $map->after->create AS $item )
				{
					$procedure = CMS::procedures( $item );
					CMSProcedures::apply( $procedure );
				}
			}
			else
			{
				$procedure = CMS::procedures( $map->after->create );
				CMSProcedures::apply( $procedure );
			}
		}
		
		if( !@$ids[$mql['target']] ) 
			CMS::exitWithMessage( 'error', 'Erro interno do servidor.' );
		else
			return $ids[$mql['target']];
	}
}
