<?php

class SearchtextField extends SimpletextField
{
	protected function 
	public function search( $values )
	{
		$value = @$values[$this->fieldName];
		$value = $this->prepareValue( $value );
		
		$where = $wglue = '';
		if( $value )
		{
			for( $this->field->fields AS $key => $field )
			{
				$column = $request->column( $field );
				print_r( $column[field]->from().'.'.$column[field]->column() );
			}
		}
		else
		{
			return null;
		}
	}
}

global $__CMSFields;
$__CMSFields['searchtext'] = 'SearchtextField';

?>