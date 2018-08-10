<?php

load_lib_file( 'cms/fields/simpletext' );
load_lib_file( 'webdefault/util/fileutils' );

class ImageGalleryField extends SimpletextField
{
	protected $optionsRequest, $optionsValues, $optionsMap;
	
	protected function usingSetupTable()
	{
		return false;
	}
	/*
	protected function loadOptions( $id )
	{
		$this->optionsMap = CMS::mapByName( $this->field->map );
		$this->optionsRequest = Request::createRequestFromTarget( $this->field->map, $this->optionsMap, array(), array(), $this->db );
		// MatrixQuery::printQuery( $mql );
		
		if( $id )
		{
			$from = $this->getFrom( @$this->cfrom );
			// It only supports join to mapName.id yet
			$join = explode( '=', $from->join[0] );
			$sql = 'SELECT COUNT(*) FROM '.$from->table.' AS p WHERE '.$join[0].'='.$id.' AND '.$this->field->{'save-column'}.'='.$this->field->map.'.id';
			
			$this->optionsRequest->setCustomColumn( '_hidden_selected', $sql );
		}
		
		$mql = $this->optionsRequest->matrixQueryForSelect();
		$mql['data'][$this->field->map]['id'] = array( NULL, '' );
		
		// echo MatrixQuery::select( $mql )."\n";
		$this->optionsValues = $this->db->select( MatrixQuery::select( $mql ) );
		
		//return $result;
	}
	*/
	public function select( $quick = false )
	{
		// $this->request->setColumnUsing( $this->cfrom, $this->fieldName, true );
		// $this->options = $this->loadOptions();
	}
	
	protected function deleteFiles( $files )
	{
		$folder = STORAGE_PATH.'/'.$this->field->folder.'/';
		$cut = strlen( '#USER_LIBRARY' );
		
		foreach( $files AS $file )
		{
			if( substr( $file, 0, $cut ) == '#USER_LIBRARY' )
				$path = str_replace( '#USER_LIBRARY/', USER_LIBRARY_PATH, $file );
			else
				$path = $folder.$file;
			
			unlink( $path );
		}
	}

	public function insert( $values )
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
		
		$config = @$values[$this->fieldName] ? $values[$this->fieldName] : array();
		$config['positions'] = explode( ',', $config['positions'] );
		if( !@$config['add'] ) $config['add'] = array();
		if( !@$config['remove'] ) $config['remove'] = array();
		
		$from = $this->getFrom( @$this->cfrom );
		
		$num = 0;
		$pfield = @$this->field->{'sort-column'};
		foreach( $config['add'] AS $key => $file )
		{
			$oldpath = str_replace( '#USER_LIBRARY/', USER_LIBRARY_PATH, $file );
			$newpath = str_replace( '#USER_LIBRARY/', $folder, $file );
			$newpath = non_overwrite_path( $newpath );
			$path = str_replace('#USER_LIBRARY/', '', $file );
			rename( $oldpath, $newpath );
			
			$relName = $this->fieldName.'_'.$num;
			$num++;
			
			$rel = new stdClass();
			$rel->id = $from->id;
			$rel->table = $from->table;
			$rel->join = $from->join;
			$this->map->reltables->{$relName} = $rel;
			
			// image source
			$custom = new CustomField( $relName, $this->column(), '"'.$path.'"' );
			$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
			
			// position
			if( $pfield )
			{
				$position = array_search( $key, $config['positions'] );
				$custom = new CustomField( $relName, $pfield, $position );
				$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
			}
			
			$this->request->setupTable( $this->mapName, $this->map, $relName );
		}
		
		$this->deleteFiles( $config['remove'] );
	}
	
	public function update( $values )
	{
		$folder = STORAGE_PATH.'/'.$this->field->folder.'/';
		$sourceColumn = $this->column();
		
		if( !is_dir( $folder ) )
		{
			@mkdir( $folder, 0777 );

			if( !is_dir( $folder ) )
			{
				throw new Exception( 'Error: Não foi possível criar a pasta \''.$folder.'\'.  Verifique as permissões da pasta de arquivos.' );
			}
		}
		
		$config = @$values[$this->fieldName] ? $values[$this->fieldName] : array();
		if( !@$config['remove'] ) $config['remove'] = array();
		if( !@$config['add'] ) $config['add'] = array();
		if( !@$config['list'] ) $config['list'] = array();
		if( !@$config['positions'] ) $config['positions'] = '';
		if( !@$config['remove-id'] ) $config['remove-id'] = array();
		
		$config['positions'] = explode( ',', $config['positions'] );
		
		$from = $this->getFrom( @$this->cfrom );
		
		$num = 0;
		$pfield = @$this->field->{'sort-column'};
		
		// Inserts
		foreach( $config['add'] AS $key => $file )
		{
			$oldpath = str_replace( '#USER_LIBRARY/', USER_LIBRARY_PATH, $file );
			$newpath = str_replace( '#USER_LIBRARY/', $folder, $file );
			$newpath = non_overwrite_path( $newpath );
			$path = str_replace('#USER_LIBRARY/', '', $file );
			rename( $oldpath, $newpath );
			
			$relName = $this->fieldName.'_'.$num;
			$num++;
			
			$rel = new stdClass();
			$rel->id = $from->id;
			$rel->table = $from->table;
			$rel->join = $from->join;
			$rel->insert = true;
			$this->map->reltables->{$relName} = $rel;
			
			// image source
			$custom = new CustomField( $relName, $sourceColumn, '"'.$path.'"' );
			$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
			
			// position
			if( $pfield )
			{
				$position = array_search( $key, $config['positions'] );
				$custom = new CustomField( $relName, $pfield, $position );
				$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
			}
			
			$this->request->setupTable( $this->mapName, $this->map, $relName );
		}
		
		// Deletes additions
		$this->deleteFiles( $config['remove'] );
		
		// Updates
		foreach( $config['list'] AS $key => $fileId )
		{
			$relName = $this->fieldName.'_'.$num;
			$num++;
			
			$rel = new stdClass();
			$rel->id = $from->id;
			$rel->table = $from->table;
			$rel->join = $from->join;
			$this->map->reltables->{$relName} = $rel;
			
			// id
			$custom = new CustomField( $relName, 'id', $fileId );
			$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
			
			// position
			if( $pfield )
			{
				$position = array_search( $key, $config['positions'] );
				$custom = new CustomField( $relName, $pfield, $position );
				$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
			}
			
			$this->request->setupTable( $this->mapName, $this->map, $relName );
		}
		
		// Delete old files
		if( count( $config['remove-id'] ) > 0 )
		{
			$list = $this->db->select( 'SELECT '.$from->id.' AS id, '.$sourceColumn.' AS source '.
										' FROM '.$from->table.' WHERE '.$from->id.
										' IN (\''.implode('\', \'', $config['remove-id'] ).'\')' );
			
			$removeIds = array();
			$removeFiles = array();
			foreach( $list AS $item )
			{
				$removeFiles[] = $item['source'];
				$removeIds[] = array( 'id' => $item['id'] );
			}
			
			$this->db->delete( $from->table, $removeIds );
			$this->deleteFiles( $removeFiles );
		}
	}

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
				$result->id = "id";
				$result->from = $targetName;
				$result->join = "";

				return $result;
			}
		}
	}

	public function listView( $id, $values )
	{
		$this->loadOptions();

		$temp = new stdClass();
		$temp->type = 'text';
		$temp->value = $value;
		
		return $temp;
	}
	
	public function editView( $values )
	{
		$temp = new stdClass();
		$temp->id = $this->fieldName;
		$temp->title = $this->field->title;
		$temp->type = 'image-gallery';
		$temp->upload = 'library/upload/'.$this->field->folder;
		
		$id = @$values['id'];
		
		if( $id )
		{
			$column = $this->column();
			$from = $this->getFrom( @$this->cfrom );
			$sql = 'SELECT '.$from->id.' AS id, CONCAT(\''.STORAGE.$this->field->folder.'/\', '.$column.') AS source, '.$column.' AS title';
			$order = '';
			
			if( $this->field->{'sort-column'} )
			{
				$sql .= ', '.$this->field->{'sort-column'}.' AS positions';
				$order = ' ORDER BY positions ASC';
			}
			$sql .= ' FROM '.$from->table.' WHERE ';
			
			$append = '';
			foreach( $from->join AS $join )
			{
				$path = explode( '=', $join );
				
				if( substr( $path[1], 0, 1 ) == '"' )
					$sql .= $append.$join;
				else
				{
					$sql .= $append.$path[0].'=\''.$id.'\'';
				}
				$append = ' AND ';
			}
			
			$sql .= $order;

			$result = $this->db->select( $sql );
			// echo $sql; exit;
			$temp->images = $result;
		}
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['imagegallery'] = 'ImageGalleryField';

?>