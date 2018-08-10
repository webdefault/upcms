<?php

/**
 * @package model
 * @classe home
 *
 */

load_lib_file( 'cms/fields' );
load_lib_file( 'cms/request' );

// It need to be updated like other models
class CMSModel extends BaseModel
{
	public function applyMapIds( $request, $mapName, $map, $path, $ids )
	{
		// Apply mapIds on request
		foreach( $mapIds AS $key => $value )
		{
			$field = new CustomField( $key, 'id', $value );
			$field->set( $request, $mapName, $map, $path, $ids, NULL );
		}
	}
}

?>