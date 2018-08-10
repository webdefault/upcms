<?php

/**
 * @package controller
 * @classe home
 *
 */

// ListContent could be just List, but it's a reserved word
class Library extends CMS
{
	private $libpath, $id;

	function __construct( $context )
	{
		parent::__construct( $context );
		$this->libpath = STORAGE_PATH.'/library/';

		$this->init();
	}

	public function alist()
	{
		/*$types = @$_POST['types'];
		
		// GET FILES IN FOLDER //
		
		$this->viewVars['list'] = $this->listfiles( $this->libpath, $types );
		$this->loadView( 'LibraryList', $this->viewVars );
		//global $result;
		*/
		//$result->path = substr( $this->libpath, strlen( $upload_folder ) );
		//$result->list = $fileResult;
		
		//$UpCMS->dispatchEvent( new Event( UpCMS::FILEGALLERY_AFTER_ALIST, $this ) );
	}
	
	public function delete()
	{
		$files = $_POST['filenames'];

		foreach( $files AS $file )
		{
			$filePath = $this->libpath.'/'.$file;

			if( is_file( $filePath ) )
			{
				$fileResult = @unlink( $filePath );
				
				if( is_file( $this->libpath.'/'.$file ) )
				{
					$this->dispatchError( 52105,
						'O Arquivo \''.$_POST['filename'].'\' não pode ser apagado, verifique as permissões.' );
				}
				else
				{
					$this->viewVars['result'] = substr( $filePath, strlen( STORAGE_PATH ) );
					$this->loadView( 'LibraryResult', $this->viewVars );
				}
			}
			else
			{
				$this->dispatchError( 52104, 'O Arquivo \''.$file.'\' não existe.' );
			}
		}
	}

	public function rename()
	{
		$filePath = $this->libpath.'/'.$_POST['filename'];

		if( is_file( $filePath ) )
		{
			$newFilePath = $this->libpath.'/'.$_POST['newfilename'];

			if( is_file( $newFilePath ) )
			{
				$this->dispatchError( 52106, 'Já existe um arquivo com o nome \''.$_POST['filename'].'\'.' );
			}
			else
			{
				$fileResult = @rename( $filePath, $newFilePath );

				if( !is_file( $newFilePath ) )
				{
					$this->dispatchError( 52107, 
						'O Arquivo \''.$_POST['filename'].'\' não pode ser renomeado, verifique as permissões.' );
				}
				else
				{
					$this->viewVars['result'] = substr( $newFilePath, strlen( STORAGE_PATH ) );
					$this->loadView( 'LibraryResult', $this->viewVars );
				}
			}
		}
		else
		{
			$this->dispatchError( 52104, 'O Arquivo \''.$_POST['filename'].'\' não existe.' );
		}
	}

	public function upload()
	{
		$targetPath = $this->libpath;
		// print_r( $targetPath );
		
		$this->viewVars['result'] = array();
		
		foreach( $_FILES AS $file )
		{
			$i = 1;
			$curname = basename( 
				$this->nonOverwritePath( 
					clean_special_chars( $targetPath.basename( $file['name'] ) )
				) 
			);

			$filePath = $targetPath.$curname;
			@move_uploaded_file( $file['tmp_name'], $filePath );

			if( !is_file( $filePath ) )
			{
				$this->dispatchError( 52108, 
							'O Arquivo \''.$file['name'].'\' não pode ser salvo, verifique as permissões.' );
			}
			else
			{
				array_push( $this->viewVars['result'], array( 
					'name'=>substr( $filePath, strlen( $targetPath ) ),
					'path'=>$curname
				));
			}
		}

		$this->loadView( 'LibraryResult', $this->viewVars );
	}

	public function decompress()
	{
		$file = $_POST['file'];
		$filePath = $filePath.'/'.$file;
		if( is_file( $filePath ) )
		{
			$ext = end( explode( '.', $file ) );

			load_lib_file( 'cms/compression' );

			if( strtolower( $ext ) == 'zip' )
				$files = decompress_zip( $filePath, $this->libpath );
			
			if( @$files != NULL )
			{
				$this->viewVars['result'] = $files;
				$this->loadView( 'LibraryResult', $this->viewVars );
			}
			else
			{
				$this->dispatchError( 52109, 'Não foi possível extrair nenhum arquivo de \''.$_POST['file'].'\'. Verifique se o arquivo zip não está corrompido e as permissões de pasta no servidor.' );
			}
		}
	}

	private function dispatchError( $errorNumber, $errorText )
	{
		$this->viewVars['error'] = $errorNumber;
		$this->viewVars['result'] = $errorText;
		$this->loadView( 'LibraryResult', $this->viewVars );
		exit();
	}

	private function init()
	{
		if( !is_dir( STORAGE_PATH ) )
		{
			$this->dispatchError( 52101, 'Pasta de arquivos não existe ou está configurada corretamente.' );
		}
		else if( !is_dir( $this->libpath ) )
		{
			@mkdir( $this->libpath, 0777 );

			if( !is_dir( $this->libpath ) )
			{
				$this->dispatchError( 52102, 'Não foi possível criar a pasta \'library\'. Verifique as permissões da pasta de arquivos.' );
			}
			else
			{
				chmod( $this->libpath, 0777 );
			}
		}

		$userpath = 'user'.$this->user['id'].'/';
		$this->libpath .= $userpath;

		if( !is_dir( $this->libpath ) )
		{
			@mkdir( $this->libpath, 0777 );

			if( !is_dir( $this->libpath ) )
			{
				$this->dispatchError( 52103, 'Não foi possível criar a pasta \'library/'.$userpath.'\'. Verifique as permissões da pasta de arquivos.' );
				$this->loadView( 'LibraryResult', $this->viewVars );
				exit();
			}
			else
			{
				chmod( $this->libpath, 0777 );
			}
		}
	}

	private static function listfiles( $path, $exts )
	{
		$result = array();

		if( !is_dir( $path ) ) return NULL;
		
		if( $handle = opendir( $path ) )
		{
			if( $exts != NULL && $exts[0] == '!' )
			{
				while( false !== ( $file = readdir( $handle ) ) )
				{
					$explode = explode( '.', $file );
					$ext = end( $explode );
					
					if( strpos( $file, '.' ) !== 0 and ( $exts == ';' or strpos( $exts, $ext ) === false or strpos( $exts, $file ) === false ) )
					{
						$t = array( 
							'name' => $file, 
							'path' => ( $substr ? substr( $path, strlen( $upload_folder ) ) : $path ).'/'.$file,
							'type' => is_dir( $path.'/'.$file ) ? 'dir' : 'file'
						);
						
						array_push( $result, $t );
					}
				}
			}
			else
			{
				while( false !== ( $file = readdir( $handle ) ) )
				{
					$explode = explode( '.', $file );
					$ext = end( $explode );
					
					if( strpos( $file, '.' ) !== 0 )
					{
						$t = array( 
							'name' => $file, 
							'path' => substr( $path, strlen( STORAGE_PATH ) ).$file,
							'type' => is_dir( $path.'/'.$file ) ? 'dir' : 'file'
						);

						array_push( $result, $t );
					}
				}
			}
			
			closedir( $handle );
		}
		
		return $result;
	}

	private function nonOverwritePath( $path )
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
}

?>
