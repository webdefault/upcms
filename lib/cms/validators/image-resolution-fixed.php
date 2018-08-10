<?php

load_lib_file( 'cms/validators/filesize-less-than' );

class ImageResolutionFixedValidator extends FilesizeLessThanValidator
{
	protected static $FILES = array();
	
	protected function loadFile( $fieldName, $field, $value )
	{
		if( $value == "0," || $value == "1," )
			return NULL;
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
		
		if( $file )
		{
			$image = imagecreatefromstring( $file );
			if( imagesx( $image ) == $rule->width && imagesy( $image ) == $rule->height )
				return true;
			else
				return false;
		}
		else
			return false;
		// 
	}
}

global $__CMSValidators;
$__CMSValidators['image-resolution-fixed'] = 'ImageResolutionFixedValidator';

?>