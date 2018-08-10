<?php

load_lib_file( 'cms/fields/simpletext' );

class NumberField extends SimpletextField
{
	protected function prepareValue( $value )
	{
		if( @$this->field->subtype == 'real' || 
			@$this->field->subtype == 'decimal' || 
			@$this->field->subtype == 'money' )
		{
			$value = doubleval( $value );
		}
		else
		{
			$value = intval( $value );
		}
		
		return $value;
	}
	
	public function insert( $values )
	{
		if( @$this->field->static != true )
		{
			$value = @$values[$this->fieldName];
			$value = $this->prepareValue( $value );
			
			
			// $value = preg_replace( '/[^0-9]/', '', $value );
			// if( @$this->field->decimal ) $value /= 100;
			$this->request->setColumn( $this->cfrom, @$this->field->column, $this->fieldName, $value, $this, MatrixQuery::SET );
		}
	}

	public function update( $values )
	{
		if( @$this->field->static != true )
		{
			$value = @$values[$this->fieldName];
			$value = $this->prepareValue( $value );

			// if( @$this->field->subtype == 'DECIMAL' ) $value /= 100;
			// print_r( $values[$this->fieldName] );
			$this->request->setColumn( $this->cfrom, @$this->field->column, $this->fieldName, $value, $this, MatrixQuery::SET );
		}
	}
	
	public function listView( $id, $values )
	{
		$temp = new stdClass();
		$temp->type = 'number';
		$temp->pre = @$this->field->pre;
		$temp->pos = @$this->field->pos;
		$temp->subtype = @$this->field->subtype;
		$temp->thousands = @$this->field->thousands;
		$temp->decimal = @$this->field->decimal;
		$temp->allowZero = @$this->field->{'allow-zero'};
		$temp->allowNegative = @$this->field->{'allow-negative'};
		$temp->affixesStay = @$this->field->{'affixes-stay'};
		$temp->showPositiveSign = @$this->field->{'show-positive-sign'};
		$temp->{'state-classes'} = @$this->field->{'state-classes'};
		$temp->{'list-class'} = parse_bracket_instructions( @$this->field->{'list-class'}, $values );
		// $temp->mask = @$this->field->mask;
		// $temp->maskOptions = @$this->field->{'mask-options'};
		$temp->value = $values[$this->fieldName];
		
		return $temp;
	}

	public function editView( $values )
	{
		$temp = new stdClass();

		if( @$this->field->static )
			$temp->type = 'staticnumber';
		else
			$temp->type = 'number';

		$temp->id = $this->fieldName;
		$temp->subtype = @$this->field->subtype;
		$temp->thousands = @$this->field->thousands;
		$temp->decimal = @$this->field->decimal;
		$temp->allowZero = @$this->field->{'allow-zero'};
		$temp->allowNegative = @$this->field->{'allow-negative'};
		$temp->affixesStay = @$this->field->{'affixes-stay'};
		$temp->showPositiveSign = @$this->field->{'show-positive-sign'};
		$temp->{'state-classes'} = @$this->field->{'state-classes'};
		$temp->readonly = @$this->field->readonly;
		$temp->visible = parse_bracket_instructions( @$this->field->visible, $values );
		// $temp->mask = @$this->field->mask;
		// $temp->maskOptions = @$this->field->{'mask-options'};
		$temp->{'class'} = parse_bracket_instructions( @$this->field->{'class'}, $values );
		$temp->title = parse_bracket_instructions( @$this->field->title, $values );
		$temp->placeholder = @$this->field->placeholder;
		// $temp->display = @$this->field->display;
		$temp->help = @$this->field->help;
		$temp->pre = @$this->field->pre;
		$temp->pos = @$this->field->pos;
		$temp->value = @$values[$this->fieldName];

		$this->applyValidations( $temp );
		
		return $temp;
	}
	
	public function updateEditView( $values )
	{
		$temp = new stdClass();

		/*if( @$this->field->static )
			$temp->type = 'staticnumber';
		else
			$temp->type = 'number';*/

		// $temp->id = $this->fieldName;
		$temp->subtype = @$this->field->subtype;
		$temp->thousands = @$this->field->thousands;
		$temp->decimal = @$this->field->decimal;
		$temp->allowZero = @$this->field->{'allow-zero'};
		$temp->allowNegative = @$this->field->{'allow-negative'};
		$temp->affixesStay = @$this->field->{'affixes-stay'};
		$temp->showPositiveSign = @$this->field->{'show-positive-sign'};
		$temp->{'state-classes'} = @$this->field->{'state-classes'};
		$temp->readonly = @$this->field->readonly;
		// $temp->mask = @$this->field->mask;
		// $temp->maskOptions = @$this->field->{'mask-options'};
		$temp->{'class'} = parse_bracket_instructions( @$this->field->{'class'}, $values );
		$temp->title = parse_bracket_instructions( @$this->field->title, $values );
		$temp->placeholder = @$this->field->placeholder;
		// $temp->display = @$this->field->display;
		$temp->help = @$this->field->help;
		$temp->pre = @$this->field->pre;
		$temp->pos = @$this->field->pos;
		// $temp->value = @$values[$this->fieldName];

		// $this->applyValidations( $temp );
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['number'] = 'NumberField';

?>