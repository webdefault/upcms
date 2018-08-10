<?php

load_lib_file( 'cms/fields/simpletext' );

class CustomGetField extends SimpletextField
{
	public function insert( $values )
	{
		
	}

	public function update( $values )
	{
		
	}
	
	public function select( $quick = false )
	{
		if( @$this->isSelectable( array() ) )
		{
			$this->request->setCustomColumn( $this->fieldName, $this->field->get );
		}
	}
	
	protected function prepareValue( $value )
	{
		if( isset( $this->field->subtype ) )
		{
			if( $this->field->subtype == 'datetime' )
			{
				return date( $this->field->format, $value );
			}
			else
				return $value;
		}
		else
			return $value;
	}
	
	public function listView( $id, $values )
	{
		$values[$this->fieldName.'_formatted'] = $this->prepareValue( $values[$this->fieldName] );
		$temp = new stdClass();
		$temp->type = 'text';
		$temp->{'list-class'} = parse_bracket_instructions( @$this->field->{'list-class'}, $values );
		$temp->pre = parse_bracket_instructions( @$this->field->pre, $values );
		$temp->pos = parse_bracket_instructions( @$this->field->pos, $values );
		
		$temp->value = parse_bracket_instructions( @$this->field->value, $values );
		$temp->class = parse_bracket_instructions( @$this->field->class, $values );
		
		return $temp;
	}
	
	public function editView( $values )
	{
		$temp = parent::editView( $values );

		$temp->type = 'staticsimpletext';
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['customget'] = 'CustomGetField';

?>