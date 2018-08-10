<?php

class NotEmptyValidator implements IValidator
{
	protected $mapName, $map, $path, $ids, $db, $validators;
	
	protected function createRequest()
	{
		return Request::createRequestFromTarget( $this->mapName, $this->map, $this->path, $this->ids, $this->db );
	}
	
	protected function selectMQL( $mql )
	{
		// print_r( MatrixQuery::select( $mql ) );
		return MatrixQuery::select( $mql, $this->db );
	}
	
	public function set( $mapName, $map, $path, $ids, $db )
	{
		$this->mapName = $mapName;
		$this->map = $map;
		$this->path = $path;
		$this->ids = $ids;
		$this->db = $db;
	}
	
	public function validate( $fieldName, $field, $value, $rule )
	{
		// var_dump( $value );
		return $value != '';
	}
}

global $__CMSValidators;
$__CMSValidators['not-empty'] = 'NotEmptyValidator';

?>