<?php

load_lib_file( 'cms/fields/options' );
load_lib_file( 'cms/parse_bracket_instructions' );

class ListField extends SimpletextField
{
	protected $listMap, $listMapName;
	
	public function set( $request, $mapName, $map, $path, $mapId, $field, $db )
	{
		parent::set( $request, $mapName, $map, $path, $mapId, $field, $db );
		
		$this->listMapName = parse_bracket_instructions( @$this->field->map, array() );
		$this->listMap = CMS::mapByName( $this->listMapName );
		$this->listPage = CMS::pageByName( $this->listMapName );
	}
	
	protected function usingSetupTable()
	{
		return false;
	}
	
	public function select( $quick = false )
	{
		
	}
	
	protected function tableList()
	{
		$result = new stdClass();
		$result->id = $this->listMap->id;
		$result->table = $this->listMap->table;
		if( isset( $this->listMap->where ) ) 
			$result->where = $this->listMap->where;
		
		if( isset( $this->listMap->join ) )
			$result->join = $this->listMap->join;
		
		return $result;
	}
	
	protected function mergeReltables()
	{
		foreach( $this->listMap->reltables AS $key => $item )
		{
			if( !isset( $this->map->reltables->{$key} ) )
			{
				$this->map->reltables->{$key} = $item;
			}
			else
			{
				throw new Exception("Related table with same name '".$key."' on ".$this->mapName." and ".$this->listMapName, 1);
			}
		}
	}
	
	public function insert( $values )
	{
		if( $this->isEditable( $values ) )
		{
			$value = $values[$this->fieldName];
			
			$POST = CMS::globalValue( 'POST' );
			$POST[$this->fieldName] = @$this->mapId[$this->mapName];
			CMS::addGlobalValue( 'POST', $POST );
			
			if( isset( $value['add'] ) && count( $value['add'] ) > 0 )
			{
				$num = 0;
				
				// print_r( $selected ); print_r( $list ); exit;
				$column = $this->column();
				$table = $this->tableList();
				
				$this->mergeReltables();
				
				foreach( $value['add'] AS $row )
				{
					$relName = $this->fieldName.'_'.$num;
					
					$this->map->reltables->{$relName} = $table;
					
					foreach( $this->listMap->fields AS $key => $field )
					{
						$class = CMSFieldClass( $field->type );
						$field->from = $relName;
						$obj = new $class( $key );
						
						$obj->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, $field, $this->db );
						$obj->insert( $row );
					}
					
					$num++;
				}
			}
		}
	}
	
	public function update( $values )
	{
		if( $this->isEditable( $values ) )
		{
			$value = $values[$this->fieldName];
			
			$POST = CMS::globalValue( 'POST' );
			$POST[$this->fieldName] = $this->mapId[$this->mapName];
			CMS::addGlobalValue( 'POST', $POST );
			
			if( isset( $value['add'] ) && count( $value['add'] ) > 0 )
			{
				$num = 0;
				
				// print_r( $selected ); print_r( $list ); exit;
				$column = $this->column();
				$table = $this->tableList();
				$table->insert = true;
				
				$this->mergeReltables();
				
				foreach( $value['add'] AS $row )
				{
					$addable = false;
					foreach( $row As $v )
					{
						// TODO: Apply validation to all fields before submitting.
						if( $v !== NULL && is_string( $v ) && strlen( $v ) > 0 )
						{
							$addable = true;
						}
					}
					
					if( $addable )
					{
						$relName = $this->fieldName.'_'.$num;
						
						$this->map->reltables->{$relName} = $table;
						
						foreach( $this->listMap->fields AS $key => $field )
						{
							$class = CMSFieldClass( $field->type );
							$field->from = $relName;
							$obj = new $class( $key );
							
							$obj->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, $field, $this->db );
							$obj->insert( $row );
						}
						
						$num++;
					}
				}
			}
			
			if( isset( $value['delete'] ) && count( $value['delete'] ) > 0 )
			{
				$deletes = explode( ',', $value['delete'] );
				$erase = array();
				foreach( $deletes AS $where )
				{
					$erase[] = array( 'id' => $where );
				}
				
				$this->db->delete( $this->listMap->table, $erase );
			}
		}
	}
	
	public function listView( $id, $values )
	{
		return NULL;
	}
	
	protected function fields( $target )
	{
		$request = Request::createRequestFromTarget( $this->listMapName, $this->listMap, $this->path, $this->mapId, $this->db );
		
		$fields = new stdClass();
		$columns = $request->visibleColumnsName();
		foreach( $columns AS $c )
		{
			$f = $request->column( $c );
			$temp = $f['field']->editView( array() );
			
			if( $temp ) $fields->{$c} = $temp;
		}
		
		$target->fields = $fields;
		$target->page = new stdClass();
		
		foreach( $this->listPage->list->fields AS $name => $field )
		{
			$temp = '';
			
			if( isset( $field->align ) )
				$temp .= ' text-'.$field->align;
			
			if( isset( $field->{'size'} ) )
				$temp .= ' col-md-'.$field->size;
			
			$target->page->{$name} = $temp;
		}
		
		
		if( isset( $this->mapId[$this->mapName] ) )
		{
			foreach( $this->listMap->join AS $j )
			{
				$join = explode( '=', $j );
				$path = explode( '.', $join[1] );
				
				if( !@$this->listMap->where ) $this->listMap->where = new stdClass();
				
				if( count( $path ) > 1 )
				{
					// print_r( $join );echo "\n";
					$this->listMap->where->{$join[0]} = $this->mapId[$path[0]];
				}
				else
				{
					if( strpos( $join[1], '"' ) == 0 ) $join[1] = substr( $join[1], 1, -1 );
					$this->listMap->where->{$join[0]} = $join[1];
				}
			}/*
			$join = $this->listMap->join[0];
			$join = explode( '=', $join );
			
			if( !@$this->listMap->where ) $this->listMap->where = new stdClass();
			
			$this->listMap->where->{$join[0]} = $this->mapId[$this->mapName];*/
			unset( $this->listMap->join );
			
			$request = Request::createRequestFromTarget( $this->listMapName, $this->listMap, $this->path, $this->mapId, $this->db );
			
			$mql = $request->matrixQueryForSelect();
			
			$temp = $mql['tables'][ $mql['target'] ];
			$mql['data'][ $mql['target'] ][ 'id' ] = array( $temp['id'], null, MatrixQuery::GET );
			// print_r( MatrixQuery::select( $mql ) );exit;
			$result = MatrixQuery::select( $mql, $this->db );
			// print_r( $result );
			$rows = array();
			
			if( $result )
			{
				foreach( $result AS $row )
				{
					// print_r( $row );
					$obj = new stdClass();
					$obj->id = $row['id'];
					$obj->fields = new stdClass();
					
					foreach( $fields AS $key => $f )
					{
						$col = $request->column( $key );
						$temp = $col['field']->editView( $row );
						// print_r( $temp );
						// print_r( $row );
						
						// $obj->fields->{$key} = new stdClass();
						// $obj->fields->{$key}->value = @$temp->value;
						
						$obj->fields->{$key} = $temp;
					}
					
					$rows[] = $obj;
				}
			}
			
			$target->rows = $rows;
		}
	}
	
	
	public function editView( $values )
	{
		$temp = parent::editView( $values );
		$temp->type = 'list';
		$temp->class = @$this->field->class;
		
		$this->fields( $temp );
		
		$temp->{'show-column-names'} = @$this->field->{'show-column-names'};
		
		$temp->{'add-btn'} = new stdClass();
		$temp->{'add-btn'}->name = @$this->field->{'add-name'} ? @$this->field->{'add-name'} : "Adicionar";
		
		$temp->{'remove-btn'} = new stdClass();
		$temp->{'remove-btn'}->name = @$this->field->{'remove-name'} ? @$this->field->{'remove-name'} : "Remover";
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['list'] = 'ListField';

?>