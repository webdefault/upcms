<?php

load_lib_file( 'cms/fields/simpletext' );

class OnoffField extends SimpletextField
{
	public function insert( $values )
	{
		$value = @$values[$this->fieldName];
		$value = $value == 'true' ? 'on' : 'off';
		$this->request->setColumn( $this->cfrom, @$this->field->column, $this->fieldName, $this->field->{$value}->value, $this, MatrixQuery::SET );
	}

	public function update( $values )
	{
		$value = @$values[$this->fieldName];
		$value = $value == 'true' ? 'on' : 'off';
		$this->request->setColumn( $this->cfrom, @$this->field->column, $this->fieldName, $this->field->{$value}->value, $this, MatrixQuery::SET );
	}
	
	protected function applyValidations( $target )
	{
		if( @$this->field->validation && @$this->field->validation->onChange == true )
		{
			if( !@$target->events )
				$target->events = new stdClass();
			
			$id = @$this->mapId[$this->mapName] ? '/'.$this->mapId[$this->mapName] : '';
			$target->events->change = array(
				array( 'validation', 'edit-content/validate/'.$this->mapName.$id, 'post', $this->fieldName ) );
		}
	}
	
	public function listView( $id, $values )
	{
		$value = $values[$this->fieldName];
		
		$temp = new stdClass();
		$temp->type = 'text';
		
		if( $value == $this->field->on->value )
		{
			$temp->{'list-class'} = $this->field->on->class;
			$temp->value = $this->field->on->name;
		}
		else
		{
			$temp->{'list-class'} = $this->field->off->class;
			$temp->value = $this->field->off->name;
		}
		// $temp->value = $value;
		
		return $temp;
	}
	
	public function editView( $values )
	{
		$temp = new stdClass();
		
		$temp->static = parse_bracket_instructions( @$this->field->static, $values );
		
		if( !$temp->static )
		{
			$temp->value = @$values[$this->fieldName];
			$temp->type = 'onoff';
		}
		else
		{
			$temp->value = @$values[$this->fieldName] ? 'Sim' : 'Não';
			$temp->type = 'staticsimpletext';
		}
		
		// print_r( $temp );
		
		$temp->{'list-class'} = parse_bracket_instructions( @$this->field->{'list-class'}, $values );
		$temp->id = $this->fieldName;
		$temp->title = $this->field->title;
		$temp->display = parse_bracket_instructions( @$this->field->display, $values );
		$temp->static = parse_bracket_instructions( @$this->field->static, $values );
		$temp->help = @$this->field->help;
		$temp->valid = @$this->field->validate_field;
		$temp->pre = @$this->field->pre;
		$temp->pos = @$this->field->pos;
		$temp->readonly = @$this->field->readonly;
		
		$this->applyValidations( $temp );
		
		$temp->on = @$this->field->on;
		$temp->off = @$this->field->off;
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['onoff'] = 'OnoffField';

?>