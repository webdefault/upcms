<?php

class ECMTreeMultipleselect extends SimpletextField
{
	protected $submap, $subrequest, $subvalues, $checks;
	protected function usingSetupTable()
	{
		return false;
	}
	
	protected function loadTreeValues( $relId )
	{
		$this->submap = CMS::mapByName( $this->field->map );
		
		// Load checks of the tree (if provided id)
		if( $relId )
		{
			$from = $this->getFrom( @$this->cfrom );
			// It only supports join to mapName.id yet
			$join = explode( '=', $from->join[0] );
			$sql = 'SELECT '.$this->field->columns->level.' AS level, '.$this->field->columns->value.' AS value FROM '.$from->table.' AS p WHERE '.$join[0].'='.$relId;
			
			$values = $this->db->select( $sql );
			// echo $sql; exit;
			// print_r( $this->values ); exit;
			$this->checks = array();
			foreach( $values AS $item )
			{
				$level = $item['level'];
				$value = $item['value'];
				
				if( !@$this->checks[$level] ) $this->checks[$level] = array();
				
				$this->checks[$level][$value] = true;
			}
		}
		
		// Load tree
		$this->subrequest = Request::createRequestFromTarget( $this->field->map, $this->submap, array(), array(), $this->db );
		$mql = $this->subrequest->matrixQueryForSelect();
		$mql['order'] = $this->submap->orderby;
		// echo MatrixQuery::select( $mql ); exit;
		$result = $this->db->select( MatrixQuery::select( $mql ) );
		
		$this->subvalues = new stdClass();
		// print_r( $this->field->columns );
		foreach( $result AS $item )
		{
			$level = 0;
			$target = NULL;
			foreach( $this->field->{'map-columns'} AS $col )
			{
				$id = @$item[$col->id];
				
				if( $id )
				{
					if( $target )
						$temp = @$target->subs->{'a'.$id};
					else
						$temp = @$this->subvalues->{'a'.$id};
					
					if( !$temp )
					{
						$temp = new stdClass();
						$temp->id = $id;
						$temp->name = $item[$col->name];
						$temp->subs = new stdClass();
						$temp->value = $this->isSelected( $level, $id );
						
						if( $target )
							$target->subs->{'a'.$id} = $temp;
						else
							$this->subvalues->{'a'.$id} = $temp;
					}
					
					$target = $temp;
					$level++;
				}
				else break;
			}
		}
	}
	
	private function isSelected( $level, $value )
	{
		if( $this->checks && @$this->checks[$level] )
			return @$this->checks[$level][$value] != NULL;
		else
			return false;
	}
	
	private function unselect( $level, $value )
	{
		if( $this->checks && @$this->checks[$level] )
		{
			unset( $this->checks[$level][$value] );
			if( count( array_keys( $this->checks[$level] ) ) == 0 ) unset( $this->checks[$level] );
		}
		else
			return false;
	}
	
	public function processTreeColumn( $item, $colindex )
	{
		
	}
	
	public function select( $quick = false )
	{
		// $this->request->setColumnUsing( $this->cfrom, $this->fieldName, true );
		// $this->options = $this->loadOptions();
	}

	public function insert( $values )
	{
		$this->loadTreeValues( NULL );
		
		$options = @$values[$this->fieldName] ? $values[$this->fieldName] : array();
		$from = $this->getFrom( @$this->cfrom );
		
		$num = 0;
		foreach( $options AS $level => $values )
		{
			foreach( $values AS $value => $status )
			{
				if( $status == 'on' )
				{
					$relName = $this->fieldName.'_'.$num;
					$num++;
					
					$rel = new stdClass();
					$rel->id = $from->id;
					$rel->table = $from->table;
					$rel->join = $from->join;
					$this->map->reltables->{$relName} = $rel;
					
					$custom = new CustomField( $relName, $this->field->columns->level, '"'.$level.'"' );
					$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
					
					$custom = new CustomField( $relName, $this->field->columns->value, '"'.$value.'"' );
					$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
					
					$this->request->setupTable( $this->mapName, $this->map, $relName );
				}
			}
		}
	}
	
	public function update( $values )
	{
		$column = $this->request->column( 'id' );
		
		$this->loadTreeValues( @$column['value'] );
		
		$options = @$values[$this->fieldName] ? $values[$this->fieldName] : array();
		$from = $this->getFrom( @$this->cfrom );
		
		$num = 0;
		foreach( $options AS $level => $values )
		{
			foreach( $values AS $value => $status )
			{
				if( $status == 'on' && !$this->isSelected( $level, $value ) )
				{
					$relName = $this->fieldName.'_'.$num;
					$num++;
					
					$rel = new stdClass();
					$rel->id = $from->id;
					$rel->table = $from->table;
					$rel->join = $from->join;
					$rel->insert = true;
					$this->map->reltables->{$relName} = $rel;
					
					$custom = new CustomField( $relName, $this->field->columns->level, '"'.$level.'"' );
					$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
					
					$custom = new CustomField( $relName, $this->field->columns->value, '"'.$value.'"' );
					$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
					
					$this->request->setupTable( $this->mapName, $this->map, $relName );
				}
				
				// Remove this level+value from checks
				$this->unselect( $level, $value );
			}
		}
		
		foreach( $this->checks AS $level => $values )
		{
			foreach( $values AS $value => $status )
			{
				$wheres = array();
				$wheres[$this->field->columns->level] = $level;
				$wheres[$this->field->columns->value] = $value;
				
				$this->db->delete( $from->table, array( $wheres ) );
			}
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
		// $this->loadTreeValues();

		$temp = new stdClass();
		$temp->type = 'text';
		$temp->value = $value;
		
		return $temp;
	}
	
	public function editView( $values )
	{
		$this->loadTreeValues( @$values['id'] );
		
		$temp = new stdClass();
		$temp->type = 'tree';
		$temp->id = $this->fieldName;
		$temp->selectable = true;
		$temp->subs = $this->subvalues;
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['ecm-treemultipleselect'] = 'ECMTreeMultipleselect';

?>