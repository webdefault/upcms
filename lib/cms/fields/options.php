<?php

load_lib_file( 'cms/fields/simpletext' );

class OptionsField extends SimpletextField
{
	public function listView( $id, $values )
	{
		$temp = new stdClass();
		$temp->{'list-class'} = '';
		
		$options = $this->generateAllOptions( $values );
		
		if( @$this->field->{'list-as'} == 'list' )
		{
			$temp->type = 'list';
			$temp->values = array();
		}
		else
		{
			$temp->type = 'text';
			$temp->value = '';
		}
		
		// print_r( $options );
		//print_r( $values );
		$vals = explode( ',', $values[$this->fieldName] );
		$glue = '';
		// print_r( $vals );
		foreach( $vals AS $v )
		{
			$a = $this->optionFromValue( $options, $v ); 
			
			if( @$a )
			{
				if( @$this->field->{'list-as'} == 'list' )
				{
					$item = new stdClass();
					$item->value = $a->title;
					if( isset( $a->{'list-class'} ) ) $item->{'list-class'} = $a->{'list-class'};
					$temp->values[] = $item;
				}
				else
				{
					$temp->value .= $glue.$a->title;
					$glue = ', ';
					
					if( isset( $a->{'list-class'} ) ) 
						$temp->{'list-class'} .= ' '.$a->{'list-class'};
				}
			}
			else
			{
				//print_r( $v );
				// print_r( $options );exit;
			}
		}
		
		// print_r( ':'.$temp->value );
		
		return $temp;
	}
	
	protected function usingSetupTable()
	{
		return @$this->field->subtype != 'rows';
	}
	
	protected function optionFromValue( $options, $value )
	{
		foreach( $options as $option )
		{
			if( $value == $option->value )
			{
				return $option;
			}
		}
	}
	
	protected function loadDynamicOptions( &$options, $dyn, $values )
	{
		$mql = array( 'data' => array(),
					  'tables' => array(),
					  'target' => 'options',
					  'slice' => NULL,
					  'select' => array(),
					  'where' => array() );
		
		$list = $this->mapId;
		if( @$values['id'] ) $list[$this->mapName] = $values['id'];

		$opts = array();
		if( @$dyn->{'subtype'} == "multiple-columns" )
		{
			foreach( $dyn->columns AS $column )
			{
				$opts[$column] = array( NULL, NULL, MatrixQuery::GET );
			}
		}
		else
		{
			$opts[$dyn->title] = $opts[$dyn->value] = array( NULL, NULL, MatrixQuery::GET );
			
			if( @$dyn->class ) $opts[$dyn->class] = array( NULL, NULL, MatrixQuery::GET );
			
			if( @$dyn->{'list-class'} ) $opts[$dyn->{'list-class'}] = array( NULL, '', MatrixQuery::GET );
		}
		
		$mql['data']['options'] = $opts;
		
		$from = $this->table( $dyn->from );
		
		$mql['tables']['options'] = array();
		foreach( $from AS $key => $val )
		{
			$mql['tables']['options'][$key] = $val;
		}
		
		if( @$from->join )
		{
			$join = explode( '=', $from->join[0] );
			$path = explode( '.', $join[1] );
			
			$value = @$list[$path[0]];
			// print_r( $value );
			if( $value ) $mql['data']['options'][$join[0]] = array( NULL, $value );
		}
		
		if( @$from->where )
		{
			foreach( $from->where AS $key => $rule )
			{
				if( is_array( $rule ) )
				{
					$temp = array( parse_bracket_instructions( $rule[0], CMS::globalValues() ) );
					if( count( $rule ) > 1 ) $temp[] = parse_bracket_instructions( $rule[1], CMS::globalValues() );
					
					$mql['data']['options'][$key] = array( $key, $temp, MatrixQuery::WHERE_RULE );
				}
				else
				{
					$value = parse_bracket_instructions( @$rule, $values );
					if( is_numeric( $value ) ) $value = $value + 0;
					
					if( substr( $value, 0, 1 ) == '%' || substr( $value, -1, 1 ) == '%' )
					{
						$mql['data']['options'][$key] = array( $key, $value, MatrixQuery::LIKE );
						//$request->setColumn( $this->table, $this->column, $this->name, $this->value, $this, MatrixQuery::LIKE );
					}
					else
					{
						// Aplicar resoluçào diretamente no MySQL fazendo conferencia com is_numeric.
						$val = is_numeric( $value ) ? $value : str_replace( "\\%", "%", $value );
						$mql['data']['options'][$key] = array( $key, $val, MatrixQuery::EQUAL_TO );
					}
				}
				//
			}
		}
		
		/* 
		If geting a value in edited field and this field is using 
		some custom dynamic super zion query that doesn't return
		the old selected options. We have to assure it.
		*/
		// if( @$this->field->subtype == 'rows' && @$values[$this->fieldName] )
		if( isset( $values[$this->fieldName] ) )
		{
			if( count( $mql['where'] ) )
			{
				$value = $values[$this->fieldName];
				$vals = explode(',', $value);
				$value = '"'.implode('","',$vals).'"';
				if( $value !== NULL )
					$mql['where'][] = array( 'OR', 'options.'.$dyn->value.' IN ('.$value.')' );
			}
		}
		
		// print_r( MatrixQuery::select( $mql ) );
		// var_dump( $mql );
		$result = MatrixQuery::select( $mql, $this->db );
		// print_r( $result );

		if( @$dyn->{'subtype'} == "multiple-columns" )
		{
			// print_r( MatrixQuery::select( $mql ) );
			foreach( $result AS $key => $item )
			{
				$opt = new stdClass();
				$opt->title = parse_bracket_instructions( $dyn->title, $item );
				$opt->value = parse_bracket_instructions( $dyn->value, $item );
				
				if( @$dyn->class ) 
					$opt->class = parse_bracket_instructions( $dyn->class, $item );
				
				if( @$dyn->{'list-class'} ) 
					$opt->{'list-class'} = parse_bracket_instructions( $dyn->{'list-class'}, $item );
				$options[] = $opt;
			}
		}
		else
		{
			foreach( $result AS $key => $item )
			{
				$opt = new stdClass();
				$opt->title = $item[$dyn->title];
				$opt->value = $item[$dyn->value];
				
				if( @$dyn->class ) 
					$opt->class = $item[$dyn->class];
				
				if( @$dyn->{'list-class'} ) 
					$opt->{'list-class'} = $item[$dyn->{'list-class'}];
				$options[] = $opt;
			}
		}
	}
	
	protected function generateAllOptions( $values )
	{
		$options = array();

		foreach( $this->field->options AS $opt )
		{
			if( @$opt->type != NULL && $opt->type == 'dynamic' )
				$this->loadDynamicOptions( $options, $opt, $values );
			else
			{
				$temp = new stdClass();

				foreach( $opt AS $k => $v ) 
				{
					$temp->{$k} = $v;
					// if( is_numeric( $v ) ) $temp->{$k} += 0;
				}
				
				$options[] = $temp;
			}
		}
		
		return $options;
	}
	
	public function select( $quick = false )
	{
		if( $this->isSelectable( array() ) )
		{
			if( @$this->field->subtype == 'columns' )
			{
				$options = $this->generateAllOptions( array() );
				
				foreach( $options AS $option )
				{
					$from = @$option->from ? $option->from : $this->from();
					
					$custom = new CustomField( $from, $option->value, $option->value, NULL );
					$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
					
					$this->request->setupTable( $this->mapName, $this->map, $from );
				}
			}
			else if( @$this->field->subtype == 'rows' )
			{
				$from = $this->from();
				// $options = $this->generateAllOptions( array() );
				$sql = 'GROUP_CONCAT(DISTINCT '.$from.'.'.$this->column().' SEPARATOR \',\')';
				$this->request->setCustomColumn( $this->fieldName, $sql );
				
				$table = $this->table( $from );
				$sql = 'GROUP_CONCAT(DISTINCT '.$from.'.'.$table->id.' SEPARATOR \',\')';
				$this->request->setCustomColumn( $this->fieldName.'_ids', $sql );
				
				$this->request->setupTable( $this->mapName, $this->map, $from );
				// print_r( $sql );
				/*
				
				$relName = $this->fieldName.'_'.$num;
				$num++;
				
				$rel = new stdClass();
				$rel->id = $from->id;
				$rel->table = $from->table;
				$rel->join = $from->join;
				$this->map->reltables->{$relName} = $rel;
				
				$custom = new CustomField( $relName, $this->column(), $line['id'] );
				$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
				
				$this->request->setupTable( $this->mapName, $this->map, $relName );
				
				*/
			}
			else
				parent::select( $quick );
		}
	}
	
	public function insert( $values )
	{
		if( $this->isEditable( $values ) )
		{
			if( @$this->field->subtype == 'columns' )
			{
				$list = @$values[$this->fieldName];
				$selected = explode(',', $list );
				$options = $this->generateAllOptions( array() );
				
				foreach( $options AS $option )
				{
					$from = @$option->from ? $option->from : $this->from();
					
					$value = in_array( $option->value, $selected ) ? 1 : 0;
					$custom = new SetField( $from, $option->value, $option->value, $value );
					$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
					
					$this->request->setupTable( $this->mapName, $this->map, $from );
				}
			}
			else if( @$this->field->subtype == 'rows' )
			{
				$num = 0;
				
				$list = @$values[$this->fieldName];
				
				if( $list != "" )
				{
					$selected = explode(',', $list );
					// print_r( $selected ); print_r( $list ); exit;
					$column = $this->column();
					foreach( $selected AS $value )
					{
						$relName = $this->fieldName.'_'.$num;
						
						$from = @$option->from ? $option->from : $this->from();
						$table = @$this->table( $from );
						$this->map->reltables->{$relName} = $table;
						
						// New
						$custom = new SetField( $relName, $column, $column, $value );
						$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
						
						$this->request->setupTable( $this->mapName, $this->map, $relName );
						
						$num++;
						
						// Old	
						//$custom = new CustomField( $relName, $this->column(), $line['id'] );
						//$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
					}
				}
			}
			else
			{
				$value = @$values[$this->fieldName];
				if( $value == '' ) $value = NULL;
				// var_dump( $value );// exit;
				$this->request->setColumn( $this->cfrom, @$this->field->column, $this->fieldName, $value, $this, MatrixQuery::SET );
			}
		}
	}
	
	public function update( $values )
	{
		if( $this->isEditable( $values ) )
		{
			$from = @$option->from ? $option->from : $this->from();
			
			if( @$this->field->subtype == 'columns' )
			{
				$list = @$values[$this->fieldName];
				$selected = explode(',', $list );
				$options = $this->generateAllOptions( array() );
				
				foreach( $options AS $option )
				{
					$value = in_array( $option->value, $selected ) ? 1 : 0;
					$custom = new SetField( $from, $option->value, $option->value, $value );
					$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
					
					$this->request->setupTable( $this->mapName, $this->map, $from );
				}
			}
			else if( @$this->field->subtype == 'rows' )
			{
				$t = @$this->table( $from );
				
				// Need to get the current values, edit it and put new values
				$result = $this->currentValue();
				
				$list = @$values[$this->fieldName];
				$selected = explode(',', $list );
				
				foreach( $selected AS $key => $sel )
				{
					$index = array_search( $sel, $result );
					if( $index !== false )
					{
						unset( $selected[$key] );
						unset( $result[$index] );
					}
				}
				
				// print_r( count( $result ) );
				if( count( $selected ) > 0 )
				{
					$num = 0;
					$column = $this->column();
					
					foreach( $selected AS $sel )
					{
						$relName = $this->fieldName.'_'.$num;
						
						// Copy table
						$table = new stdClass();
						$table->id = $t->id;
						$table->table = $t->table;
						$table->join = $t->join;
						
						$this->map->reltables->{$relName} = $table;
						
						// New
						$custom = new SetField( $relName, $column, $column, $sel );
						$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
						
						if( count( $result ) > 0 )
						{
							$key = key( $result );
							$custom = new WhereField( $relName, $table->id, $table->id, $key );
							$custom->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, NULL, $this->db );
							unset( $result[$key] );
						}
						else
						{
							$table->insert = true;
						}
						
						$this->request->setupTable( $this->mapName, $this->map, $relName );
						
						$num++;
					}
				}
				
				if( count( $result ) > 0 )
				{
					$list = array_keys( $result );
					foreach( $list AS $where )
					{
						$erase = array();
						$erase[$t->id] = $where;	
						$this->db->delete( $t->table, array( array( 'id' => $where ) ) );
					}
				}
				
				// print_r( $result );exit;
			}
			else
				parent::insert( $values );
		}
	}
	
	protected function currentValue()
	{
		$cvalues = CMS::globalValue( 'CURRENT_VALUES' );
		
		$temp = $cvalues[$this->fieldName];
		if( $temp != '' )
		{
			$cids = explode( ',', $cvalues[$this->fieldName.'_ids'] );
			$cvals = explode( ',', $temp );
		
			$temp = array();
			foreach( $cids AS $key => $id )
			{
				$temp[$id] = $cvals[$key];
			}
		
			return $temp;
		}
		else
			return array();
	}

	/*protected function dynamicOptionsFrom( $targetName )
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
				$result->id = "id";
				$result->from = $targetName;
				$result->join = "";

				return $result;
			}
		}
	}*/
	
	protected function applyValidations( $target )
	{
		if( @$this->field->validation && @$this->field->validation->onChange == true )
		{
			if( !@$target->events ) 
				$target->events = new stdClass();
			
			$target->events->change = array(
				array( 'validation', 'edit-content/validate/'.$this->mapName, 'post', $this->fieldName ) );
		}
	}
	
	protected function baseEditView( $values )
	{
		$temp = parent::baseEditView( $values );
		
		$temp->options = $this->generateAllOptions( $values );
		
		$this->applyValidations( $temp );
		
		return $temp;
	}
	
	protected function baseOptionsEditView( $temp, $values )
	{
		if( @$this->field->static )
		{
			$temp->type = 'staticsimpletext';
			
			if( isset( $temp->value ) )
			{
				foreach( $temp->options AS $option )
				{
					if( $temp->value == $option->value )
					{
						$temp->value = $option->title;
						break;
					}
				}
			}
		}
		else
		{
			$temp->type = 'options';
		}
		
		if( isset( $temp->value ) )
		{
			if( @$this->field->subtype == 'columns' )
			{
				$temp->value = array();
				foreach( $temp->options AS $option )
				{
					if( @$values[$option->value] )
					{
						$temp->value[] = $option->value;
					}
				}
			}
			else if( @$this->field->subtype == 'rows' )
			{
				$temp->value = explode( ',', $temp->value );
			}
		}
	}
	
	public function editView( $values )
	{
		$temp = $this->baseEditView( $values );
		
		$temp->{'html-before'} = @$this->field->{'html-before'};
		
		if( !isset( $values[$this->fieldName] ) && @$this->field->subtype == 'column' )
			$temp->value = true;
		else
		{
			$temp->value = @$values[$this->fieldName];
			
			if( is_numeric( $temp->value ) )
			{ 
    			$temp->value += 0;
    		}
		}
		
		$this->baseOptionsEditView( $temp, $values );
		
		return $temp;
	}
	
	public function updateEditView( $values )
	{
		$temp = $this->baseEditView( $values );

		$upvals = CMS::globalValue('UPDATE_VALUES');
		
		if( @$upvals && @$upvals[$this->fieldName] )
		{
			$temp->value = $upvals[$this->fieldName];
		}
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['options'] = 'OptionsField';

?>