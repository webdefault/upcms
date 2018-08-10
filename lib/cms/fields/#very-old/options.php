<?php

load_lib_file( 'cms/fields/simpletext' );

class OptionsField extends SimpletextField
{
	public function submit( $value )
	{
		return implode(';', $value);
	}
	
}

global $__CMSFields;
$__CMSFields['options'] = 'OptionsField';

?>