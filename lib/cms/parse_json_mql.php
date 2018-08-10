<?php

function parse_json_mql( $mql, $bracket_object )
{
	$object = $bracket_object;

	$nql = array(
		'target' => @$mql->target,
		'tables' => array(),
		'data' => array(),
		'select' => @$mql->select ? $mql->select : array() );

	if( @$mql->group )
	{
		$nql['group'] = array();
		foreach( $mql->group AS $group )
		{
			$nql['group'][] = parse_bracket_instructions( $group, $object );
		}
	}
	
	foreach( $mql->tables AS $key => $table )
	{
		if( @$mql->tables->{$key}->union )
		{
			$nql['tables'][$key]['union'] = array();
			foreach( $mql->tables->{$key}->union as $select )
			{
				$nql['tables'][$key]['union'][] = parse_json_mql( $select, $object );
			}
		}
		else
		{
			$t = $mql->tables->{$key}->table;
			$temp = array();

			if( is_string( $t ) )
			{
				$temp['table'] = parse_bracket_instructions( $t, $object );
				if( @$mql->tables->{$key}->{'force-load'} ) $temp['force-load'] = parse_bracket_instructions( $mql->tables->{$key}->{'force-load'}, $object );
				if( @$mql->tables->{$key}->id ) $temp['id'] = parse_bracket_instructions( $mql->tables->{$key}->id, $object );
			}
			else
			{
				$temp['table'] = parse_json_mql( $t, $object );
				if( @$mql->tables->{$key}->{'force-load'} ) $temp['force-load'] = parse_bracket_instructions( $mql->tables->{$key}->{'force-load'}, $object );
				if( @$mql->tables->{$key}->id ) $temp['id'] = parse_bracket_instructions( $mql->tables->{$key}->id, $object );
			}

			$nql['tables'][$key] = $temp;
		}

		$nql['data'][$key] = array();
		foreach( $mql->data->{$key} AS $ikey => $value )
		{
			$columns = $mql->data->{$key}->{$ikey};
			$temp = array();

			if( count( $columns ) > 0 )
			{
				// print_r( $columns );
				$temp[] = parse_bracket_instructions( $columns[0], $object );

				if( @$columns[1] != null )
				{
					if( is_array( $columns[1] ) )
					{
						$t = array();
						
						$t[] = parse_bracket_instructions( $columns[1][0], $object );
						if( @$columns[1][1] ) $t[] = parse_bracket_instructions( $columns[1][1], $object );
						
						$temp[] = $t;
					}
					else
						$temp[] = parse_bracket_instructions( $columns[1], $object );
				}
				
				else if( count( $columns ) > 2 )
					$temp[] = "";

				if( @$columns[2] != null )
					$temp[] = parse_bracket_instructions( $columns[2], $object );

				if( @$columns[3] != null )
					$temp[] = parse_bracket_instructions( $columns[3], $object );
				
				// if( !isset( $columns[2] ) ) {print_r( $columns );exit;}
				if( is_string( @$columns[2] ) )
				{
					$temp[2] = constant('MatrixQuery::'.$columns[2] );
				}
			}

			// print_r( $temp );
			// echo "\n";

			$nql['data'][$key][$ikey] = $temp;
		}

		if( @$mql->order )
		{
			$nql['order'] = array();

			foreach( $mql->order AS $value )
				$nql['order'][] = $value;
		}
	}

	// print_r( $nql );
	return $nql;
}

?>