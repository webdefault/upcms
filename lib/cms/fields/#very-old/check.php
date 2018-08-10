<?php

load_lib_file( 'cms/fields/simpletext' );

class CheckField extends SimpletextField
{
	function __construct( $fieldName )
	{
		$this->fieldName = $fieldName;
		$this->defaultValidator = 'not-empty';
	}

	public function update( $values )
	{
		$value = @$values[end($this->fieldPath)];
		
		if( $value != NULL )
		{
			$froms = $this->from( Operation::UPDATE );
			$this->sql->{$froms[0]}['columns'][$this->column()] = $this->submit( $value );
		}
	}

	public function delete()
	{
		return '';
	}

	public function validate( $curValues, $newValues )
	{
		load_lib_file( 'cms/validators' );

		$value = @$values[end($fieldPath)];

		global $CMSValidators;

		$required = @$this->target->required == NULL ? false : $this->target->required;

		if( $value == NULL ) $value = '';

		if( $required == true || $value != '' )
		{
			$validator = ( @$this->target->validator != NULL ? 
				$CMSValidators[$this->target->validator] :
				$CMSValidators[$this->defaultValidator] );

			if( !$validator( $value ) )
				return array( 'error' => 1, 'message' => 'Campo inválido.' );
		}
		else
			return NULL;
	}

	public function submit( $value )
	{
		return $value;
	}
	
	public function listView( $id, $values )
	{
		$temp = new stdClass();
		$temp->type = 'status';
		$temp->class = $this->field->status->{$values[$this->fieldName]};
		$temp->value = $value;
		
		return $temp;
	}
	
	public function editView( $values )
	{
		$temp = new stdClass();
		$temp->type = 'check';
		$temp->id = $this->fieldName;
		$temp->title = $this->field->title;
		// $temp->display = @$this->field->display;
		$temp->help = @$this->field->help;
		$temp->valid = @$this->field->validate_field;
		$temp->pre = @$this->field->pre;
		$temp->pos = @$this->field->pos;
		$temp->value = $values[$this->fieldName];
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['check'] = 'CheckField';

?>