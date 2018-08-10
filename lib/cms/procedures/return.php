<?php

load_lib_file( 'cms/procedures' );
load_lib_file( 'cms/parse_bracket_instructions' );

CMSProcedures::addProcedure( 'return', function( $process, $instance )
{
	$object = $instance->getObject();
	$ifNull = @$process->{'if-null'};

	$path = explode('.', $process->path );
	$target = $object->{$path[0]};

	if( $target )
	{
		$result = $target[$path[1]];
		if( $result !== NULL )
		{
			return $result;
		}
		else
		{
			return $ifNull ? $ifNull : false;
		}
	}
	else
	{
		return $ifNull ? $ifNull : false;
	}
	// $result = parse_bracket_instructions( '{'.$process->path.'}', $object );
	// return $result;
} );

?>