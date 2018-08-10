<?php

load_lib_file( 'cms/fields/simpletext' );

class DateTimeField extends SimpletextField
{
	public function submit( $value )
	{
		$formato = ( @$this->field->{'date-format'} == NULL ) ? 'Y-m-d' : $this->field->{'date-format'};
		$formato_time = ( @$this->field->{'time-format'} != NULL ) ? @$this->field->{'time-format'} : 'h:i';
		
		$date = DateTime::createFromFormat( $formato.' '.$formato_time, $value[0].' '.$value[1] );
		return $date->format('Y-m-d H:i').':00';
	}

	public function doSelectAndSearch( $searchValues, $quick = false )
	{
		$this->doSelect( $quick );
	}
}

global $__CMSFields;
$__CMSFields['datetime'] = 'DateTimeField';

?>