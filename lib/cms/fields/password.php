<?php

load_lib_file( 'cms/fields/simpletext' );

class PasswordField extends SimpletextField
{
	public function listView( $id, $values )
	{
		$temp = new stdClass();
		$temp->type = 'text';
		$temp->{'list-class'} = parse_bracket_instructions( @$this->field->{'list-class'}, $values );
		$temp->mask = $this->field->mask;
		$temp->value = '**********';
		
		return $temp;
	}

	public function select( $quick = false )
	{
		//$this->request->setColumnFlag( $this->cfrom, $this->fieldName, 1 );
	}

	/* Obrigado ter column. Se não tiver column, não será inserido ou atualizado */
	public function insert( $values )
	{
		if( @$this->field->column )
		{
			$value = @$values[$this->fieldName];
			$value = sha1( $value.CMSConfig::USERS_PASSWORD_HASH );
			$this->request->setColumn( $this->cfrom, @$this->field->column, $this->fieldName, '"'.$value.'"', $this, 1 );
		}
	}

	public function update( $values )
	{
		if( @$this->field->column )
		{
			$value = @$values[$this->fieldName];
			$value = sha1( $value.CMSConfig::USERS_PASSWORD_HASH );
			$this->request->setColumn( $this->cfrom, @$this->field->column, $this->fieldName, '"'.$value.'"', $this, 1 );
		}
	}

	public function validate( $currentValues, $values, $force = false )
	{
		$value = @$values[$this->fieldName];

		if( @$this->field->{'confirm-password'} )
		{
			$rule = $this->field->{'confirm-password'};
			if( $value != @$values[$rule->target] )
			{
				return array( 
						'icon' => $rule->icon, 
						'class' => $rule->class,
						'message' => $rule->message );
			}
		}
		
		return parent::validate( $currentValues, $values, $force );
	}
	
	public function editView( $values )
	{
		$temp = new stdClass();

		$temp->type = 'password';
		$temp->id = $this->fieldName;
		$temp->mask = @$this->field->mask;
		$temp->maskOptions = @$this->field->{'mask-options'};
		$temp->title = parse_bracket_instructions( @$this->field->title, $values );
		$temp->placeholder = @$this->field->placeholder;
		// $temp->display = @$this->field->display;
		$temp->help = @$this->field->help;
		$temp->pre = @$this->field->pre;
		$temp->pos = @$this->field->pos;
		$temp->readonly = @$this->field->readonly;
		$temp->value = @$values[$this->fieldName];

		if( @$this->field->decimal ) 
		{
			$temp->value = floor( $temp->value * 100 );
		}

		$this->applyValidations( $temp );
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['password'] = 'PasswordField';

?>