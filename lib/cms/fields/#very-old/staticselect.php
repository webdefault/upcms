<?php

class StaticSelectField extends StaticText
{
	public function doSelectAndSearch( $searchValues, $quick = false )
	{
		$this->doSelect( $quick );
	}
}

global $__CMSFields;
$__CMSFields['staticselect'] = 'StaticSelectField';

?>