<?php

/**
 * @package model
 * @classe home
 *
 */

load_lib_file( 'cms/parse_bracket_instructions' );

class DeleteModel extends BaseModel
{
	function __construct( $context )
	{
		parent::__construct( $context );
	}
	
	function deleteItems( $map, $mapName, $path, $table, $ids )
	{
		$list = join( ',', $ids );
		CMS::addGlobalValue( 'removes', array( 'ids' => $list ) );
		
		if( @$map->before || @$map->after )
			CMSProcedures::setup( $mapName, $map, $path, array(), $this->db, array() );
		
		if( @$map->before && @$map->before->delete )
		{
			$procedure = CMS::procedures( $map->before->delete );
			CMSProcedures::apply( $procedure );
		}
		
		foreach( $ids AS $key => $id )
		{
			$ids[$key] = array( 'id' => $id );
		}
		
		if( @$map->after && @$map->after->delete )
		{
			$procedure = CMS::procedures( $map->after->delete );
			CMSProcedures::apply( $procedure );
		}
		
		return $this->db->delete( $table, $ids );
	}
	
	function updateItems( $map, $mapName, $path, $table, $fields, $ids )
	{
		$columns = array();
		
		$list = join( ',', $ids );
		CMS::addGlobalValue( 'removes', array( 'ids' => $list ) );
		
		if( @$map->before || @$map->after )
			CMSProcedures::setup( $mapName, $map, $path, array(), $this->db, array() );
		
		if( @$map->before && @$map->before->delete )
		{
			$procedure = CMS::procedures( $map->before->delete );
			CMSProcedures::apply( $procedure );
		}
		
		foreach( $fields AS $field => $value )
		{
			$columns[$field] = parse_bracket_instructions( @$value, array() );
		}
		
		$result = array();
		foreach( $ids AS $key => $id )
		{
			$where = array( 'id' => $id );
			$this->db->update( $table, $columns, $where );
		}
		
		if( @$map->after && @$map->after->delete )
		{
			$procedure = CMS::procedures( $map->after->delete );
			CMSProcedures::apply( $procedure );
		}
		
		return $result;
	}
}
