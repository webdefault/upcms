<?php

load_lib_file( 'cms/procedures' );

CMSProcedures::addProcedure( 'exec', function( $process, $instance )
{
	$app = parse_bracket_instructions( $process->application, $instance->getObject() );
	$params = parse_bracket_instructions( $process->params, $instance->getObject() );
	
	if( Config::ENV == 'development' )
	{
		exec('unset DYLD_LIBRARY_PATH ;');
		putenv('DYLD_LIBRARY_PATH');
		putenv('DYLD_LIBRARY_PATH=/usr/bin');
	}
	
	exec( $app.' '.$params );
} );

?>