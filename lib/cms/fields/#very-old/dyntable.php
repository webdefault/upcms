<?php

load_lib_file( 'cms/fields/simpletext' );

class DyntableField extends GridField
{
	public function prepareSelect()
	{
		$column = @$this->table->column == NULL ? 'x' : $this->table->column;
		$line = @$this->table->line == NULL ? 'y' : $this->table->line;
		$value = @$this->table->value == NULL ? 'value' : $this->table->value;

		array_push( $this->sql->columns, $column.' AS x' );
		array_push( $this->sql->columns, $line.' AS y' );
		array_push( $this->sql->columns, $value.' AS value' );
		$this->sql->orderby = array( 'y ASC', 'x ASC' );
	}
}

global $__CMSFields;
$__CMSFields['dyntable'] = 'DyntableField';

?>