<?php

class TextField extends SimpletextField
{
	public function editView( $values )
	{
		$temp = parent::editView( $values );

		if( parse_bracket_instructions( @$this->field->static, $values ) )
			$temp->type = 'staticsimpletext';
		else
			$temp->type = 'text';
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['text'] = 'TextField';

?>