<?php

load_lib_file( 'cms/fields/simpletext' );

class EmailField extends SimpletextField
{
	function __construct()
	{
		parent::__construct();

		$this->defaultValidator = 'email';
	}
}

global $__CMSFields;
$__CMSFields['email'] = 'EmailField';

?>