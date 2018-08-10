<?php

load_lib_file( 'cms/fields/simpletext' );

class DatetimeField extends SimpletextField
{
	protected function getDateString( $value )
	{
		$names = @$this->field->names;
		if( $names )
		{
			$date = date_create( $value );
			$cdate = date_format( $date, 'Ymd' );
			if( @$names->today && $cdate == date( 'Ymd' ) )
				return $names->today;
			else if( @$names->yesterday && $cdate == date( 'Ymd', strtotime( '-1 days' ) ) )
				return $names->yesterday;
			else if( @$names->tomorrow && $cdate == date( 'Ymd', strtotime( '+1 days' ) ) )
				return $names->tomorrow;
		}
		
		return date_format( date_create( $value ), $this->field->format );
	}

	public function editView( $values )
	{
		$temp = parent::editView( $values );

		if( parse_bracket_instructions( @$this->field->static, $values ) )
		{
			$temp->type = 'staticsimpletext';
			
			if( isset( $this->field->format ) && isset( $temp->value ) )
				$temp->value = date( $this->field->format, strtotime( $temp->value ) );
		}
		else
			$temp->type = 'datetime';
		
		$temp->{'min-date'} = parse_bracket_instructions( @$this->field->{'min-date'}, $values );
		$temp->{'max-date'} = parse_bracket_instructions( @$this->field->{'max-date'}, $values );
		
		return $temp;
	}
	
	public function listView( $id, $values )
	{
		$temp = parent::listView( $id, $values );
		/*$temp = new stdClass();
		$temp->type = 'text';
		$temp->pre = @$this->field->pre;
		$temp->pos = @$this->field->pos;*/
		$temp->value = $this->getDateString( $values[$this->fieldName] );
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['datetime'] = 'DatetimeField';

?>