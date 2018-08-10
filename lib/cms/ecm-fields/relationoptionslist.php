<?php

class ECMRelationOptionsListField extends SimpletextField
{
	protected $optionsRequest, $optionsValues, $optionsMap;
	
	protected function usingSetupTable()
	{
		return false;
	}
	
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
		foreach( $this->optionsValues AS $line )
		{
			$newValue = @$options[$line['id']];
			
			if( $newValue == 1 )
			{
				$relName = $this->fieldName.'_'.$num;
				$num++;
				
				$rel = new stdClass();
				$rel->id = $from->id;
				$rel->table = $from->table;
				$rel->join = $from->join;
				$this->map->reltables->{$relName} = $rel;
				
				$custom = new CustomField( $relName, $this->column(), $line['id'] );
				$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
				
				$this->request->setupTable( $this->mapName, $this->map, $relName );
			}
			
			unset( $options[$line['id']] );
		}
		
		$names = $this->optionsRequest->visibleColumnsName();
		$titleField = key( (array)$this->optionsValues[0] );
		
		foreach( $options AS $value )
		{
			// Create new item
			// Insert new relation
			$relName = $this->fieldName.'_'.$num;
			$num++;
			
			$rel = new stdClass();
			$rel->id = $this->optionsMap->id;
			$rel->table = $this->optionsMap->table;
			$this->map->reltables->{$relName} = $rel;
			
			$custom = new CustomField( $relName, $titleField, '"'.$value.'"' );
			$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
			$this->request->setupTable( $this->mapName, $this->map, $relName );
			
			// Insert new relation
			$attrName = $relName;
			$relName = $this->fieldName.'_'.$num;
			$num++;
			
			$rel = new stdClass();
			$rel->id = $from->id;
			$rel->table = $from->table;
			$rel->join = $from->join;
			$this->map->reltables->{$relName} = $rel;
			
			$custom = new CustomField( $relName, @$this->column(), $attrName.'.id' );
			$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
			$this->request->setupTable( $this->mapName, $this->map, $relName );
		}
	}
	
	public function update( $values )
	{
		$column = $this->request->column( 'id' );
		$id = @$column['value'];
		$this->loadOptions( $id );
		
		$options = @$values[$this->fieldName] ? $values[$this->fieldName] : array();
		
		$from = $this->getFrom( @$this->cfrom );
		// It only supports join to mapName.id yet
		$join = explode( '=', $from->join[0] );
		
		foreach( $this->optionsValues AS $line )
		{
			$oldValue = $line['_hidden_selected'];
			$newValue = @$options[$line['id']];
			if( $newValue != $oldValue )
			{
				if( $newValue == 1 )
				{
					$columns = array();
					$columns[$join[0]] = $id;
					$columns[$this->field->{'save-column'}] = $line['id'];
					
					$this->db->insert( $from->table, array( $columns ) );
				}
				else
				{
					$wheres = array();
					$wheres[$join[0]] = $id;
					$wheres[$this->field->{'save-column'}] = $line['id'];
					
					$this->db->delete( $from->table, array( $wheres ) );
				}
			}
			
			unset( $options[$line['id']] );
		}
		
		$names = $this->optionsRequest->visibleColumnsName();
		$titleField = key( (array)$this->optionsValues[0] );
		
		foreach( $options AS $value )
		{
			// Create new item
			$mql = $this->optionsRequest->matrixQueryForInsert( array( $titleField => $value ) );
			// MatrixQuery::printQuery( $mql );
			$result = MatrixQuery::insert( $mql, $this->db );
			
			// Insert new relation
			$columns = array();
			$columns[$join[0]] = $id;
			$columns[$this->field->{'save-column'}] = $result[$this->field->map];
			
			$this->db->insert( $from->table, array( $columns ) );
		}
		
		// print_r( $options );
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
		// print_R( $values );
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
		
		$names = $this->optionsRequest->visibleColumnsName();
		
		$temp->{'title-field'} = key( (array)$this->optionsValues[0] );
		
		foreach( $this->optionsValues AS $line )
		{
			// echo $line['_hidden_selected']."\n";
			$row = array(
				'id' => $line['id'],
				'columns' =>array()
				);
			
			foreach( $names AS $subname )
			{
				$column = $this->optionsRequest->column( $subname );
				$row['columns'][] = $column['field']->listView( $line['id'], @$line );
			}
			
			$temp->rows[] = $row;
			
			if( @$line['_hidden_selected'] == 1 )
			{
				$line['selected'] = true;
			}
			
			$addopts[] = $line;
		}
		
		$temp->{'options'} = $addopts;
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['ecm-relationoptionslist'] = 'ECMRelationOptionsListField';

?>