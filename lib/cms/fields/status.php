<?php

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
			$this->request->setColumnFlag( $this->cfrom, $this->fieldName, 1 );
	}
	
	public function listView( $id, $values )
	{
		$temp = new stdClass();
		$temp->type = 'text';
		$temp->{'list-class'} = parse_bracket_instructions( @$this->field->{'list-class'}, $values );
		$temp->pre = parse_bracket_instructions( @$this->field->pre, $values );
		$temp->pos = parse_bracket_instructions( @$this->field->pos, $values );
		
		if( @$this->field->mask )
			$temp->mask = parse_bracket_instructions( @$this->field->mask, $values );

		$temp->maskOptions = @$this->field->{'mask-options'};
		$temp->value = $values[$this->fieldName];
		
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
$__CMSFields['custom-get'] = 'CustomGetField';

?>