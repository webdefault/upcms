<?php

load_lib_file( 'cms/fields/simpletext' );

class ImageTitleField extends SimpletextField
{
	public function listView( $id, $values )
	{
		$temp = new stdClass();
		$temp->type = 'image-title';
		$temp->value = $values[$this->fieldName];
		$temp->image = HOME.'service/slir/w50xh50-c1x1/'.$this->field->folder.'/'.$values[$this->field->image];
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['imagetitle'] = 'ImageTitleField';

?>