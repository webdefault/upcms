<?php

load_lib_file( 'cms/parse_bracket_instructions' );

class FilesizeLessThanValidator extends NotEmptyValidator
{
	protected static $FILES = array();
	
	protected function loadFile( $fieldName, $field, $value )
	{
		if( $value == "0," || $value == "1," )
			return "";
		else
		{
			$path = $field->url( $value );
			
			$file = file_get_contents( $path );
			return self::$FILES[$fieldName] = $file;
		}
	}
	
	public function validate( $fieldName, $field, $value, $rule )
	{
		$file = @self::$FILES[$fieldName];
		if( !$file ) $file = $this->loadFile( $fieldName, $field, $value );
		
		// $image = imagecreatefromstring( $file );
		if( $file && strlen($file) >= $rule->size )
			return false;
		else
			return true;
	}
}

global $__CMSValidators;
$__CMSValidators['filesize-less-than'] = 'FilesizeLessThanValidator';

?>