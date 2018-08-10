<?php

class EmailValidator extends NotEmptyValidator
{
	public function validate( $fieldName, $field, $value, $rule )
	{
		return preg_match( '/^[^0-9][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[.][a-zA-Z]{2,4}$/', $value ) !== 0;
	}
}

global $__CMSValidators;
$__CMSValidators['email'] = 'EmailValidator';

?>