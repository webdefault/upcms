<?php

function decompress_zip( $target, $destiny )
{
	$result = array();

	$file = new ZipArchive();
	if( $file->open( $target ) === true )
	{
		for ( $i = 0; $i < $file->numFiles; $i++ )
		{
			$t = $file->statIndex( $i );
			array_push( $result, $destiny.'/'.$t['name'] );
		}
		
		$extract = $file->extractTo( $destiny );
		$file->close();
		
		return $extract ? $files : NULL;
	}
	else return NULL;
}

?>