<?php

load_lib_file( 'cms/parse_bracket_instructions' );

class ActionsField extends SimpletextField
{
	/*public function from( $operation )
	{
		return @$this->field->from ? array( $this->field->from ) : array( $this->mapName );
	}

	public function setFrom( $values, $operation )
	{
		$this->field->from = $values[0];
	}*/
	
	public function select( $quick = false )
	{
		// return false;
	}

	public function doSelectAndSearch( $searchValues, $quick = false )
	{
		/*$name = $this->select( $quick );
		$whereTarget = '';		if( @$this->field->rel != NULL )
		{
			$whereTarget = $this->field->rel.'.'.$name;
		}
		else
			$whereTarget = $this->sql->nickname.'.'.$name;		$this->sql->custom['where'] .= 
			( $this->sql->custom['where'] == '' ? ' ( ' : ' OR ' ).
				$whereTarget.' LIKE \'%'.implode( '%', $searchValues ).'%\'';
	*foreach( $searchValues AS $value )
		{
			
		}		$this->doSelect( $quick );*/
	}

	public function insert( $values )
	{
		/*$value = @$values[end($this->fieldPath)];
	 	print_r( $value );		if( $value != NULL )
		{
			$froms = $this->from( Operation::INSERT );
			$this->sql->{$froms[0]}['columns'][$this->field->column] = $this->submit( $value );
		}*/
	}

	public function update( $values )
	{
		/**$value = @$values[end($this->fieldPath)];
		
	print_r( $values );
		if( $value != NULL )
		{
			$froms = $this->from( Operation::UPDATE );
			$this->sql->{$froms[0]}['columns'][$this->field->column] = $this->submit( $value );
		}*/
	}

	public function delete()
	{
		return '';
	}

	public function validate( $curValues, $newValues, $force = false )
	{
		/*load_lib_file( 'cmvalidators' );
		*$value = @$values[end($fieldPath)];		global $CMSValidators;		$required = @$this->target->required == NULL ? false : $this->target->required;		if( $value == NULL ) $value = '';		if( $required == true || $value != '' )
		{
			$validator = ( @$this->target->validator != NULL ? 
				$CMSValidators[$this->target->validator] :
				$CMSValidators[$this->defaultValidator] );			if( !$validator( $value ) )
				$this->sql->invalids[] = end($fieldPath);
		}
		else*/
		return NULL;
	}

	public function submit( $value )
	{
		return $value;
	}
	
	public function listView( $id, $values )
	{
		$temp = new stdClass();
		$temp->type = 'custom';
		$temp->id = $id;
		$temp->subs = array();
		$temp->title = $this->field->title;

		$dropdown = new stdClass();
		$dropdown->type = 'btn-group';
		// $dropdown->class = 'pull-right';
		$dropdown->title = $this->field->title;
		$dropdown->{'dropdown-class'} = parse_bracket_instructions( @$this->field->{'dropdown-class'}, $values );
		$dropdown->options = array();
		$temp->subs[] = $dropdown;
		
		if( @$this->field->buttons )
		{
			$dropdown->subs = array();
			
			foreach( $this->field->buttons AS $btn )
			{
				$item = new stdClass();
				$item->id = $id;
				$item->type = 'btn';
				$item->class = 'btn-sm ';
				if( @$btn->class ) $item->class .= parse_bracket_instructions( $btn->class, $values );

				$item->title = parse_bracket_instructions( $btn->title, $values );
				$item->icon = parse_bracket_instructions( @$btn->icon, $values );
				$item->url = parse_bracket_instructions( @$btn->url, $values );
				
				$dropdown->subs[] = $item;
			}
		}
		
		if( @$this->field->dropdown )
		{
			$dropdown->type = 'dropdown';
			foreach( $this->field->dropdown AS $option )
			{
				$item = new stdClass();
				$item->id = $id;
				$item->type = $option->type;
				$item->title = parse_bracket_instructions( @$option->title, $values );
				$item->icon = parse_bracket_instructions( @$option->icon, $values );
				$item->url = parse_bracket_instructions( @$option->url, $values );

				$item->class = parse_bracket_instructions( @$option->class, $values );
				$dropdown->options[] = $item;
			}
		}
		return $temp;
	}
	
	public function editView( $values )
	{
		$temp = $this->listView( NULL, $values );
		$temp->type = 'element';
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['actions'] = 'ActionsField';

?>