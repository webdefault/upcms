<?php

load_lib_file( 'cms/fields/simpletext' );

class SimpleTable extends SimpletextField
{
	protected $submap, $subrequest, $subvalues;
	protected function usingSetupTable()
	{
		return false;
	}
	
	protected function loadOptions( $id )
	{
		$this->submap = CMS::mapByName( $this->field->map );
		
		$from = $this->getFrom( @$this->cfrom );
		// It only supports join to mapName.id yet
		$join = explode( '=', $from->join[0] );
		
		$this->subrequest = Request::createRequestFromTarget( $this->field->map, $this->submap, array(), array(), $this->db );
		
		$custom = new CustomField( $this->field->map, $join[0], '"'.$id.'"' );
		$custom->set( $this->subrequest, $this->field->map, $this->subrequest, array(), array(), NULL, $this->db );
		
		if( $id )
		{
			$mql = $this->subrequest->matrixQueryForSelect();
			$mql['data'][$this->field->map]['id'] = array( NULL, '' );
			$this->subvalues = $this->db->select( MatrixQuery::select( $mql ) );
		}
		/*
		if( $id )
		{
			
			$sql = 'SELECT COUNT(*) FROM '.$from->table.' AS p WHERE '.$join[0].'='.$id.' AND '.$this->field->{'save-column'}.'='.$this->field->map.'.id';
			
			$this->optionsRequest->setCustomColumn( '_hidden_selected', $sql );
		}
		
		
		
		
		// echo MatrixQuery::select( $mql )."\n";
		
		*/
		//return $result;
	}

	public function select( $quick = false )
	{
		// $this->request->setColumnUsing( $this->cfrom, $this->fieldName, true );
		// $this->options = $this->loadOptions();
	}

	public function insert( $values )
	{
		$this->loadOptions( NULL );
		
		$options = @$values[$this->fieldName] ? $values[$this->fieldName] : array();
		
		$from = $this->getFrom( @$this->cfrom );
		
		// It only supports join to mapName.id yet
		$join = explode( '=', $from->join[0] );
		
		$num = 0;
		$a = array_keys( get_object_vars( $this->submap->fields ) );
		// print_R( reset( $a ) );
		$firstField = reset( $a );
		foreach( $options AS $value )
		{
			// Create new item
			// Insert new relation
			$relName = $this->fieldName.'_'.$num;
			$num++;
			
			if( is_array( $value ) )
			{
				
			}
			else
			{
				$rel = new stdClass();
				$rel->id = $from->id;
				$rel->table = $from->table;
				$rel->join = $from->join;
				$this->map->reltables->{$relName} = $rel;
				
				$custom = new CustomField( $relName, $firstField, '"'.$value.'"' );
				$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
				$this->request->setupTable( $this->mapName, $this->map, $relName );
			}
		}
	}
	
	public function update( $values )
	{
		$this->loadOptions( @$values['id'] );
		
		$options = @$values[$this->fieldName] ? $values[$this->fieldName] : array();
		
		$from = $this->getFrom( @$this->cfrom );
		
		// It only supports join to mapName.id yet
		$join = explode( '=', $from->join[0] );
		
		$column = $this->request->column( 'id' );
		$id = @$column['value'];
		$this->loadOptions( $id );
		
		$options = @$values[$this->fieldName] ? $values[$this->fieldName] : array();
		
		$from = $this->getFrom( @$this->cfrom );
		// It only supports join to mapName.id yet
		$join = explode( '=', $from->join[0] );
		
		foreach( $this->subvalues AS $line )
		{
			$oldValue = $line['id'];
			$newValue = @$options[$line['id']];
			if( $newValue != 1 )
			{
				$wheres = array();
				$wheres[$from->id] = $line['id'];
				
				$this->db->delete( $from->table, array( $wheres ) );
			}
			
			unset( $options[$line['id']] );
		}
		
		$num = 0;
		$a = array_keys( get_object_vars( $this->submap->fields ) );
		// print_R( reset( $a ) );
		$firstField = reset( $a );
		foreach( $options AS $value )
		{
			// Create new item
			// Insert new relation
			$relName = $this->fieldName.'_'.$num;
			$num++;
			
			if( is_array( $value ) )
			{
				
			}
			else
			{
				$rel = new stdClass();
				$rel->id = $from->id;
				$rel->table = $from->table;
				$rel->join = $from->join;
				$rel->insert = true;
				$this->map->reltables->{$relName} = $rel;
				
				$custom = new CustomField( $relName, $firstField, '"'.$value.'"' );
				$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
				$this->request->setupTable( $this->mapName, $this->map, $relName );
			}
		}
		
		// print_r( $options );*/
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
		$this->loadOptions( @$values['id'] );
		
		$temp = new stdClass();
		$temp->type = 'list';
		$temp->footer = true;
		$temp->{'create-new'} = true;
		$temp->{'create-new-placeholder'} = @$this->field->{'create-new-placeholder'} ? $this->field->{'create-new-placeholder'} : '';
		
		$temp->{'delete-item'} = true;
		$temp->icon = $this->field->icon;
		$temp->id = $this->fieldName;
		$temp->title = $this->field->title;
		$temp->help = @$this->field->help;
		$temp->valid = @$this->field->validate_field;
		$temp->rows = array();
		
		$names = $this->subrequest->visibleColumnsName();
		
		// $temp->{'title-field'} = key( (array)$this->optionsValues[0] );
		
		if( $this->subvalues )
		{
			foreach( $this->subvalues AS $line )
			{
				// echo $line['_hidden_selected']."\n";
				$row = array(
					'id' => $line['id'],
					'columns' => array()
					);
				
				foreach( $names AS $subname )
				{
					$column = $this->subrequest->column( $subname );
					$colview = $column['field']->listView( $line['id'], @$line );
					if( $colview ) $row['columns'][] = $colview;
				}
				
				$temp->rows[] = $row;
			}
		}
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['simpletable'] = 'SimpleTable';

?>