<?php

class UniqueValidator extends NotEmptyValidator
{
	public function validate( $fieldName, $field, $value, $rule )
	{
		/*
		 * types: 
		 * full-query: uses the entire query to check if it's unique
		 * table-query: uses the table query removing joins
		 * table: just look if in the table exists anyother with the same value
		 */
		$type = isset( $rule->type ) ? $rule->type : 'table';
		
		$request = $this->createRequest();
		$mql = $request->matrixQueryForSelect();
		
		
		$field = $request->column( $fieldName )['field'];
		$mql['data'][$field->from()][$fieldName] = array( null, $value, MatrixQuery::EQUAL_TO );
		
		$temp = $mql['tables'][ $mql['target'] ];
		$id = NULL;
		
		if( @$mql['data'][ $mql['target'] ][ $temp['id'] ] )
		{
			$id = $mql['data'][ $mql['target'] ][ $temp['id'] ];
			$mql['data'][ $mql['target'] ][ $temp['id'] ] = array( null, null, MatrixQuery::GET );
		}
		
		if( $type != 'full-query' )
		{
			$from = $field->from();
			$mql['target'] = $from;
			
			foreach( $mql['tables'] AS $key => $obj )
			{
				if( $key != $from )
				{
					unset( $mql['data'][$key] );
					unset( $mql['tables'][$key] );
				}
			}
			
			$data = $mql['data'][$from];
			$mql['data'][$from] = array();
			
			$table = $from == $this->mapName ? $this->map : $this->map->reltables->{$from};
			$where = isset( $table->where ) ? $table->where : array();
			
			foreach( $data AS $key => $item )
			{
				if( 
					( $type == 'table-query' && in_array( $key, $where ) ) || 
					$key == $fieldName )
				{
					$mql['data'][$from][$key] = $item;
				}
			}
			
			$mql['data'][ $from ][ $table->id ] = array( null, '', MatrixQuery::GET );
		}
		
		$result = $this->selectMQL( $mql );
		// print_r( $mql ); exit;
		if( $result && count( (array) $result ) > 0 )
		{
			foreach( $result AS $item )
			{
				if( $item['id'] != $id ) return false;
			}
			
			return true;
		}
		else
			return true;
		return !( count( $result ) > 0 );
	}
}

global $__CMSValidators;
$__CMSValidators['unique'] = 'UniqueValidator';

?>