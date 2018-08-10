<?php

/**
 * @package controller
 * @classe home
 *
 */

interface IValidator
{
	public function set( $mapName, $map, $path, $ids, $db );
	public function validate( $fieldName, $field, $value, $rule );
}

global $__CMSValidators;
$__CMSValidators = array();

function CMSValidatorClass( $name )
{
	global $__CMSValidators;
	
	if( !@$__CMSValidators[$name] )
	{
		$path = 'validators/'.$name;
		load_lib_file( 'cms/'.$path );
	}
	
	return $__CMSValidators[$name];
}

load_lib_file( 'cms/validators/not-empty' );

function CMSValidate( $validatorName, $mapName, $map, $path, $ids, $db, $fieldName, $field, $value, $rule )
{
	$validar = CMSValidatorClass( $validatorName );
	
	if( $validar )
	{
		$obj = new $validar();
		$obj->set( $mapName, $map, $path, $ids, $db );
		
		return $obj->validate( $fieldName, $field, $value, $rule );
	}
	else
	{
		throw new Exception("CMSValidatorClass not found", 1);
		return false;
	}
}

?>