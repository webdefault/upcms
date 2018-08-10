<?php

load_lib_file( 'cms/parse_bracket_instructions' );

class Request
{
	public $search;
	
	private $tables, $tableOpts, $columns,
			$target, $mapId, $customColumns,
			$mapName, $map, $slice;
	
	private function __construct( $target )
	{
		$this->target = $target;
		$this->tables = array();
		$this->tablesOpts = array();
		$this->customColumns = array();
		$this->columns = array();
	}
	
	public function setTarget( $target )
	{
		$this->target = $target;
	}
	
	public function target()
	{
		return $this->target;
	}
	
	public function slice( $offset, $length )
	{
		$this->slice = array( $offset, $length );
	}
	
	public function setColumn( $table, $column, $name, $value, $field, $flag = 0 )
	{
		$result = true;
		if( @$this->tables[$table] == NULL )
		{
			$this->tables[$table] = array();
			$result = false;
		}
		
		$this->columns[$name] = $this->tables[$table][$name] = array(
			'value' => $value,
			'column' => $column,
			'field' => $field,
			'flag' => $flag );
		
		return $result;
	}

	public function columnsName()
	{
		return array_keys( $this->columns );
	}
	
	public function visibleColumnsName()
	{
		$cols = $this->columnsName();
		foreach( $cols AS $k => $col )
		{
			if( substr( $col, 0, 8 ) == '_hidden_' )
				unset( $cols[$k] );
		}
		
		return $cols;
	}
	
	public function table( $name )
	{
		return @$this->tablesOpts[$name];
	}
	
	public function column( $name )
	{
		if( isset( $this->columns[$name] ) )
			return $this->columns[$name];
		else
			return NULL;
	}
	
	public function setupTable( $mapName, $map, $targetName )
	{
		if( !@$this->tablesOpts[$targetName] )
		{
			$reltable = $targetName == $mapName ? $map : @$map->reltables->{$targetName};
			
			if( $reltable )
			{
				if( @$reltable->where != NULL )
				{
					foreach( $reltable->where AS $key => $item )
					{
						$field = new WhereField( $targetName, $key.rand(0,9999), $key, $item );
						$field->set( $this, $mapName, $map, NULL, NULL, NULL );
					}
				}
				
				if( @$reltable->join != NULL && $targetName != $mapName )
				{
					foreach( $reltable->join AS $item )
					{
						$joinType = null;
						$temp = explode(':', $item );
						if( count( $temp ) > 1 )
						{
							$joinType = $temp[0];
							$temp = $temp[1];
						}
						else
						{
							$temp = $temp[0];
						}
						$temp = explode( '=', $temp );
						$field = new JoinField( $targetName, $temp[0].rand(0,9999), $temp[0], $temp[1], $joinType );
						
						$field->set( $this, $mapName, $map, NULL, $this->mapId, NULL );
						
						if( substr( $temp[1], 0, 1 ) != '"' )
						{
							$join = explode( '.', $temp[1] );
							if( @$this->tables[$join[0]] == NULL )
								$this->setupTable( $mapName, $map, $join[0] );
						}
					}
				}
				
				if( @$reltable->table !== false )
				{
					$this->tablesOpts[$targetName] = array( 
						'id' => @$reltable->id, 
						'table' => $reltable->table,
						'insert' => @$reltable->insert,
						'update' => @$reltable->update,
						'order' => @$reltable->order );
				}
			}
		}
	}
	
	public function tableOptions( $tableName )
	{
		return @$this->tablesOpts[$tableName];
	}
	/*
	public function tableGroupBy( $tableName, $groupBy )
	{
		if( array_search( $groupBy, $this->tableOpts[$tableName]['group'] ) === FALSE )
			$this->tableOpts[$tableName]['group'][] = $groupBy;
	}
	*/
	public function setColumnFlag( $table, $name, $flag )
	{
		$this->tables[$table][$name]['flag'] = $flag;
	}
	
	public function columnValue( $table, $name )
	{
		return $this->tables[$table][$name]['value'];
	}

	public function columnField( $table, $name )
	{
		return $this->tables[$table][$name]['field'];
	}

	public function setCustomColumn( $name, $value )
	{
		$this->customColumns[$name] = $value;
	}
	
	public function removeColumn( $table, $name )
	{
		unset( $this->tables[$table][$name] );
	}

	protected function matrixQuery()
	{
		$result = array( 'data' => array(),
						 'tables' => array(),
						 'target' => $this->target,
						 'slice' => @$this->slice,
						 'select' => array() );
		
		foreach( $this->customColumns AS $key => $select )
		{
			$result['select'][] = '('.$select.') AS '.$key;
		}
		
		foreach( $this->tables AS $tkey => $table )
		{
			if( @$this->tablesOpts[$tkey] != NULL )
			{
				$result['data'][$tkey] = array();
				$result['tables'][$tkey] = $this->tablesOpts[$tkey];
				
				foreach( $table AS $ckey => $column )
				{
					if( $column['flag'] != 0 )
					{
						$value = @$column['value'] !== NULL ? @$column['value'] : NULL;
						
						// if( @$column['column'] == null ) print_r( $ckey );
						$result['data'][$tkey][$ckey] = array( 
							@$column['column'], 
							$value, 
							$column['flag'] );
						/*
						if( $column['flag'] == 3 )
						{
							$result['data'][$tkey][$ckey] = array( $column['column'], $value, '', $column['field']->joinType() );
						}
						else if( $column['flag'] == 2 )
						{
							$result['data'][$tkey][$ckey] = array( $column['column'], $value, 2 );
						}
						else
						{
							$result['data'][$tkey][$ckey] = array( $column['column'], $value );
						}
						*/
					}
				}
			}
		}
		
		// print_r( $result );exit;
		return $result;
	}
	
	public function matrixQueryForSelect( $fields = NULL )
	{
		foreach( $this->tables AS $tkey => $table )
		{
			foreach( $table AS $ckey => $column )
			{
				if( @$column['field'] != NULL )
				{
					if( $column['field'] === true || $column['field'] === false ) print_r( $column );
					
					if( $fields == NULL || in_array( $ckey, $fields ) ) $column['field']->select();
				}
			}
		}

		return $this->matrixQuery();
	}

	public function matrixQueryForInsert( $values )
	{
		foreach( $this->tables AS $tkey => $table )
		{
			foreach( $table AS $ckey => $column )
			{
				if( @$column['field'] != NULL )
				{
					$column['field']->insert( $values );
				}
			}
		}

		return $this->matrixQuery();
	}

	public function matrixQueryForUpdate( $values, $columns = NULL )
	{
		foreach( $this->tables AS $tkey => $table )
		{
			foreach( $table AS $ckey => $column )
			{
				if( @$column['field'] != NULL && ( $columns == NULL || in_array( $ckey, $columns ) ) )
				{
					$column['field']->update( $values );
				}
			}
		}

		return $this->matrixQuery();
	}
	
	public function getMapId()
	{
		return $this->mapId;
	}
	
	public function setMapId( $mapId )
	{
		$this->mapId = $mapId;
	}
	
	public static function createRequestFromTarget( $mapName, $map, $path, $mapId, $db )
	{
		$request = new Request( count( $path ) > 0 ? end( $path ) : $mapName );
		$target = $map;
		$request->setMapId( $mapId );
		
		$i = 0;
		while( count( $path ) > $i )
		{
			$target = $map->fields->{$path[$i]};
			$i++;
		}
		
		if( $mapId )
		{
			/*foreach( $mapId AS $key => $id )
			{
				$field = new CustomField( $key, 'id', $id );
				$field->set( $request, $mapName, $map, NULL, NULL, NULL );
			}*/
			
			$request->setMapId( $mapId );
		}
		
		if( isset( $map->{'force-setup-table'} ) )
		{
			foreach( $map->{'force-setup-table'} AS $t )
			{
				$request->setupTable( $mapName, $map, $t );
			}
		}
		
		$objs = array();
		// print_r( $target ); exit;
		foreach( $target->fields AS $key => $field )
		{
			$class = CMSFieldClass( $field->type );
			$obj = new $class( $key );
			
			$obj->set( $request, $mapName, $map, $path, $mapId, $field, $db );
		}
		
		if( isset( $target->search ) )
		{
			$search = new Request( count( $path ) > 0 ? end( $path ) : $mapName );
			$objs = array();
			// print_r( $target ); exit;
			foreach( $target->search AS $key => $field )
			{
				$class = CMSFieldClass( $field->type );
				$obj = new $class( $key );
				
				$obj->set( $search, $mapName, $map, $path, $mapId, $field, $db );
			}
			
			$request->search = $search;
		}
		
		if( $mapId )
		{
			foreach( $mapId AS $key => $id )
			{
				if( $request->table( $key ) && $id != NULL )
				{
					$field = new WhereField( $key, 'id', 'id', $id );
					$field->set( $request, $mapName, $map, NULL, NULL, NULL );
				}
			}
		}

		return $request;
	}
}

?>