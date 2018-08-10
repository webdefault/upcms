<?php

/**
 * @package controller
 * @classe home
 *
 */

load_lib_file( 'webdefault/db/mysql/matrixquery' );

abstract class Operation
{
    const SET = 0;
    const SELECT = 1;
    const INSERT = 2;
    const VALIDATE = 3;
    const UPDATE = 4;
    const DELETE = 5;
    // etc.
}

interface IField
{
	public function set( $request, $mapName, $map, $path, $mapId, $field, $db );
	public function select( $quick = false );
	public function insert( $values );
	public function update( $values );
	public function setFrom( $values, $operation );
	public function delete();
	public function validate( $curValues, $newValues, $force = false );
	public function submit( $value );
	public function doSelectAndSearch( $searchValue, $quick = false );
	
	public function listView( $id, $values );
	public function editView( $values );
	public function updateEditView( $values );
}

/*
interface ITable
{
	public function prepareSelect();
	public function prepareInsert();
	public function prepareUpdate();
}
*/
class CustomField implements IField
{
	protected $table, $column, $value, $name;
	
	function __construct( $table, $name, $column, $value )
	{
		$this->table = $table;
		$this->name = $name;
		$this->column = $column;
		$this->value = $value;
	}
	
	public function set( $request, $mapName, $map, $path, $mapId, $field, $db = NULL )
	{
		$request->setColumn( $this->table, $this->column, $this->name, $this->value, $this, MatrixQuery::GET );
	}
	
	public function select( $quick = false ){ return true; }
	public function insert( $values )
	{
		$this->request->setColumnFlag( $this->table, $this->column, MatrixQuery::SET );
	}
	public function update( $values )
	{
		$this->request->setColumnFlag( $this->table, $this->column, MatrixQuery::SET );
	}
	
	public function setFrom( $values, $operation ){}
	public function delete(){}
	public function validate( $curValues, $newValues, $force = false ){}
	public function submit( $value ){}
	public function doSelectAndSearch( $searchValue, $quick = false ){}
	public function listView( $id, $value ) {}
	public function editView( $value ) {}
	public function updateEditView( $values ) {}
}

class SetField extends CustomField
{
	public function set( $request, $mapName, $map, $path, $mapId, $field, $db = NULL )
	{
		$request->setColumn( $this->table, $this->column, $this->name, $this->value, $this, MatrixQuery::SET );
	}
}

class JoinField extends CustomField
{
	protected $join, $joinType;
	
	function __construct( $table, $name, $column, $value, $joinType )
	{
		$this->table = $table;
		$this->name = $name;
		$this->column = $column;
		$this->value = $value;
		
		switch( $joinType )
		{
			case 'LEFT_JOIN':
				$joinType = MatrixQuery::LEFT_JOIN;
				break;
				
			case 'RIGHT_JOIN':
				$joinType = MatrixQuery::RIGHT_JOIN;
				break;
				
			case 'INNER_JOIN':
				$joinType = MatrixQuery::INNER_JOIN;
				break;
				
			case 'OUTER_JOIN':
				$joinType = MatrixQuery::OUTER_JOIN;
				break;
			
			default:
				$joinType = MatrixQuery::JOIN;
				break;
		}
		
		$this->joinType = $joinType;
	}
	
	public function joinType()
	{
		return $this->joinType;
	}
	
	public function set( $request, $mapName, $map, $path, $mapId, $field, $db = NULL )
	{
		$this->request = $request;
		$this->mapName = $mapName;

		$this->map = $map;
		$this->path = $path;
		$this->mapId = $mapId;
		$this->field = $field;
		$this->db = $db;
		// parent::set( $request, $mapName, $map, $path, $mapId, $field, $db );
		
		$this->join = $path = explode( '.', $this->value );
		
		$this->value = ( !@$this->mapId[$mapName] && @$this->mapId[$path[0]] && $path[1] == 'id' ) 
			? $this->mapId[$path[0]] 
			: $this->value;
			
		$request->setColumn( $this->table, $this->column, $this->name, $this->value, $this, $this->joinType );
	}
	
	protected function table( $targetName )
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
	
	public function insert( $values )
	{
		$t = $this->table( $this->table );
		
		if( $t->id == $this->column )
			$this->request->setColumn( $this->table, $this->column, $this->name, $this->value, $this, 0 );
	}
	
	public function update( $values )
	{
		$t = $this->table( $this->table );
		
		if( $t->id == $this->column )
			$this->request->setColumn( $this->table, $this->column, $this->name, $this->value, $this, 0 );
	}
}

class WhereField extends CustomField
{
	protected $request;
	public function set( $request, $mapName, $map, $path, $mapId, $field, $db = NULL )
	{
		$this->request = $request;
		
		if( is_array( $this->value ) )
		{
			$temp = array( parse_bracket_instructions( $this->value[0], CMS::globalValues() ) );
			if( count( $this->value ) > 1 ) $temp[] = parse_bracket_instructions( $this->value[1], CMS::globalValues() );
			
			$request->setColumn( $this->table, $this->column, $this->name, $temp, $this, MatrixQuery::WHERE_RULE );
		}
		else
		{
			$this->value = parse_bracket_instructions( $this->value, CMS::globalValues() );
			
			if( substr( $this->value, 0, 1 ) == '%' || substr( $this->value, -1, 1 ) == '%' )
			{
				$request->setColumn( $this->table, $this->column, $this->name, $this->value, $this, MatrixQuery::LIKE );
			}
			else
			{
				if( strpos( $this->value, '%' ) !== false ) 
					$val = str_replace( "\\%", "%", $this->value );
				else
					$val = $this->value;
				
				$request->setColumn( $this->table, $this->column, $this->name, $val, $this, MatrixQuery::EQUAL_TO );
			}
		}
	}
	
	public function update( $values )
	{
		$this->request->setColumn( 
			$this->table, 
			$this->column, 
			$this->name, 
			$this->value, 
			$this, 
			!is_array( $this->value ) ? MatrixQuery::EQUAL_TO : MatrixQuery::IGNORE );
	}
	
	public function insert( $values )
	{
		$this->request->setColumn( 
			$this->table, 
			$this->column, 
			$this->name, 
			$this->value, 
			$this, 
			!is_array( $this->value ) ? MatrixQuery::EQUAL_TO : MatrixQuery::IGNORE );
	}
	
	public function updateEditView( $values )
	{
		return NULL;
	}
}

global $__CMSFields;
$__CMSFields = array();

function CMSFieldClass( $name )
{
	global $__CMSFields;
	
	if( !@$__CMSFields[$name] )
	{
		if( strpos( $name, '-' ) !== false )
		{
			$path = explode( '-', $name );
			$path = $path[0].'-fields/'.$path[1];
		}
		else
			$path = 'fields/'.$name;

		load_lib_file( 'cms/'.$path );
	}
	
	return $__CMSFields[$name];
}

?>