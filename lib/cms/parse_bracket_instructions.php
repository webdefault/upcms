<?php

function object_parse_bracket_instructions( $prop, $obj )
{
	$result = new stdClass();

	foreach( $prop as $key => $value )
	{
		if( is_array( $value ) )
			$result->{$key} = array_parse_bracket_instructions( $value, $obj );
		else if( is_object( $value ) )
			$result->{$key} = object_parse_bracket_instructions( $value, $obj );
		else
			$result->{$key} = parse_bracket_instructions( $value, $obj );
	}

	return $result;
}

function array_parse_bracket_instructions( $prop, $obj )
{
	$result = array();

	foreach( $prop as $key => $value )
	{
		if( is_array( $value ) )
			$result[$key] = array_parse_bracket_instructions( $value, $obj );
		else if( is_object( $value ) )
			$result[$key] = object_parse_bracket_instructions( $value, $obj );
		else
			$result[$key] = parse_bracket_instructions( $value, $obj );
	}

	return $result;
}

function parse_bracket_instructions( $text, $values )
{
	// if( !is_string( $text ) ) print_r( $text );
	if( is_array($text) ) debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

	while( preg_match( '/\{([^}]+)\}/im', $text, $matches, PREG_OFFSET_CAPTURE ) !== 0 )
	{
		$string = substr( $matches[0][0], 1, -1);
		$string = trim( $string );
		
		$temp = explode( '?', $string );
		if( count( $temp ) > 1 )
		{
			$result = __internal_parse_if( $temp, $values );
		}
		else
			$result = __internal_parse_value( $temp[0], $values );
		
		if( is_array( $result ) )
		{
			return $result;
		}
		else
		{
			$text = substr( $text, 0, $matches[0][1] ).$result.substr( $text, $matches[0][1] + strlen( $matches[0][0] ) );
		}
	}
	
	return $text;
}

function __internal_parse_if( $text, $values )
{
	$temp = explode( '==', $text[0], 2 );
	$temp1 = explode( '>=', $text[0], 2 );
	$temp2 = explode( '<=', $text[0], 2 );
	$temp3 = explode( '@=', $text[0], 2 );
	$temp4 = explode( '!=', $text[0], 2 );
	if( count( $temp ) > 1 )
	{
		$op = 1;
	}
	else if( count( $temp1 ) > 1 )
	{
		$temp = $temp1;
		$op = 2;
	}
	else if( count( $temp2 ) > 1 )
	{
		$temp = $temp2;
		$op = 3;
	}
	else if( count( $temp3 ) > 1 )
	{
		$temp = $temp3;
		$op = 4;
	}
	else if( count( $temp4 ) > 1 )
	{
		$temp = $temp4;
		$op = 5;
	}
	

	$next = explode( '?', $text[1], 2 );
	$end = explode( ':', $text[1], 2 );
	$hasif = count( $next ) > 1;
	
	// if( $temp[0] == 'pago' ) print_r( $values );
	
	$a = __internal_parse_value( $temp[0], $values );
	if( count( $temp ) > 1 )
	{
		$b = __internal_parse_value( $temp[1], $values );
		
		if( ( $a == $b && ( $op == 1 || $op == 4 ) ) || ( $a >= $b && $op == 2 ) || ( $a <= $b && $op == 3 ) || ( $a === NULL && $op == 4 ) || ( $a != $b && $op == 5 ) )
		{
			return __internal_parse_value( $hasif ? $next[0] : $end[0], $values );
		}
		else
		{
			if( $hasif )
				return __internal_parse_if( $next[1], $values );
			else
				return __internal_parse_value( $end[1], $values );
		}
	}
	else if( $a )
	{
		__internal_parse_value( $hasif ? $next[0] : $end[0], $values );
	}
	else
	{
		if( $hasif )
			return __internal_parse_if( $next[1], $values );
		else
			return __internal_parse_value( $end[1], $values );
	}
}

function __internal_parse_value( $text, $values )
{
	$text = trim( $text );
	$fchar = substr( $text, 0, 1 );
	
	if( $text == 'false' )
		return false;
	else if( $text == 'true' )
		return true;
	else if( strtolower( $text ) == 'null' )
		return NULL;
	else if( $fchar == '\'' || $fchar == '"' )
		return substr( $text, 1, -1 );
	else if( is_numeric( $text ) )
	{
		return $text + 0;
	}
	else if( strpos( $text, '.' ) !== false )
	{
		$path = explode( '.', $text );
		
		$t = __get( $values, $path[0] );
		if( $t != null && __get( $t, $path[1] ) )
			return __get( $t, $path[1] );
		else
		{
			//$r = CMS::globalValue( $path[0], $path[1] );
			//return isset( $r[$path[1]] ) ? $r[$path[1]] : NULL;
			return CMS::globalValue( $path[0], $path[1] );
		}
	}
	else
	{
		if( __get( $values, $text ) != NULL )
			return __get( $values, $text );
		else
		{
			return CMS::globalValue( $text );
		}
	}
}

function __get( $obj, $index )
{
	if( is_array( $obj ) )
		return @$obj[$index];
	else
		return @$obj->{$index};
}

?>