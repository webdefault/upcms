<?php

load_lib_file( 'cms/fields/simpletext' );

class RichtextField extends SimpletextField
{
	
}

global $__CMSFields;
$__CMSFields['richtext'] = 'RichtextField';

?>