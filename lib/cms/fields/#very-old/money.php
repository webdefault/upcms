<?php

load_lib_file( 'cms/fields/simpletext' );

class MoneyField extends SimpletextField
{
	
	public function submit( $value )
	{
		$hasCents = strlen($value) > 3;
		
		$temp = str_replace('.', '', $value);
		$temp = str_replace(',', '', $temp);

		if($hasCents)
		{
			$temp /= 100;
		}

		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['money'] = 'MoneyField';

?>