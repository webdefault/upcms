<?php

class SwitchField extends SimpletextField
{
	protected $basename, $cases;
	
	function __construct( $fieldName )
	{
		parent::__construct( $fieldName );
		$this->basename = '_hidden_'.$fieldName.'_';
	}
	
	protected function usingSetupTable()
	{
		$this->cases = array();
		
		foreach( $this->field->cases AS $key => $case )
		{
			$class = CMSFieldClass( $case->type );
			$caseField = new $class( $this->basename.$key );
			$caseField->set( $this->request, $this->mapName, 
							 $this->map, $this->path, $this->mapId, 
							 $case, $this->db );
			$this->cases[$key] = $caseField;
		}
		
		return true;
	}

	protected function loadOptions()
	{
		$this->optionsRequest = Request::createRequestFromTarget( $this->field->map, CMS::mapByName( $this->field->map ), array(), array(), $this->db );
		$mql = $this->optionsRequest->matrixQueryForSelect();
		$mql['data'][$this->field->map]['id'] = '';
		$this->optionsValues = $this->db->select( MatrixQuery::select( $mql ) );
		
		//return $result;
	}

	public function select( $quick = false )
	{
		$this->request->setColumnFlag( $this->cfrom, $this->fieldName, 1 );
		// $this->options = $this->loadOptions();
	}

	/*public function insert( $values )
	{
		// $value = @$values[$this->fieldName];

		// $this->request->setColumn( $this->cfrom, $this->fieldName, '"'.$value.'"', $this, true );
	}*/

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
				$result->id = 'id';
				$result->from = $targetName;
				$result->join = '';

				return $result;
			}
		}
	}

	public function listView( $id, $values )
	{
		$value = $values[$this->fieldName];
		
		return $this->cases[$value]->listView( $id, $values );
	}
	
	public function editView( $values )
	{
		$this->loadOptions();
		
		$temp = new stdClass();
		$temp->type = 'list';
		$temp->footer = true;
		$temp->{'create-new-item'} = true;
		$temp->icon = $this->field->icon;
		$temp->id = $this->fieldName;
		$temp->title = $this->field->title;
		$temp->help = @$this->field->help;
		$temp->valid = @$this->field->validate_field;
		$temp->readonly = @$this->field->readonly;
		$temp->rows = array();

		$addopts = array();

		print_r( $this->optionsValues );
		
		foreach( $this->optionsValues AS $opt )
		{
			$addopts[] = array( 'id' => $opt['id'], 'title' => $opt['name'] );
		}
		$temp->{'options'} = $addopts;
		
		return $temp;
	}
}

global $__CMSFields;
$__CMSFields['switch'] = 'SwitchField';

?>