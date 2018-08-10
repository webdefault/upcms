<?php

class BasicUpload
{
	public static function save( $postImage, $folder, $usingStoragePath = true )
	{
		if( $usingStoragePath ) $folder = STORAGE_PATH.'/'.$folder;

		$curname = basename( 
			BasicUpload::nonOverwritePath( 
				$folder.'/'.clean_special_chars( basename( $postImage['name'] ) ) ) );

		$filePath = $folder.'/'.$curname;
		
		@move_uploaded_file( $postImage['tmp_name'], $filePath );
		// echo $filePath;
		if( !is_file( $filePath ) )
		{
			return NULL;
		}
		else
		{
			$path = $usingStoragePath ? substr( $filePath, strlen( STORAGE_PATH ) ) : $filePath;
			return array( 'name' => $curname, 'path' => $path );
		}
	}

	private static function nonOverwritePath( $path )
	{
		echo $path;
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
}

?>