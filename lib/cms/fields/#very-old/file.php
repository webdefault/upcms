<?php

load_lib_file( 'cms/fields/simpletext' );

class FileField extends SimpletextField
{
	public function validate( $value )
	{
		load_lib_file( 'cms/validators' );

		global $CMSValidators;
		$tempRequerid = true;
		$tempExtensions = true;

		$required = @$this->target->required == NULL ? false : $this->target->required;

		if( $value == NULL ) $value = '';

		if( $required == true || $value != '' )
		{
			$validator = ( @$this->target->validator != NULL ? 
				$CMSValidators[$this->target->validator] :
				$CMSValidators[$this->defaultValidator] );

			$tempRequerid = $validator( $value );
		}
		else
			$tempRequerid = true;

		if( $value != '' && @$this->field->extensions != NULL )
		{
			$validator = $CMSValidators['extensions'];
			$tempExtensions = $validator($value, $this->field->extensions);
		}

		if($tempRequerid && $tempExtensions)
			return true;
		else
			return false;
	}

	public function doSelectAndSearch( $searchValues, $quick = false )
	{
		$this->doSelect( $quick );
	}
}

global $__CMSFields;
$__CMSFields['file'] = 'FileField';

?>