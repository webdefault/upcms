<?php

load_lib_file( 'cms/parse_bracket_instructions' );

function create_view_object( $obj, $request, $values, $fieldHandler = NULL )
{
	if( @$obj->type == 'field' )
	{
		$column = $request->column( $obj->target );
		
		if( $column ) 
		{
			$result = $column['field']->editView( @$values );
			
			if( $fieldHandler )	$result = $fieldHandler( $result );
			if( isset($obj->{'size-md'} ) ) $result->{'size-md'} = $obj->{'size-md'};
			if( isset($obj->{'class'} ) ) $result->{'class'} = $obj->{'class'};
		}
		else
		{
			$result = NULL;
			CMS::exitWithMessage( 'error', 'Layout error: Column \''.$obj->target.'\' was not found', $debug = NULL );
		}
		
		return $result;
	}
	else
	{
		$temp = new stdClass();

		foreach( $obj AS $key => $prop )
		{
			if( $key == 'subs' )
			{
				$temp->subs = array();
				foreach( $prop AS $subObj )
				{
					$temp->subs[] = create_view_object( $subObj, $request, $values, $fieldHandler );
				}
			}
			else if( is_array( $prop ) )
			{
				$temp->{$key} = array_parse_bracket_instructions( $prop, array() );
			}
			else if( is_object( $prop ) )
			{
				$temp->{$key} = object_parse_bracket_instructions( $prop, array() );
			}
			else
			{
				$temp->{$key} = parse_bracket_instructions( $prop, array() );
			}
		}

		return $temp;
	}
}

function update_view_object( $results, $updates, $obj, $request, $values, $fieldHandler = NULL )
{
	if( @$obj->type == 'field' )
	{
		if( $updates === true || in_array( $obj->target, $updates ) )
		{
			$column = $request->column( $obj->target );
			$result = $column['field']->updateEditView( @$values );
			
			if( $result )
			{
				if( $fieldHandler )	$fieldHandler( $obj->target, $result );
			
				$results->{$obj->target} = $result;
			}
		}
	}
	else
	{
		$temp = new stdClass();
		$subs = NULL;
		
		foreach( $obj AS $key => $prop )
		{
			if( $key == 'subs' )
			{
				$subs = $prop;
			}
			else if( is_array( $prop ) )
			{
				$temp->{$key} = array_parse_bracket_instructions( $prop, array() );
			}
			else if( is_object( $prop ) )
			{
				$temp->{$key} = object_parse_bracket_instructions( $prop, array() );
			}
			else
			{
				$temp->{$key} = parse_bracket_instructions( $prop, array() );
			}
		}
		
		if( @$temp->id )
		{
			if( $updates === true || in_array( $temp->id, $updates ) )
				$results->{$temp->id} = $temp;
		}
		
		if( $subs != NULL )
		{
			foreach( $subs AS $subObj )
			{
				update_view_object( $results, $updates, $subObj, $request, $values, $fieldHandler );
			}
		}
	}
}

?>