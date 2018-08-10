<?php

class MQL
{
	private $mql;

	function __construct( $mql = NULL )
	{
		if( $mql )
		{
			$this->mql = $mql;
		}
		else
		{
			$this->mql = array( 
				'data' => array(),
				'tables' => array(),
				'select' => array() );
		}
	}

	public function setTarget( $key )
	{
		$this->mql['target'] = $key;
	}

	public function setTable( $key, $name, $primary )
	{
		$this->mql['tables'][$key] = array( 'table' => $name, 'id' => $primary );
		$this->mql['data'][$key] = array();

		if( !@$this->mql['target'] )
		{
			$this->mql['target'] = $key;
		}
	}

	public function setColumn( $table, $key, $name = NULL, $value = NULL, $flag = NULL )
	{
		$temp = array();

		$temp[] = $name;
		$temp[] = $value;
		$temp[] = $flag ? $flag : MatrixQuery::GET;
		
		$this->mql['data'][$table][$key] = $temp;
	}

	public function setCustomSelect( $item )
	{
		$this->mql['select'][] = $item;
	}

	public function setSlice( $offset, $limit )
	{
		$this->mql['slice'] = array( $offset, $limit );
	}

	public function getMatrix()
	{
		return $this->mql;
	}
}

?>