<?php

load_lib_file( 'cms/parse_bracket_instructions' );
load_lib_file( 'cms/validators' );

class SimpletextField implements IField
{
	protected $fieldName, $request, $mapName, $map, $path, $mapId, $field, $cfrom, $db;

	function __construct( $fieldName )
	{
		$this->fieldName = $fieldName;
		$this->defaultValidator = 'not-empty';
	}

	public function from()
	{
		return @$this->field->from ? $this->field->from : $this->mapName;
	}

	protected function prepareValue( $value )
	{
		if( @$this->field->textcase )
		{
			$textcase = strtolower( $this->field->textcase );
			if( $textcase == 'lower' )
				$value = strtolower( $value );
			else if( $textcase == 'upper' )
				$value = strtoupper( $value );
		}

		return $value;
	}
	
	protected function table( $targetName )
	{
		if( $targetName == $this->mapName )
		{
			return $this->map;
		}
		else
		{
			$from = @$this->map->reltables->{@$targetName};

			if( $from )
				return $from;
			else
			{
				$result = new stdClass();
				$result->id = 'id';
				$result->from = $targetName;
				$result->join = '';

				return $result;
			}
		}
	}

	public function set( $request, $mapName, $map, $path, $mapId, $field, $db )
	{
		$this->request = $request;
		$this->mapName = $mapName;

		$this->map = $map;
		$this->path = $path;
		$this->mapId = $mapId;
		$this->field = $field;
		$this->db = $db;
		
		$this->cfrom = $this->from();
		
		$temp = $request->setColumn( $this->cfrom, @$this->field->column, $this->fieldName, NULL, $this );
		if( $this->usingSetupTable() && !$temp )
			$request->setupTable( $mapName, $map, $this->cfrom );
	}

	protected function usingSetupTable()
	{
		return true;
	}

	public function setFrom( $values, $operation )
	{
		// $this->field->from = $values[0];
	}
	
	public function column()
	{
		return @$this->field->column ? $this->field->column : $this->fieldName;
	}
	
	public function colname()
	{
		return @$this->field->column ? $this->field->column.' AS '.$this->fieldName : $this->field->column;
	}
	
	protected function isEditable( $values )
	{
		$ignore = parse_bracket_instructions( @$this->field->ignore, $values );
		$visible = parse_bracket_instructions( @$this->field->visible, $values );
		
		$disabled = filter_var( parse_bracket_instructions( @$this->field->disabled, $values ), FILTER_VALIDATE_BOOLEAN );
		$static = parse_bracket_instructions( @$this->field->static, $values );
		
		return $static !== true && $ignore !== true && $visible != 'hidden' && $disabled != true;
	}
	
	protected function isSelectable( $values )
	{
		$ignore = parse_bracket_instructions( @$this->field->ignore, $values );
		
		return $ignore !== true;
	}

	public function select( $quick = false )
	{
		if( @$this->isSelectable( array() ) )
			$this->request->setColumnFlag( $this->cfrom, $this->fieldName, MatrixQuery::GET );
	}

	public function insert( $values )
	{
		if( $this->isEditable( $values ) )
		{
			$value = @$values[$this->fieldName];
			$value = $this->prepareValue( $value );

			$this->request->setColumn( $this->cfrom, @$this->field->column, $this->fieldName, $value, $this, MatrixQuery::SET );
		}
	}

	public function update( $values )
	{
		if( $this->isEditable( $values ) )
		{
			$value = @$values[$this->fieldName];
			$value = $this->prepareValue( $value );

			$this->request->setColumn( $this->cfrom, @$this->field->column, $this->fieldName, $value, $this, MatrixQuery::SET );
		}
	}

	public function delete()
	{
		return '';
	}
	
	public function validationUpdates()
	{
		if( @$this->field->validation && @$this->field->validation->updates )
			return $this->field->validation->updates;
		else
			return array();
	}

	public function validate( $currentValues, $values, $fullValidation = false )
	{
		$value = @$values[$this->fieldName];
		$value = $this->prepareValue( $value );
		
		if( $this->isEditable( $values ) && ( isset( $values[$this->fieldName] ) || $fullValidation ) )
		{
			$required = @$this->field->required == NULL ? false : parse_bracket_instructions( @$this->field->required, $values );
			
			if( $value == NULL ) $value = '';
			if( $required == true || $value != '' )
			{
				$validation = @$this->field->validation;
				
				if( $validation )
				{
					foreach( $validation->rules AS $key => $rule )
					{
						$result = CMSValidate( $key, $this->mapName, $this->map, $this->path, $this->mapId, $this->db, $this->fieldName, $this, $value, $rule );

						if( $result === false )
						{
							return array( 
								'success' => false,
								'icon' => $rule->icon, 
								'class' => $rule->class,
								'message' => $rule->message );
						}
					}

					if( @$validation->success && !$fullValidation )
					{
						$rule = $validation->success;

						return array( 
								'success' => true,
								'icon' => $rule->icon, 
								'class' => $rule->class,
								'message' => $rule->message );
					}
					else
					{
						return NULL;
					}
				}
				else
				{
					return NULL;
				}
			}
		}
		else
		{
			return array();
		}
	}

	public function submit( $value )
	{
		return $value;
	}
	
	public function doSelectAndSearch( $searchValues, $quick = false )
	{
		$name = $this->select( $quick );
		$whereTarget = '';

		if( @$this->field->rel != NULL )
		{
			$whereTarget = $this->field->rel.'.'.$name;
		}
		else
			$whereTarget = $this->sql->nickname.'.'.$name;

		$this->sql->custom['where'] .= 
			( $this->sql->custom['where'] == '' ? ' ( ' : ' OR ' ).
				$whereTarget.' LIKE \'%'.implode( '%', $searchValues ).'%\'';
		/*foreach( $searchValues AS $value )
		{
			
		}*/

		$this->doSelect( $quick );
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
	
	protected function applyValidations( $target )
	{
		if( @$this->field->validation && @$this->field->validation->onChange == true )
		{
			if( !@$target->events )
				$target->events = new stdClass();
			
			$id = @$this->mapId[$this->mapName] ? '/'.$this->mapId[$this->mapName] : '';
			$target->events->formChange = array(
				array( 'validation', 'edit-content/validate/'.$this->mapName.$id, 'post', $this->fieldName ) );
		}
	}
	
	protected function baseEditView( $values )
	{
		$temp = new stdClass();
		
		$temp->id = $this->fieldName;
		
		$temp->{'class'} = parse_bracket_instructions( @$this->field->{'class'}, $values );
		$temp->title = parse_bracket_instructions( @$this->field->title, $values );
		$temp->placeholder = parse_bracket_instructions( @$this->field->placeholder, $values );
		$temp->help = parse_bracket_instructions( @$this->field->help, $values );
		$temp->visible = parse_bracket_instructions( @$this->field->visible, $values );
		
		if( @$this->field->disabled )
		{
			//echo $this->fieldName.': '.$this->field->disabled.' '.filter_var( parse_bracket_instructions( @$this->field->disabled, $values ), FILTER_VALIDATE_BOOLEAN )."\n";
			$temp->disabled = filter_var( parse_bracket_instructions( @$this->field->disabled, $values ), FILTER_VALIDATE_BOOLEAN );
		}
		
		$temp->readonly = parse_bracket_instructions( @$this->field->readonly, $values );
		
		return $temp;
	}

	public function editView( $values )
	{
		$temp = $this->baseEditView( $values );

		$temp->static = parse_bracket_instructions( @$this->field->static, $values );
		
		$temp->{'html-before'} = @$this->field->{'html-before'};
		
		if( $temp->static )
			$temp->type = 'staticsimpletext';
		else
			$temp->type = 'simpletext';

		$temp->pre = parse_bracket_instructions( @$this->field->pre, $values );
		$temp->pos = parse_bracket_instructions( @$this->field->pos, $values );
		
		// Mask
		$temp->mask = parse_bracket_instructions( @$this->field->mask, $values );
		$temp->maskOptions = @$this->field->{'mask-options'};
		
		
		$temp->autocomplete = parse_bracket_instructions( @$this->field->autocomplete, $values );
		$temp->textcase = parse_bracket_instructions( @$this->field->textcase, $values );
		
		$temp->value = @$values[$this->fieldName];
		$this->applyValidations( $temp );
		
		return $temp;
	}
	
	public function updateEditView( $values )
	{
		$temp = $this->baseEditView( $values );
		
		$temp->pre = parse_bracket_instructions( @$this->field->pre, $values );
		$temp->pos = parse_bracket_instructions( @$this->field->pos, $values );
		
		// Mask
		$temp->mask = parse_bracket_instructions( @$this->field->mask, $values );
		$temp->maskOptions = @$this->field->{'mask-options'};
		
		
		$temp->autocomplete = parse_bracket_instructions( @$this->field->autocomplete, $values );
		$temp->textcase = parse_bracket_instructions( @$this->field->textcase, $values );
		
		$upvals = CMS::globalValue('UPDATE_VALUES');
		
		if( @$upvals && @$upvals[$this->fieldName] )
		{
			$temp->value = $upvals[$this->fieldName];
		}
		
		return $temp;
	}
	
	protected function searchMode( $value )
	{
		switch( $this->field->{'search-mode'} )
		{
			case 'like':
				return 'LIKE \'%'.$value.'%\'';
				break;
				
			case 'equal':
				return '= \''.$value.'\'';
				break;
				
			case 'more-equal':
				return '>= \''.$value.'\'';
				break;
				
			case 'less-equal':
				return '<= \''.$value.'\'';
				break;
			
			default:
				return null;
				break;
		}
	}
	
	public function search( $request, $values )
	{
		$value = @$values[$this->fieldName];
		$value = $this->prepareValue( $value );
		
		$where = $wglue = '';
		if( $value !== NULL )
		{
			foreach( $this->field->fields AS $field )
			{
				$column = $request->column( $field );
				
				// print_r( $field );exit;
				$where .= $wglue.$column['field']->from().'.'.$column['field']->column().' '.$this->searchMode( $value );
				$wglue .= ' OR ';
			}
			
			return '('.$where.')';
		}
		else
		{
			return null;
		}
	}
}

global $__CMSFields;
$__CMSFields['simpletext'] = 'SimpletextField';

?>