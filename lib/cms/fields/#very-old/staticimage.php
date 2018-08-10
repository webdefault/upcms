<?php

load_lib_file( 'cms/fields/simpletext' );

class StaticImageField extends StaticText
{
	public function doSelectAndSearch( $searchValues, $quick = false )
	{
		$this->doSelect( $quick );
	}
}

global $__CMSFields;
$__CMSFields['staticimage'] = 'StaticImageField';

?>