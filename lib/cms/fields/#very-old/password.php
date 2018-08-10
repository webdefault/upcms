<?php

load_lib_file( 'cms/fields/simpletext' );

class PasswordField extends SimpletextField
{
	public function doSelectAndSearch( $searchValues, $quick = false )
	{
		$this->doSelect( $quick );
	}

	public function validate( $value )
	{
		if( strlen($value[0]) > 0 )
		{
			$end = end( $fieldPath );
			if(@$this->currentValues[$end] != sha1( $value[0].Options::get( 'cms', 'password_hash' ) ) )
			{
				return false;
			}
			else if( $value[1] !=  $value[2])
			{
				return false;
			}
		}

		return true;
	}

	public function submit( $value )
	{
		return sha1( $value[2].Options::get( 'cms', 'password_hash' ) );
	}
}

global $__CMSFields;
$__CMSFields['password'] = 'PasswordField';

?>