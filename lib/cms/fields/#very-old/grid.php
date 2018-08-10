<?php

load_lib_file( 'cms/fields/simpletext' );

class GridField extends SimpletextField implements ITable
{
	public function select( $quick = false )
	{
		return NULL;
	}

	public function insert( $value )
	{
		return NULL;
	}

	public function update( $value )
	{
		return NULL;
	}

	public function doSelect( $quick = false )
	{
		
	}

	public function doSelectAndSearch( $searchValues, $quick = false )
	{
		
	}

	public function doUpdate( $value )
	{
		
	}

	public function prepareSelect()
	{

	}

	public function prepareInsert()
	{

	}

	public function prepareUpdate()
	{

	}
}

global $__CMSFields;
$__CMSFields['grid'] = 'GridField';

?>