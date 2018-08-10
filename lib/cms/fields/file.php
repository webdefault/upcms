<?php

load_lib_file( 'cms/fields/simpletext' );
load_lib_file( 'webdefault/util/fileutils' );

class FileField extends SimpletextField
{
	protected function getFrom( $targetName )
	{
		if( $targetName == $this->mapName )
		{
			return $this->map;
		}
		else
		{
			$from = @$this->map->reltables->{@$targetName};

			if( $from )
				return $from;
			else
			{
				$result = new stdClass();
				$result->id = 'id';
				$result->from = $targetName;
				$result->join = '';

				return $result;
			}
		}
	}
	
	public function validate( $currentValues, $values, $fullValidation = false )
	{
		$file = @$values[$this->fieldName] ? explode( ',', $values[$this->fieldName] ) : array();
		$path;
		
		return @$file[0] == 1 ? parent::validate( $currentValues, $values, $fullValidation ) : NULL;
	}
	
	public function insert( $values )
	{
		$file = @$values[$this->fieldName] ? explode( ',', $values[$this->fieldName] ) : array();
		$path;
		
		if( $file[0] == 1 )
		{
			$folder = STORAGE_PATH.'/'.$this->field->folder.'/';
			
			if( !is_dir( $folder ) )
			{
				@mkdir( $folder, 0777 );

				if( !is_dir( $folder ) )
				{
					throw new Exception( 'Error: Não foi possível criar a pasta \''.$folder.'\'.  Verifique as permissões da pasta de arquivos.' );
				}
			}
			
			$oldpath = str_replace( '#USER_LIBRARY/', USER_LIBRARY_PATH, $file );
			$newpath = str_replace( '#USER_LIBRARY/', $folder, $file );
			$newpath = non_overwrite_path( $newpath );
			$path = str_replace('#USER_LIBRARY/', '', $file );
			rename( $oldpath, $newpath );

			$this->request->setColumn( $this->cfrom, @$this->field->column, $this->fieldName, '"'.$path.'"', $this, 1 );
		}
	}
	
	public function update( $values )
	{
		$file = @$values[$this->fieldName] ? explode( ',', $values[$this->fieldName] ) : array();
		$path;
		
		if( $file[0] == 1 )
		{
			$folder = STORAGE_PATH.'/'.$this->field->folder.'/';
			$file = $file[1];
			if( !is_dir( $folder ) )
			{
				@mkdir( $folder, 0777 );

				if( !is_dir( $folder ) )
				{
					throw new Exception( 'Error: Não foi possível criar a pasta \''.$folder.'\'.  Verifique as permissões da pasta de arquivos.' );
				}
			}
			
			$path = str_replace( '#USER_LIBRARY/', '', $file );
			
			$cut = strlen( '#USER_LIBRARY' );
			if( substr( $file, 0, $cut ) == '#USER_LIBRARY' )
			{
				$oldpath = str_replace( '#USER_LIBRARY/', USER_LIBRARY_PATH, $file );
				$newpath = str_replace( '#USER_LIBRARY/', $folder, $file );
				$newpath = non_overwrite_path( $newpath );
				rename( $oldpath, $newpath );
			}
			else
			{
				// Should delete the old file;
				// unlink( $oldfile );
			}

			$this->request->setColumn( $this->cfrom, @$this->field->column, $this->fieldName, '"'.$path.'"', $this, 1 );
		}
	}
	
	protected function applyValidations( $target )
	{
		if( @$this->field->validation && @$this->field->validation->onChange == true )
		{
			if( !@$target->events ) 
				$target->events = new stdClass();
			
			$target->events->change = array(
				array( 'validation', 'edit-content/validate/'.$this->mapName, 'post', $this->fieldName ) );
		}
	}
	
	public function editView( $values )
	{
		$temp = new stdClass();
		$temp->type = 'file';
		$temp->id = $this->fieldName;
		$temp->title = $this->field->title;
		// $temp->display = @$this->field->display;
		$temp->help = @$this->field->help;
		$temp->valid = @$this->field->validate_field;
		$temp->value = @$values[$this->fieldName];
		
		$temp->upload = isset( $this->field->upload ) ? $this->field->upload : 'library/upload/'.$this->field->folder;
		if( isset( $this->field->{'upload-hash'} ) ) $temp->{'upload-hash'} = $this->field->{'upload-hash'};
		$temp->readonly = @$this->field->readonly;
		$temp->options = array();
		
		$temp->{'layout-size'} = parse_bracket_instructions( @$this->field->{'edit-layout-size'}, $values );
		
		if( $temp->value )
		{
			$temp->image = isset( $this->field->url ) ?
				parse_bracket_instructions( $this->field->url, array( 'value' => $values[$this->fieldName] ) ) : 
				STORAGE.$this->field->folder.'/'.$temp->value;
		}
		
		$this->applyValidations( $temp );
		
		return $temp;
	}
	
	public function listView( $id, $values )
	{
		$temp = new stdClass();
		$temp->type = 'file';
		$temp->value = isset( $this->field->thumb ) ? 
			parse_bracket_instructions( $this->field->thumb, array( 'value' => $values[$this->fieldName] ) ) : 
			SERVICE.'slir/w50xh50-c1x1/'.$this->field->folder.'/'.$values[$this->fieldName];
		
		return $temp;
	}
	
	public function url( $value )
	{
		$temp = explode( ',', $value );
		$value = str_replace( '#USER_LIBRARY', '', $temp[1] );
		
		if( @$this->field->url )
		{
			return parse_bracket_instructions( $this->field->url, array('value' => $value ) );
			// $path = "https://stage.agendadacidade.com.br/admin-imagem/parceiros/anigifseg_4.gif";
		}
		else
		{
			return STORAGE.$this->field->folder.'/'.$value;
		}
	}
}

global $__CMSFields;
$__CMSFields['file'] = 'FileField';

?>