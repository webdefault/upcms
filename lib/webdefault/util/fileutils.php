<?php

function non_overwrite_path( $path )
{
	$i = 1;
	
	$t = substr( $path, count( $path ) - 2 );
	
	if( $t == '/' or $t == '\\' ) $path = substr( $path, 0, count( $path ) - 2 );
	
	$t = explode( '/', $path );
	array_pop( $t );
	$p = join( '/', $t );
	
	$filename = '/'.basename( $path );
	$curname = $filename;
	
	$t = explode( '.', $filename );
	$ext = count( $t ) > 1 ? array_pop( $t ) : '';
	$filename = join( '.', $t );
	
	while( is_file( $p.$curname ) or is_dir( $p.$curname ) )
	{
		$curname = $filename.'_'.$i;
		$curname .= '.'.$ext;
		$i++;
	}
	
	return $p.$curname;
}

?>