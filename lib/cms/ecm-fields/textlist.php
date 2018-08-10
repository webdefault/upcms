<?php

class ECMTextlistField extends SimpletextField
{
	protected function usingSetupTable()
	{
		return false;
	}
	
	protected function loadOptions()
	{
		$request = Request::createRequestFromTarget( $this->field->map, CMS::mapByName( $this->field->map ), array(), array(), $this->db );
		// $mql = $request->matrixQueryForSelect();

		//print_r( $this->map );
		//$request = Request::createRequestFromTarget( $mapName, $map, $path, $ids, $this->db );
		//$mql = $request->matrixQueryForSelect();
	}

	public function select( $quick = false )
	{
		$from = $this->getFrom( @$this->cfrom );
		$sql = 'SELECT GROUP_CONCAT( '.$this->column().' SEPARATOR \''.$this->field->separator.'\' ) FROM '.$from->table.' WHERE ';
		
		$append = '';
		foreach( $from->join AS $join )
		{
			$sql .= $append.$join;
			$append = ' AND ';
		}

		$this->request->setCustomColumn( $this->fieldName, $sql );
		// print_r( $result );
		// $this->request->setColumnUsing( $this->cfrom, $this->fieldName, true );
	}

	public function insert( $values )
	{
		
	}

	protected function getFrom( $targetName )
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
	}
	
	public function listView( $id, $values )
	{
		$temp = new stdClass();
		$temp->type = 'text';
		$temp->value = $values[$this->fieldName];
		
		return $temp;
	}
	
	public function editView( $values )
	{
		$temp = new stdClass();
		$temp->type = 'select';
		/*$temp->id = $this->fieldName;
		$temp->title = $this->field->title;
		$temp->display = @$this->field->display;
		$temp->help = @$this->field->help;
		$temp->valid = @$this->field->validate_field;
		$temp->value = $value;
		$temp->options = array();

		foreach( $this->field->options AS $opt )
		{
			if( @$opt->type != NULL && $opt->type == 'dynamic' )
				$this->loadDynamicOptions( $temp, $opt );
			else
				$temp->options[] = $opt;
		}
		*/
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['ecm-textlist'] = 'ECMTextlistField';

?>