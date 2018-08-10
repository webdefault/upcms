<?php

load_lib_file( 'cms/fields/options' );
load_lib_file( 'cms/parse_bracket_instructions' );

class SelectField extends OptionsField
{
	protected function applyUpdateOnSearch( $target )
	{
		if( @$this->field->{'update-on-search'} )
		{
			if( !@$target->events )
				$target->events = new stdClass();
			
			$id = @$this->mapId[$this->mapName] ? '/'.$this->mapId[$this->mapName] : '';
			$target->events->search = array(
				array( 'validation', 'edit-content/update-form/'.$this->mapName.$id, 'post', $this->fieldName ) );
		}
	}
	
	public function editView( $values )
	{
		$temp = parent::editView( $values );

		if( @$this->field->static )
		{
			$temp->type = 'staticsimpletext';
		}
		else
		{
			$temp->type = 'select';
		}
		
		$temp->multiple = parse_bracket_instructions( @$this->field->multiple, $values );
		$temp->searchable = parse_bracket_instructions( @$this->field->searchable, $values );
		
		$this->applyUpdateOnSearch( $temp );
		
		// if( is_numeric( $temp->value ) ) echo $this->fieldName." > ".$temp->value;
		return $temp;
	}
	
	public function updateEditView( $values )
	{
		$temp = parent::updateEditView( $values );

		if( @$this->field->static )
		{
			$temp->type = 'staticsimpletext';
		}
		else
		{
			$temp->type = 'select';
		}
		
		$this->applyUpdateOnSearch( $temp );
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['select'] = 'SelectField';

?>