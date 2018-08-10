<?php

load_lib_file( 'cms/fields/simpletext' );

class HiddenField extends SimpletextField
{
	
	public function listView( $id, $values )
	{
		return NULL;
	}
	
	public function update( $values )
	{
		$value = NULL;
		$flag = @$this->field->save === false ? MatrixQuery::IGNORE : MatrixQuery::SMART_SET;
		
		if( isset( $this->field->value ) )
		{
			$value = parse_bracket_instructions( $this->field->value, $values );
		}
		else if( @$values[$this->fieldName] )
		{
			$value = '"'.$values[$this->fieldName].'"';
		}
		else if( isset( $this->field->default ) )
		{
			$value = $this->field->default;
		}
		
		// echo $this->fieldName.' value: '.$value."\n";
		
		if( $value !== NULL )
		{
			$this->request->setColumn( 
				$this->from(), 
				@$this->column(), 
				$this->fieldName, 
				$value, 
				$this, 
				$flag );
		}
	}
	
	public function insert( $values )
	{
		$value = NULL;
		
		$flag = @$this->field->save === false ? MatrixQuery::IGNORE : MatrixQuery::SMART_SET;
		
		if( isset( $this->field->value ) )
		{
			$value = parse_bracket_instructions( $this->field->value, $values );
		}
		else if( @$values[$this->fieldName] )
		{
			$value = '"'.$values[$this->fieldName].'"';
		}
		else if( isset( $this->field->default ) )
		{
			$value = $this->field->default;
		}
		
		if( isset( $value ) )
		{
			// var_dump( $value );exit;
			$this->request->setColumn( 
				$this->from(), 
				@$this->column(), 
				$this->fieldName, 
				$value, 
				$this, 
				$flag );
		}
	}
	
	public function editView( $values )
	{
		$temp = new stdClass();
		$temp->type = 'hidden';
		$temp->id = $this->fieldName;
		$temp->value = @$values[$this->fieldName];
		
		return $temp;
	}
	
	public function updateEditView( $values )
	{
		$temp = new stdClass();
		
		$temp->type = 'hidden';
		$temp->id = $this->fieldName;
		
		$upvals = CMS::globalValue('UPDATE_VALUES');
		
		if( @$upvals && @$upvals[$this->fieldName] )
		{
			$temp->value = $upvals[$this->fieldName];
		}
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['hidden'] = 'HiddenField';

?>