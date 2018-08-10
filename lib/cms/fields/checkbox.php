<?php

load_lib_file( 'cms/fields/onoff' );

class CheckboxField extends OnoffField
{
	public function editView( $values )
	{
		$temp = parent::editView( $values );
		
		if( !$temp->static ) $temp->type = 'checkbox';
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['checkbox'] = 'CheckboxField';

?>