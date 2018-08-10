<?php

class SuperSelect
{
	public $columns, $where, $limit, $offset, $from, $orderby, $joins, $nickname, $groupby;
	public $myKey, $parentKey, $count, $joinType, $custom;

	function __construct( $from )
	{
		$this->custom = array();
		$this->columns = array();
		$this->limit = array();
		$this->joins = array();
		$this->orderby = array();
		$this->where = '';
		$this->whereParents = 0;
		$this->from = $from;

		$this->offset = $this->limit = 
		$this->nickname = $this->myKey = $this->count = 
		$this->joinType = NULL;
		$this->groupby = NULL;
	}

	function getSQL()
	{
		// if( $this->whereParents != 0 ) throw new Exception( 'There is an open parenthesis.' );
		$sql = 'SELECT ';
		$glue = '';
		
		$as = ( $this->nickname != NULL ? $this->nickname : $this->from ).'.';

		if( $this->count != NULL )
		{
			$sql .= $this->count;

			$glue = ', ';
		}

		foreach( $this->columns as $column )
		{
			if( $column instanceof SuperSelect )
			{
				$sql .= $glue.'( '.$column->getSQL().' )';
			}
			else if( is_array( $column ) )
			{
				$sql .= $glue.$column[0];
			}
			else
			{
				$sql .= $glue.$as.$column;
			}

			$glue = ', ';
		}

		$joining = '';
		if( count( $this->joins ) > 0 )
		{
			$mas = $as;
			// $joining = '';
			/*$jlist = array();

			$i = 0;
			$keys = array_keys($this->joins);
			$get = $keys[$i];
			while( $get != NULL )
			{
				$target = $this->joins[$key];
				$target->parentKey

				if( )
			}*/
			// print_r( $this->joins );
			foreach( $this->joins AS $join )
			{
				$as = ( @$join->nickname != NULL ? $join->nickname : $join->from ).'.';
				
				foreach( $join->columns as $column )
				{
					if( $column instanceof SuperSelect )
					{
						$sql .= $glue.'( '.$column->getSQL().' )';
					}
					else if( is_array( $column ) )
					{
						$sql .= $glue.$column[0];
					}
					else
					{
						$sql .= $glue.$as.$column;
					}

					$glue = ', ';
					
					$glue = ', ';
				}

				$joining .= ' '.($join->joinType != NULL ? $join->joinType.' ' : 'LEFT ').'JOIN '.$join->from;
				if( $join->nickname != NULL ) $joining .= ' AS '.$join->nickname;
				$joining .= ' ON '.$as.$join->myKey.' = '.$join->parentKey;
			}
		}

		$sql .= ' FROM '.$this->from;
		if( $this->nickname != NULL ) $sql .= ' AS '.$this->nickname;

		$sql .= $joining;

		if( $this->where != '' )
		{
			$sql .= ' WHERE '.$this->where;
		}

		if( $this->groupby != NULL )
			$sql .= ' GROUP BY '.$this->groupby;
		
		if( count( $this->orderby ) > 0 )
		{
			$sql .= ' ORDER BY '.implode(', ', $this->orderby );
		}

		if( $this->limit != NULL )
		{
			$sql .= ' LIMIT '.$this->limit;

			if( $this->offset != NULL )
			{
				$sql .= ' OFFSET '.$this->offset;
			}
		}

		return $sql;
	}

	public function addWhere( $operator, $value )
	{
		$this->where .= ( $this->where != '' ? $operator.' ' : '' ).$value.' ';
	}
}

?>