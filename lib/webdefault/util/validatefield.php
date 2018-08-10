<?php

function validateField( $target )
{
	foreach( $target AS $item )
	{
		foreach( $item['rules'] AS $rule )
		{
			if( preg_match( '/'.$rule[0].'/', $item['value'] ) )
				return $rule[1];
		}
	}

	return NULL;
}

?>