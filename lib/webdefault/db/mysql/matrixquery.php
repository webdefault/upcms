<?php

class MatrixQuery
{
	const IGNORE		= 0;
	const GET			= 1;
	const SET			= 2;
	const SMART_SET		= 3;
	const SET_RULE		= 4;
	const WHERE_RULE	= 5;
	const EQUAL_TO		= 6;
	
	const LIKE			= 7;
	const NOT_LIKE		= 8;
	
	const LEFT_JOIN		= 9;
	const RIGHT_JOIN	= 10;
	const INNER_JOIN 	= 11;
	const OUTER_JOIN 	= 12;
	const JOIN			= 13;
	
	private static $debug;
	
	public static function printQuery( $matrixQuery, $returnString = false )
	{
		header("Content-Type: text/html");
		
		$result = '<table cellpadding="5" border="1">';
		
		if( count( @$matrixQuery['select'] ) )
		{
			$result .= '<tr><td><strong>CUSTOM</strong></td>';
			
			foreach( $matrixQuery['select'] AS $item )
			{
				$result .= '<td>'.$item.'</td>';
			}
			
			$result .= '</tr>';
		}
		
		foreach( $matrixQuery['data'] AS $tkey => $table )
		{
			$result .= '<tr><td valign="top"><strong>'.$tkey.'</strong><br />';
			if( @$matrixQuery['tables'][$tkey]['union'] )
			{
				$result .= 'union:';
				$glue = '';
				foreach( $matrixQuery['tables'][$tkey]['union'] AS $select )
				{
					$result .= $glue.self::printQuery( $select, true );
					$glue = '+';
				}
				$result .= '</td>';
			}
			else
			{
				$result .= 'table: '.
					$matrixQuery['tables'][$tkey]['table'].'<br />id: '.
					$matrixQuery['tables'][$tkey]['id'].'</td>';
			}
			
			foreach( $table AS $ckey => $column )
			{
				$result .= '<td valign="top">'.( @$column[0] ? $column[0] : $ckey ).'<br />'.@$column[1].' AS '.$ckey.'</td>';
			}
			
			$result .= '</tr>';
		}
		
		if( $returnString )
			return $result.'</table>';
		else
			echo $result.'</table>';
	}
	
	private static function columnSelection( &$cglue, $lend, $column, $ckey, $tkey )
	{
		$asset = '';
		if( @$column[0] != NULL )
		{
			$asset = ' '.$ckey;
			$cckey = $column[0];
		}
		else
		{
			$cckey = $ckey;
		}
		
		$temp = $cglue.$lend.$tkey.'.'.$cckey.$asset;
		$cglue = ', ';
		
		return $temp;
	}

	private static function getRealPath( $data, $val, &$link )
	{
		$link = false;
		$path = explode( '.', $val );
		
		if( isset( $data[$path[0]] ) && isset( $data[$path[0]][$path[1]] ) )
		{
			$column = $data[$path[0]][$path[1]];
			
			if( count( $column ) > 1 && $column[0] )
			{
				$link = true;
				return $path[0].'.'.$column[0];
			}
			else
				return $val;
		}
		else
		{
			if( count( $path ) > 1 ) $link = true;
			// Problem related to join orders found here and fixed by adding $link = true;
			// Need to check if all situations are this way solved.
			// $link = true;
			return $val;
		}
	}
	
	public static function select( $matrixQuery, $db = NULL )
	{
		if( Config::ENV == 'development' )
		{
			self::$debug = array();
		}
		// self::printQuery( $matrixQuery );exit; echo '<br />';
		// print_r( $matrixQuery );exit;
		
		$lend = ''; $ltab = '';
		if( MATRIX_QUERY_DEBUG )
		{
			$lend = "\n    "; $ltab = '	';
		}
		
		$binds = array();
		$sql = 'SELECT ';
		if( isset( $matrixQuery['options']['count'] ) && $matrixQuery['options']['count'] == true )
		{
			$sql .= 'SQL_CALC_FOUND_ROWS ';
		}
		
		$join = array();
		
		$from = $where = $order =
		$fglue = $cglue = $jglue = $wglue = $oglue = '';
		$loadedTables = array();
		$appliedTables = array();
		
		$target = @$matrixQuery['tables'][ $matrixQuery['target'] ];
		if( $target == NULL )
		{
			echo 'MQL::target \''.$matrixQuery['target'].'\' cannot be null';exit;
		}
		
		if( isset( $target['union'] ) )
		{
			$from = '(';
			$glue = '';
			foreach( $target['union'] AS $select )
			{
				$temp = MatrixQuery::select( $select );

				if( is_array( $temp ) )
				{
					// TODO: Change to some default PHP function
					// to join arrays.
					foreach( $temp[1] AS $item ) $binds[] = $item;

					$from .= $glue.$lend.$temp[0];
				}
				else
				{
					$from .= $glue.$lend.$temp;
				}

				$glue = ' UNION ';
			}
			$from .= ') '.$matrixQuery['target'];
			$loadedTables[$matrixQuery['target']] = array();
			$appliedTables[] = $matrixQuery['target'];
			$fglue = ', ';
		}
		else
		{
			// print_r( $target )."\n";
			if( is_string( $target['table'] ) )
			{
				$from = $target['table'].' '.$matrixQuery['target'];
				$fglue = ', ';
			}
			else
			{
				$temp = MatrixQuery::select( $target['table'] );

				if( is_array( $temp ) )
				{
					// TODO: Change to some default PHP function
					// to join arrays.
					foreach( $temp[1] AS $item ) $binds[] = $item;

					$tsql = $temp[0];
				}
				else
				{
					$tsql = $temp;
				}

				$from = '('.$tsql.') '.$matrixQuery['target'];
				$fglue = ', ';
			}

			$loadedTables[$matrixQuery['target']] = array();
			$appliedTables[] = $matrixQuery['target'];
		}
		
		foreach( $matrixQuery['data'] AS $tkey => $table )
		{
			$opts = $matrixQuery['tables'][$tkey];
			if( isset( $opts['order'] ) )
			{
				// print_r( $opts['order'] );
				foreach( $opts['order'] AS $k => $o )
				{
					$order .= $oglue.$tkey.'.'.$k.' '.$o;
					// print_r( $order );
					$oglue = ', ';
				}
			}
			
			foreach( $table AS $ckey => $column )
			{
				// echo $tkey.".".$ckey." ";
				
				if( $column[0] == NULL ) $column[0] = $ckey;
				
				switch( @$column[2] )
				{
					case MatrixQuery::GET:
						$sql .= self::columnSelection( $cglue, $lend, $column, $ckey, $tkey );
						break;
						
					case MatrixQuery::SET:
						$sql .= $lend.$cglue.$lend.'('.$column[1].') AS '.$ckey;
						$cglue = ', ';
						break;
						
					case MatrixQuery::SET_RULE:
						if( @$column[1][1] ) $binds[] = $column[1][1];
						$sql .= $lend.$cglue.$lend.'('.$column[1][0].') AS '.$ckey;
						$cglue = ', ';
						break;
						
					case MatrixQuery::WHERE_RULE:
						$binds[] = $column[1][1];
						$where .= $wglue.'('.$column[1][0].')';
						$wglue = ' AND ';
						break;
						
					case MatrixQuery::EQUAL_TO:
						$sql   .= self::columnSelection( $cglue, $lend, $column, $ckey, $tkey );
						$where .= $wglue.$tkey.'.'.$column[0].'=?';
						
						$binds[] = $column[1];
						$wglue  = ' AND ';
						break;
						
					case MatrixQuery::LIKE:
						$sql   .= self::columnSelection( $cglue, $lend, $column, $ckey, $tkey );
						$where .= $wglue.$tkey.'.'.$column[0].' LIKE ?';
						$binds[] = $val;
						$wglue  = ' AND ';
						break;
						
					case MatrixQuery::LEFT_JOIN:
					case MatrixQuery::RIGHT_JOIN:
					case MatrixQuery::INNER_JOIN:
					case MatrixQuery::OUTER_JOIN:
					case MatrixQuery::JOIN:
						if( is_string( $opts['table'] ) )
						{
							$tableJoin = $opts['table'];
						}
						else
						{
							$temp = MatrixQuery::select( $target['table'] );

							if( is_array( $temp ) )
							{
								// TODO: Change to some default PHP function
								// to join arrays.
								foreach( $temp[1] AS $item ) $binds[] = $item;

								$tsql = $temp[0];
							}
							else
							{
								$tsql = $temp;
							}

							$tableJoin = '('.$tsql.')';
						}
						
						$jtype = self::joinType( $column[2] );
						
						$rule = $tkey.'.'.$column[0].'='.self::getRealPath( $matrixQuery['data'], $column[1], $link );
						// echo $rule."\n";
						$on = explode( '.', $column[1] );
						
						if( @$join[$tkey] == NULL )
						{
							$loadedTables[$tkey] = array();
							
							$join[$tkey] = $jtype.$tableJoin.
								 ' AS '.$tkey.' ON '.$rule;
						}
						else
						{
							$join[$tkey] .= ' AND '.$rule;
						}
						
						if( $link )
						{
							$loadedTables[$tkey][] = $on[0];
						}
						
						break;
				}
			}
		}

		$keys = array_keys( $loadedTables );
		// print_r( $keys );
		foreach( $matrixQuery['tables'] AS $key => $table )
		{
			//echo "loading: ".in_array( $key, $loadedTables )." && ";
			// print_r( @$table['force-load'] == true );
			
			// TODO: Need to check why force-load is/was needed.
			// if( !in_array( $key, $loadedTables ) && @$table['force-load'] == true )
			if( !in_array( $key, $keys ) )
			{
				// echo "MatrixQuery 344: ".$key."\n";
				$from .= $fglue.$lend.$table['table'].' AS '.$key;
				$loadedTables[$key] = array();
				$appliedTables[] = $key;
				
			}
		}

		// Custom select, workaround for today.
		if( count( @$matrixQuery['select'] ) )
		{
			foreach( $matrixQuery['select'] AS $item )
			{
				$sql .= $cglue.$lend.$item;
			}
		}
		
		// Custom where, workaround for today.
		if( isset( $matrixQuery['where'] ) && count( $matrixQuery['where'] ) )
		{
			foreach( $matrixQuery['where'] AS $item )
			{
				if( $wglue != '' ) $wglue = ' '.$item[0].' ';
				$where .= $wglue.$item[1];
				$wglue = ' ';
			}
		}
		
		if( isset( $matrixQuery['custom-where'] ) )
		{
			$where .= $wglue.'('.$matrixQuery['custom-where'].')';
		}
		
		// sort joins
		$sortedJoins = "";
		$keys = array_keys($join);
		$i = 0;
		$d = 0;
		
		while( count( $appliedTables ) < count( $loadedTables ) )
		{
			// No more creativity for vars names. target, table
			$t = $keys[$i];
			
			// echo $t."\n";
			if( !in_array( $t, $appliedTables ) )
			{
				$load = $loadedTables[$t];
				$canload = true;
				foreach( $load AS $l )
				{
					if( !in_array( $l, $appliedTables ) )
					{
						// echo $t." ".$l."\n";
						$canload = false;
						break;
					}
				}
				
				if( $canload )
				{
					$sortedJoins .= ' '.$join[$t];
					$appliedTables[] = $t;
				}
			}
			
			$d++;
			$i++;
			
			if( $d > 100 ) 
			{
				debug_print_backtrace();
				trigger_error( "Sorting join tables could not load all of them.\nNot loaded: ".
					implode( ', ', array_diff( $keys, $appliedTables ) )."\nLoaded: ".implode(', ', $appliedTables ), E_USER_ERROR);
				exit;
			}
			
			if( $i == count( $keys ) ) $i = 0;
		}
		
		$sql .= ' '.$lend.'FROM '.$from.$sortedJoins;
		if( $where != '' ) $sql .= ' '.$lend.'WHERE '.$where;

		if( @$matrixQuery['group'] ) $sql .= ' '.$lend.'GROUP BY '.join( ',', $matrixQuery['group'] );
		
		if( @$matrixQuery['order'] )
		{
			$order = $oglue = '';
			foreach( $matrixQuery['order'] AS $k => $o )
			{
				$order .= $oglue.$k.' '.$o;
				$oglue = ', ';
			}
		}
		
		if( $order != '' ) $sql .= $lend.' ORDER BY '.$order;
		
		if( @$matrixQuery['slice'] != NULL )
		{
			$sql .= ' '.$lend.'LIMIT '.$matrixQuery['slice'][1];
			$sql .= ' OFFSET '.$matrixQuery['slice'][0];
		}
		
		// print_r( $sql );
		
		/*if( count( $binds ) > 0 )
		{
			echo $sql."\n";
			print_r( $binds );
			exit;
		}//*/
		
		if( $db == NULL )
		{
			if( count( $binds ) > 0 )
			{
				// print_r( $sql );
				return array( $sql, $binds );
			}
			else
				return $sql;
		}
		else
		{
			/*while( $pos = array_search( 0, $binds ) )
			{
				unset( $binds[$pos] );
			}*/
			// print_r( $db->select( $sql, $binds ) ); exit;
			return $db->select( $sql, $binds );
		}
	}
	
	protected static function joinType( $type )
	{
		switch( $type )
		{
			case self::LEFT_JOIN:
				return ' LEFT JOIN ';
			
			case self::RIGHT_JOIN:
				return ' RIGHT JOIN ';
				
			case self::INNER_JOIN:
				return ' INNER JOIN ';
				
			case self::OUTER_JOIN:
				return ' OUTER JOIN ';
				
			case self::JOIN:
				return ' JOIN ';
		}
	}

	protected static function findColumnValue( $column, $data, &$linkage = false )
	{
		switch( $column[2] )
		{
			case MatrixQuery::SET:
			case MatrixQuery::LIKE:
			case MatrixQuery::SET_RULE:
			case MatrixQuery::WHERE_RULE:
				return $column[1];
				
			case MatrixQuery::EQUAL_TO:	
				return $column[1];
			
			case MatrixQuery::SMART_SET:
			case MatrixQuery::LEFT_JOIN:
			case MatrixQuery::RIGHT_JOIN:
			case MatrixQuery::INNER_JOIN:
			case MatrixQuery::OUTER_JOIN:
			case MatrixQuery::JOIN:
				$fchar = substr( $column[1], 0, 1 );
				
				if( $fchar == '"' )
				{
					return substr( $column[1], 1, -1 );
				}
				else if( ctype_digit( $fchar ) || $fchar == '-' || $fchar == '+' )
				{
					return $column[1];
				}
				else if( !ctype_alnum( $fchar ) )
				{
					//$equal = explode( $column )
				}
				else
				{
					$linkage = true;
					$path = explode( '.', $column[1] );
					$ncol = @$data[$path[0]] ? @$data[$path[0]][$path[1]] : NULL;
					
					//echo "ncol: "; print_r( $path ); echo "\n";
					//echo "res: ".$ncol."\n";

					if( $ncol != NULL )
					{
						return MatrixQuery::findColumnValue( $ncol, $data );
					}

					return NULL;
				}
				break;
				
			/*
				$linkage = true;
				$path = explode( '.', $column[1] );
				$ncol = @$data[$path[0]] ? @$data[$path[0]][$path[1]] : NULL;
				
				//echo "ncol: "; print_r( $path ); echo "\n";
				//echo "res: ".$ncol."\n";

				if( $ncol != NULL )
				{
					return MatrixQuery::findColumnValue( $ncol, $data );
				}

				return NULL;
			*/
		}
	}

	public static function insert( $matrixQuery, $db )
	{
		// self::printQuery( $matrixQuery );exit;
		// var_dump( $matrixQuery );exit;
		/*
		$ids = array();
		$data = $matrixQuery['data'];
		$tables = array_keys( $data );
		$queue = array_keys( $tables );

		$i = 0;
		$bug = 10;
		while( count( $queue ) > 0 && $bug > 0 )
		{
			$targetName = $tables[$queue[$i]];
			$target = $data[$targetName];
			$columns = array();
			$cancreate = true;

			foreach( $target AS $ckey => $column )
			{
				if( count( $column ) > 3 )
					$value = array( $column[2], MatrixQuery::findColumnValue( $column[3], $data, $link ) );
				else
				{
					$value = MatrixQuery::findColumnValue( $column[1], $data, $link );
				}
				
				if( $value === NULL )
				{
					// echo $column[1];
					$cancreate = false;
					break;
				}
				else
				{
					$columns[(@$column[0] ? $column[0] : $ckey)] = $value;
				}
			}

			if( $cancreate )
			{
				// print_r( $columns );
				$id = $db->insert( $matrixQuery['tables'][ $targetName ]['table'], array( $columns ) );
				$data[$targetName]['id'] = array( 'id', '"'.$id.'"', 2 );

				$ids[$targetName] = $id;
				array_splice( $queue, $i, 1 );
			}/*
			else
			{
				echo "cannot\n\n";
				print_r( $columns );
			}* /
			
			$bug--;
			$i++;
			if( $i >= count( $queue ) ) $i = 0;
		}
		
		if( $bug == 0 )
		{
			print_r( $data );
			throw new Exception( "MatrixQuery::insert : Max steps in while rotine reach" );
		}*/
		
		foreach( $matrixQuery['tables'] AS $key => $table )
		{
			$matrixQuery['tables'][$key]['insert'] = true;
		}
		
		return self::update( $matrixQuery, $db );

		// return $ids;
	}

	public static function update( $matrixQuery, $db )
	{
		if( Config::ENV == 'development' )
		{
			self::$debug = array();
		}
		// print_r( $matrixQuery );exit;
		
		$ids = array();
		$data = $matrixQuery['data'];
		$tables = array_keys( $data );
		$queue = array_keys( $tables );
		
		$t = 0;
		$i = 0;
		while( count( $queue ) > 0 && $t < count( $queue ) )
		{
			$targetName = $tables[$queue[$i]];
			$target = $data[$targetName];
			$columns = array();
			$where = array();
			$cancreate = true;
			$link = false;
			
			// echo $targetName."\n";
			// print_r( $target );echo "\n";
			foreach( $target AS $ckey => $column )
			{
				$link = false;
				$name = @$column[0] && $column[0] != "" ? $column[0] : $ckey;
				
				$value = MatrixQuery::findColumnValue( $column, $data, $link );
				
				// echo $ckey." ";print_r( $column )."\n";
				if( $value === NULL && $column[1] !== NULL )
				{
					// echo $ckey." can't created ".$column[1]."\n";
					$cancreate = false;
					break;
				}
				else
				{
					switch( $column[2] )
					{
						case MatrixQuery::SET:
						case MatrixQuery::SMART_SET:
						// case MatrixQuery::SMART_SET:
							// if( $name == 'using_person_data' ) var_dump( $value );
							$columns[$name] = $value;
							break;
							
						case MatrixQuery::SET_RULE:
							$columns[$name] = $value;
							break;
							
						case MatrixQuery::EQUAL_TO:
						case MatrixQuery::LEFT_JOIN:
						case MatrixQuery::RIGHT_JOIN:
						case MatrixQuery::INNER_JOIN:
						case MatrixQuery::OUTER_JOIN:
						case MatrixQuery::JOIN:
						
						case MatrixQuery::WHERE_RULE:
							if( @$matrixQuery['tables'][ $targetName ]['insert'] )
								$columns[$name] = $value;
							else
								$where[$name] = $value;
							break;
							
						case MatrixQuery::IGNORE:
							print_r( 'ignored' );
							break;
							
						/*
						Not supported yet.
						case MatrixQuery::LIKE:
							$sql   .= self::columnSelection( $cglue, $lend, $column, $ckey, $tkey );
							$where .= $wglue.$tkey.'.'.$column[0].' LIKE ?';
							$binds[] = $val;
							$wglue  = ' AND ';
							break;*/
					}
				}
				
				//if( count( $column ) > 3 )
				// $value = array( $column[2], MatrixQuery::findColumnValue( $column[3], $data, $link ) );
				//else
				//{
				//	$value = MatrixQuery::findColumnValue( $column[1], $data, $link );
				//}
				
			}
			
			//echo "Can create? : ".$cancreate."\n";
			// print_r( array( 'table' => $targetName, 'cols' => $columns, 'where' => $where ) );//exit;
			if( $cancreate && count( $columns ) > 0 )
			{
				if( Config::ENV == 'development' )
				{
					self::$debug[] = array( 'table' => $targetName, 'cols' => $columns, 'where' => $where );
				}
				// print_r( array( 'table' => $targetName, 'cols' => $columns, 'where' => $where ) );//exit;
				if( !@$matrixQuery['tables'][ $targetName ]['insert'] && count( $where ) > 0 )
				{
					/*
					echo $matrixQuery['tables'][ $targetName ]['table']."\n\n";
					print_r( $columns );
					echo "\n\n";
					print_r( $where );
					echo "\n\n\n\n";
					// */
					
					$sql = 'SELECT * 
							FROM '.$matrixQuery['tables'][ $targetName ]['table'].'
							WHERE ';
					$binds = array();
					
					$glue = '';
					foreach( $where as $k => $v )
					{
						$sql .= $glue.$k.( is_array( $v ) ? $v[0] : ' = ? ' );
						
						if( is_array( $v ) )
						{
							if( count( $v ) > 1 )
							{
								$binds[] = $v[1];
							}
						}
						else
						{
							$binds[] = $v;
						}
						
						$glue = ' AND ';
					}
					
					$result = $db->select( $sql, $binds );
					if( count( $result ) > 0 )
					{
						$db->update( $matrixQuery['tables'][ $targetName ]['table'], $columns, $where );
					}
					else if( $result !== false )
					{
						foreach( $where AS $k => $v )
						{
							$columns[$k] = $v;
						}
						
						$id = $db->insert( $matrixQuery['tables'][ $targetName ]['table'], array( $columns ) );
					}
				}
				else
				{
					// print_r( $columns );
					$id = $db->insert( $matrixQuery['tables'][ $targetName ]['table'], array( $columns ) );
					$data[$targetName]['id'] = array( 'id', $id, MatrixQuery::EQUAL_TO );
					$ids[$targetName] = $id;
				}
				
				array_splice( $queue, $i, 1 );
				$t = 0;
			}
			else
			{
				$t++;
			}
			
			$i++;
			if( $i >= count( $queue ) ) $i = 0;
		}
		
		return $ids;
	}
	
	public static function delete( $matrixQuery, $db )
	{
		if( Config::ENV == 'development' )
		{
			self::$debug = array();
		}
		
		foreach( $matrixQuery['data'] AS $tkey => $table )
		{
			$where = array();
			$candelete = true;
			
			foreach( $table AS $ckey => $column )
			{
				$name = @$column[0] && $column[0] != "" ? $column[0] : $ckey;
				$value = MatrixQuery::findColumnValue( $column, $matrixQuery['data'], $link );
				
				if( $value === NULL )
				{
					// echo $ckey." can't created ".$column[1]."\n";
					$candelete = false;
					break;
				}
				else
				{
					switch( $column[2] )
					{
						case MatrixQuery::EQUAL_TO:
						case MatrixQuery::LEFT_JOIN:
						case MatrixQuery::RIGHT_JOIN:
						case MatrixQuery::INNER_JOIN:
						case MatrixQuery::OUTER_JOIN:
						case MatrixQuery::JOIN:
							$where[$name] = $value;
							
						/*
						Not supported yet.
						case MatrixQuery::LIKE:
							$sql   .= self::columnSelection( $cglue, $lend, $column, $ckey, $tkey );
							$where .= $wglue.$tkey.'.'.$column[0].' LIKE ?';
							$binds[] = $val;
							$wglue  = ' AND ';
							break;*/
					}
				}
			}
			
			if( $candelete )
			{
				if( Config::ENV == 'development' )
				{
					self::$debug[] = array( 'table' => $tkey, 'where' => $where, 'deleted' => "true" );
				}
				
				$db->delete( $matrixQuery['tables'][$tkey]['table'], array($where) );
			}
			else
			{
				if( Config::ENV == 'development' )
				{
					self::$debug[] = array( 'table' => $tkey, 'where' => $where, 'deleted' => "false" );
				}
			}
		}
	}
	
	public static function getDebug()
	{
		return self::$debug;
	}
}

?>