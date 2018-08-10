<?php

/**
 * @package model
 * @classe home
 *
 */

load_lib_file( 'cms/fields' );

class UtilModel extends BaseModel
{
	function __construct( $context )
	{
		parent::__construct( $context );
	}

	public function prepareTarget( $mapName, $map, $targetName, &$target, &$name, &$table, &$fieldPath )
	{
		// Prepare the target
		if( $targetName == NULL )
		{
			$target = $map;
			$name = $mapName;
			$table = $map;
			$fieldPath = array( '' );
		}
		else
		{
			global $CMSFields;

			$target = $map->fields->{$targetName};
			$fieldPath = array( $targetName, '' );
			
			//$manager = $CMSFields[$target->type];
			// $manager->set( $sql, $map, $mapName, $field, $fieldPath, $mapId, NULL );
			$name = $target->from;//$manager->from( Operation::SET );
			// $name = $name[0];
			$table = $map->reltables->{$name};
			
		}
	}

	public function parseMapArray( $list, $default )
	{
		$temp = array();
		foreach( $list AS $value )
		{
			$t = explode( '=', $value );
			if( count( $t ) == 1 )
				$temp[$default] = $t[0];
			else
				$temp[$t[0]] = $t[1];
		}
		
		return $temp;
	}

	// Adiciona seleção de uma variavel ao $sql.
	public function parseVariable( $value, $sql, $map, $mapName, $targetName, $prefix )
	{
		if( substr( $value, 0, 1 ) === '@' )
		{
			$temp = substr( $value, 1 );
			$path = explode( '.', $temp );
			$name = str_replace( '.', '_', $temp );

			if( $path[0] != $targetName && @$sql->joins[$path[0]] == NULL )
			{
				$this->loadRelation( $path[0], $sql, $map, $mapName, $targetName );
				// echo $sql->joins['attrs'];
			}

			if( $path[0] == $name )
				$sql->columns[] = $path[1].' AS '.$prefix.'_'.$name;
			else
			{
				// print_r( $sql->joins );
				$sql->joins[$path[0]]->columns[] = $path[1].' AS '.$prefix.'_'.$name;
			}

			return NULL;
		}
		else if( substr( $value, 0, 2 ) === '\@' )
		{
			return substr( $value, 1 );
		}
	}

	function get( $mapName, $map, $mapId, $fields, $targetName = NULL, $queryPage = NULL, $orderby = NULL )
	{
		global $CMSFields;

		// print_r( $fields );
		// Prepare the target
		$this->prepareTarget( $mapName, $map, $targetName, $target, $name, $table, $fieldPath );
		
		$sql = new SuperSelect( $table->table );
		$sql->nickname = $name;

		$sql->columns[] = $table->id.' AS id';

		if( @$target->where != NULL )
		{
			foreach( $target->where AS $item )
			{
				$temp = explode( '=', $item );
				if( substr( $temp[1], 0, 1 ) != '\'' )
				{
					$path = explode( '.', $temp[1] );
					$this->loadRelation( $path[0], $sql, $map, $mapName, $name );
				}

				$sql->addWhere( 'AND', $name.'.'.$item );
			}
		}
		
		if( @$table->join != NULL )
		{
			$path = explode( '=', $table->join );
			$join = explode( '.', $path[1] );
			
			if( @$sql->joins[$join[0]] == NULL )
			{
				$from = $join[0];
				// Copy paste from loadRelation
				$rel = ( $from == $mapName ) ? $map : $map->reltables->{$from};
				
				$jsql = new SuperSelect( $rel->table );
				$jsql->nickname = $from;

				if( @$rel->join != NULL )
				{
					$join = explode( '=', $rel->join );
					$path = explode( '.', $join[1] );

					if( $path[0] != $name )
					{
						$this->loadRelation( $path[0], $sql, $map, $mapName, $name );
					}
					
					$join = explode( '=', $rel->join );
					$jsql->myKey = $join[0];
					$jsql->parentKey = $join[1];

					if( @$rel->{'join-type'} != NULL )
					{
						$jsql->joinType = $rel->{'join-type'};
					}
				}
				
				$jsql->myKey = $join[1];
				$jsql->parentKey = $name.'.'.$path[0];
				$sql->joins[$from] = $jsql;
				
				if( @$rel->where != NULL )
				{
					foreach( $rel->where AS $item )
					{
						$sql->addWhere( 'AND', $from.'.'.$item );
					}
				}
			}
		}
		
		// Get the fields
		// print_r( $fields );
		if( count( $fields ) == 0 ) $fields = $target->fields;
		
		foreach( $fields AS $key => $field )
		{
			$manager = $CMSFields[$field->type];
			$fieldPath[count($fieldPath)-1] = $key;

			$manager->set( $sql, $map, $mapName, $field, $fieldPath, $mapId, NULL );
			
			if( @$field->column == NULL ) $field->column = $key;

			$froms = $manager->from( Operation::SELECT );
			// print_r( $froms );
			foreach( $froms AS $from )
			{
				if( @$from != $name && @$sql->joins[$from] == NULL )
				{
					$this->loadRelation( $from, $sql, $map, $mapName, $name );
				}
			}

			if( @$field->title )
				$this->parseVariable( $field->title, $sql, $map, $mapName, $name, 'title' );

			$manager->select();
		}

		if( $mapId != NULL )
		{
			$temp = $this->parseMapArray( $mapId, $name );
			foreach( $temp AS $tab => $val )
			{
				$col = $tab == $name ? $table->id : 
					( @$map->reltables->{$tab} == NULL ? $map->id : $map->reltables->{$tab}->id );
				
				if( @$tab != $name && @$sql->joins[$tab] == NULL )
					$this->loadRelation( $tab, $sql, $map, $mapName, $name );

				$sql->addWhere( 'AND', $tab.'.'.$col.' = '.$val );
			}
		}

		$result = array();
		if( $queryPage != NULL )
		{
			$limit = Options::get( 'cms', 'list_limit' );

			$sql->count = 'count(*) AS temp_cms_total_rows';
			$temp = $this->db->select( $sql->getSQL() );
			if( $temp == NULL )
			{
				echo $sql->getSQL()."\n";
				print_r( $this->db->error() );
				exit();
			}

			$result['total'] = $temp[0]['temp_cms_total_rows'];
			$sql->count = NULL;

			$sql->limit = $limit;
			$sql->offset = $queryPage * $limit;
		}

		if( $orderby != NULL )
		{
			$otable = $name;
			$ocol = $table->id;

			if( $orderby[0] != 'id' )
			{
				$from = @$fields->{$orderby[0]}->from;
				if( $from != NULL ) $otable = $from;

				$ocol = $orderby[0];
			}

			$sql->orderby[] = $otable.'.'.$ocol.' '.$orderby[1];
		}

		$result['list'] = $this->db->select( $sql->getSQL() );

		if( $queryPage == NULL )
		{
			$result['total'] = count( $result['list'] );
		}
		
		// print_r( $sql );
		// echo $sql->getSQL();

		return $result;
	}

	public function select( $sql, $fetch = PDO::FETCH_ASSOC )
	{
		return $this->db->select( $sql, $fetch );
	}

	private function loadRelation( $from, $sql, $map, $mapName, $name )
	{
		$rel = ( $from == $mapName ) ? $map : $map->reltables->{$from};
		
		$jsql = new SuperSelect( $rel->table );
		$jsql->nickname = $from;

		if( @$rel->join != NULL )
		{
			$join = explode( '=', $rel->join );
			$path = explode( '.', $join[1] );

			if( $path[0] != $name )
			{
				$this->loadRelation( $path[0], $sql, $map, $mapName, $name );
			}
			
			$join = explode( '=', $rel->join );
			$jsql->myKey = $join[0];
			$jsql->parentKey = $join[1];

			if( @$rel->{'join-type'} != NULL )
			{
				$jsql->joinType = $rel->{'join-type'};
			}
		}

		$sql->joins[$from] = $jsql;
		
		if( @$rel->where != NULL )
		{
			foreach( $rel->where AS $item )
			{
				$sql->addWhere( 'AND', $from.'.'.$item );
			}
		}
	}

	public function loadInsertTable( $sql, $from, $rel, $map, $mapName/*, $name */)
	{
		if( @$rel->join != NULL )
		{
			$join = explode( '=', $rel->join );
			$path = explode( '.', $join[1] );

			if( @$sql->{$path[0]} == NULL /* != $name*/ )
			{
				$newrel = ( $path[0] == $mapName ) ? $map : $map->reltables->{$path[0]};

				// $from, $sql, $map, $mapName, $name
				$this->loadInsertTable( $sql, $path[0], $newrel, $map, $mapName );
			}
			// else if( @$sql->joins[$path[0]] == NULL )
				// $this->loadRelation( $path[0], $sql, $map, $mapName, $name );
		}
		//else
		//	$getme = NULL;

		global $CMSUtilModel;

		$sql->{$from} = array( 
			'from' => $rel->table,
			'columns' => array(),
			'join' => @$rel->join,
			'where' => (@$rel->where != NULL ) ? $rel->where : NULL
		);
		
		if( @$rel->defaults != NULL )
		{
			foreach( $rel->defaults AS $tkey => $default )
			{
				$sql->{$from}['columns'][$tkey] = $default;
			}
		}
	}

	public function loadUpdateTable( $sql, $from, $rel, $map, $mapName/*, $name */)
	{
		if( @$rel->join != NULL )
		{
			$join = explode( '=', $rel->join );
			$path = explode( '.', $join[1] );

			if( $path[0] != $name )
				$this->loadRelation( $path[0], $sql, $map, $mapName, $name );
			// else if( @$sql->joins[$path[0]] == NULL )
				// $this->loadRelation( $path[0], $sql, $map, $mapName, $name );
		}
		//else
		//	$getme = NULL;

		$sql->{$getme} = array( 
				'from' => $rel->table,
				'columns' => array(),
				'join' => @$rel->join,
				'where' => (@$rel->where != NULL ) ? $rel->where : NULL
			);
					
		if( @$rel->defaults != NULL )
		{
			foreach( $rel->defaults AS $tkey => $default )
			{
				$sql->{$getme}['columns'][$tkey] = $default;
			}
		}
	}

	public function loadSelectTable( $sql, $from, $rel, $map, $mapName/*, $name */)
	{
		$jsql = new SuperSelect( $rel->table );
		$jsql->nickname = $from;

		if( @$rel->join != NULL )
		{
			$join = explode( '=', $rel->join );
			$path = explode( '.', $join[1] );

			if( $path[0] != $mapName )
			{
				$newrel = ( $path[0] == $mapName ) ? $map : $map->reltables->{$path[0]};

				// $from, $sql, $map, $mapName, $name
				$this->loadSelectTable( $sql, $path[0], $newrel, $map, $mapName );
			}
			
			$join = explode( '=', $rel->join );
			$jsql->myKey = $join[0];
			$jsql->parentKey = $join[1];

			if( @$rel->{'join-type'} != NULL )
			{
				$jsql->joinType = $rel->{'join-type'};
			}
		}

		$sql->joins[$from] = $jsql;
		
		if( @$rel->where != NULL )
		{
			foreach( $rel->where AS $item )
			{
				$sql->addWhere( 'AND', $from.'.'.$item );
			}
		}
	}
}
